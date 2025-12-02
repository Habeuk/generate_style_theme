// dev-optimized.js
const { spawn } = require('child_process');
const fs = require('fs');
const path = require('path');

console.log('🚀 Démarrage du mode développement optimisé...\n');

// 1. Construire toutes les entrées une fois
console.log('📦 Construction initiale de toutes les entrées...');
const buildProcess = spawn('node', ['build-independent.js', '--fast'], {
  stdio: 'inherit',
  shell: true
});

buildProcess.on('close', (code) => {
  if (code !== 0) {
    console.error('❌ Erreur lors de la construction initiale');
    process.exit(code);
  }
  
  console.log('\n✅ Construction initiale terminée');
  console.log('👁️  Activation du mode watch sur les fichiers sources...\n');
  
  // 2. Démarrer un watcher léger pour les modifications
  startLightWatcher();
});

function startLightWatcher() {
  const chokidar = require('chokidar');
  
  // Charger le mapping des fichiers vers les entrées
  const entries = JSON.parse(
    fs.readFileSync('auto_generate_entries.json', 'utf-8')
  );
  
  // Inverser le mapping: fichier -> [entryNames]
  const fileToEntries = {};
  Object.entries(entries).forEach(([entryName, entryPath]) => {
    const absPath = path.resolve(entryPath);
    if (!fileToEntries[absPath]) fileToEntries[absPath] = [];
    fileToEntries[absPath].push(entryName);
  });
  
  const watcher = chokidar.watch('./src', {
    ignored: /node_modules/,
    persistent: true,
    ignoreInitial: true
  });
  
  watcher.on('change', (filePath) => {
    const absPath = path.resolve(filePath);
    
    if (fileToEntries[absPath]) {
      // Reconstruire seulement les entrées affectées
      fileToEntries[absPath].forEach(entryName => {
        console.log(`🔨 Reconstruction: ${entryName}`);
        
        const rebuildProcess = spawn('node', [
          'build-single-entry.js',
          entryName
        ], {
          stdio: 'inherit',
          shell: true
        });
        
        rebuildProcess.on('close', (code) => {
          if (code === 0) {
            console.log(`✅ ${entryName} mis à jour`);
          }
        });
      });
    } else {
      // Si le fichier n'est pas une entrée directe, reconstruire toutes les entrées qui pourraient l'utiliser
      console.log(`🔄 Fichier partagé modifié, reconstruction sélective...`);
      
      // Logique pour trouver quelles entrées utilisent ce fichier
      // (à adapter selon votre structure)
    }
  });
  
  console.log('✅ Watcher démarré. Modifiez un fichier pour déclencher une reconstruction.');
  console.log('📁 Serveur disponible sur: http://localhost:3000 (si vous avez un serveur)');
}