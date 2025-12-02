// build-independent.js
const fs = require('fs');
const path = require('path');
const { spawn, execSync } = require('child_process');
const os = require('os');

// Configuration
const CONFIG = {
  maxMemoryMB: 2048, // Mémoire maximale par processus
  batchSize: 50, // Entrées par lot
  maxConcurrent: 5, // Processus parallèles maximum
  nodeOptions: `--max-old-space-size=${2048}`, // Options Node.js
};

// Charger uniquement les entrées du fichier JSON
const entriesPath = path.resolve(__dirname, 'auto_generate_entries.json');
if (!fs.existsSync(entriesPath)) {
  console.error('❌ Fichier auto_generate_entries.json introuvable');
  process.exit(1);
}

const allEntries = JSON.parse(fs.readFileSync(entriesPath, 'utf-8'));
const entryNames = Object.keys(allEntries);

console.log(`📊 ${entryNames.length} entrées indépendantes détectées`);

// 1. Créer un fichier de configuration minimal pour Webpack
function createMinimalWebpackConfig(entryName, entryPath) {
  return `
    const MiniCssExtractPlugin = require('mini-css-extract-plugin');
    const path = require('path');
    
    module.exports = {
      mode: 'development',
      entry: {
        '${entryName}': '${path.resolve(__dirname, entryPath)}'
      },
      output: {
        path: '${path.resolve(__dirname, "../")}',
        filename: './js/[name].js',
      },
      devtool: false, // Désactiver source maps pour économiser
      cache: false, // Désactiver cache pour éviter conflits
      module: {
        rules: [
          {
            test: /\\.js$/,
            exclude: /node_modules/,
            use: {
              loader: 'babel-loader',
              options: {
                presets: ['@babel/preset-env'],
                cacheDirectory: false,
              }
            }
          },
          {
            test: /\\.(sa|sc|c)ss$/,
            use: [
              MiniCssExtractPlugin.loader,
              {
                loader: 'css-loader',
                options: {
                  importLoaders: 1,
                  url: false,
                }
              },
              'postcss-loader',
              'sass-loader'
            ]
          }
        ]
      },
      plugins: [
        new MiniCssExtractPlugin({
          filename: './css/[name].css'
        })
      ],
      optimization: {
        minimize: false, // Désactiver minification pour aller plus vite
        removeAvailableModules: false,
        removeEmptyChunks: false,
        splitChunks: false, // Désactiver splitChunks pour une seule entrée
      },
      performance: {
        hints: false
      },
      stats: 'errors-only'
    };
  `;
}

// 2. Fonction pour construire une entrée unique
function buildSingleEntry(entryName, entryPath, index, total) {
  return new Promise((resolve, reject) => {
    console.log(`[${index + 1}/${total}] 🔨 ${entryName}`);
    
    const configContent = createMinimalWebpackConfig(entryName, entryPath);
    const configPath = path.join(__dirname, `.temp-config-${Date.now()}-${entryName}.js`);
    
    fs.writeFileSync(configPath, configContent);
    
    // Utiliser spawn pour mieux gérer la mémoire
    const webpackProcess = spawn('node', [
      ...CONFIG.nodeOptions.split(' '),
      require.resolve('webpack/bin/webpack.js'),
      '--config', configPath,
      '--color'
    ], {
      stdio: ['pipe', 'pipe', 'pipe'],
      shell: false,
      detached: false,
    });
    
    let output = '';
    let errorOutput = '';
    
    webpackProcess.stdout.on('data', (data) => {
      output += data.toString();
    });
    
    webpackProcess.stderr.on('data', (data) => {
      errorOutput += data.toString();
    });
    
    webpackProcess.on('close', (code) => {
      // Nettoyer le fichier temporaire
      if (fs.existsSync(configPath)) {
        fs.unlinkSync(configPath);
      }
      
      if (code === 0) {
        console.log(`   ✅ ${entryName} terminé`);
        resolve({ entryName, success: true });
      } else {
        console.log(`   ❌ ${entryName} échoué: ${errorOutput.substring(0, 200)}`);
        resolve({ entryName, success: false, error: errorOutput });
      }
    });
    
    webpackProcess.on('error', (error) => {
      reject(error);
    });
  });
}

// 3. Gestionnaire de file d'attente avec contrôle mémoire
class QueueManager {
  constructor(entries, maxConcurrent = 3) {
    this.entries = entries;
    this.maxConcurrent = maxConcurrent;
    this.running = 0;
    this.completed = 0;
    this.failed = [];
    this.total = entries.length;
  }
  
  async run() {
    console.log(`🚀 Démarrage avec ${this.maxConcurrent} processus parallèles`);
    
    const chunks = [];
    for (let i = 0; i < this.entries.length; i += CONFIG.batchSize) {
      chunks.push(this.entries.slice(i, i + CONFIG.batchSize));
    }
    
    for (let chunkIndex = 0; chunkIndex < chunks.length; chunkIndex++) {
      const chunk = chunks[chunkIndex];
      console.log(`\n📦 Lot ${chunkIndex + 1}/${chunks.length} (${chunk.length} entrées)`);
      
      // Traiter le chunk avec parallélisme limité
      await this.processChunk(chunk);
      
      // Pause entre les chunks pour laisser le GC travailler
      if (chunkIndex < chunks.length - 1) {
        console.log('⏸️  Pause de 2 secondes...');
        await new Promise(resolve => setTimeout(resolve, 2000));
      }
    }
    
    return {
      total: this.total,
      completed: this.completed,
      failed: this.failed.length,
      failedEntries: this.failed
    };
  }
  
  async processChunk(chunk) {
    const promises = [];
    
    for (const [index, entry] of chunk.entries()) {
      const globalIndex = this.completed + index;
      
      // Attendre si on a trop de processus en cours
      while (this.running >= this.maxConcurrent) {
        await new Promise(resolve => setTimeout(resolve, 100));
      }
      
      this.running++;
      
      const promise = buildSingleEntry(entry.name, entry.path, globalIndex, this.total)
        .then(result => {
          this.running--;
          this.completed++;
          
          if (!result.success) {
            this.failed.push({
              name: entry.name,
              error: result.error
            });
          }
          
          return result;
        })
        .catch(error => {
          this.running--;
          this.failed.push({
            name: entry.name,
            error: error.message
          });
        });
      
      promises.push(promise);
      
      // Petit délai entre le démarrage de chaque processus
      await new Promise(resolve => setTimeout(resolve, 100));
    }
    
    await Promise.all(promises);
  }
}

// 4. Script principal
async function main() {
  console.log('🏗️  Construction des entrées indépendantes\n');
  
  // Convertir en tableau d'objets
  const entriesArray = Object.entries(allEntries).map(([name, path]) => ({
    name,
    path
  }));
  
  // Calculer le parallélisme optimal
  const cpuCount = os.cpus().length;
  const maxConcurrent = Math.min(cpuCount, CONFIG.maxConcurrent);
  
  // Lancer la construction
  const queue = new QueueManager(entriesArray, maxConcurrent);
  const startTime = Date.now();
  
  const result = await queue.run();
  
  const duration = ((Date.now() - startTime) / 1000).toFixed(1);
  
  console.log('\n' + '='.repeat(50));
  console.log('📊 RAPPORT DE CONSTRUCTION');
  console.log('='.repeat(50));
  console.log(`⏱️  Durée totale: ${duration} secondes`);
  console.log(`✅ Réussies: ${result.completed - result.failed}`);
  console.log(`❌ Échouées: ${result.failed}`);
  console.log(`📊 Total: ${result.total}`);
  
  if (result.failed > 0) {
    console.log('\n📋 Entrées échouées:');
    result.failedEntries.forEach(fail => {
      console.log(`   • ${fail.name}`);
    });
    
    // Option: Réessayer les échecs
    console.log('\n🔄 Réessayer les échecs ? (o/n)');
    process.stdin.once('data', (data) => {
      if (data.toString().trim().toLowerCase() === 'o') {
        retryFailed(result.failedEntries);
      } else {
        process.exit(result.failed > 0 ? 1 : 0);
      }
    });
  } else {
    console.log('\n🎉 Toutes les entrées ont été construites avec succès !');
    process.exit(0);
  }
}

// 5. Fonction pour réessayer les échecs
async function retryFailed(failedEntries) {
  console.log('\n🔄 Réessai des entrées échouées...');
  
  for (const entry of failedEntries) {
    try {
      await buildSingleEntry(entry.name, allEntries[entry.name], 0, 1);
    } catch (error) {
      console.log(`   ❌ ${entry.name} a de nouveau échoué`);
    }
  }
  
  process.exit(0);
}

// Gestion des erreurs non capturées
process.on('unhandledRejection', (error) => {
  console.error('💥 Erreur non gérée:', error);
  process.exit(1);
});

// Lancer le script
main();

