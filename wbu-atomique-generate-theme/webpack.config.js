const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const TerserPlugin = require("terser-webpack-plugin");
const ESLintPlugin = require("eslint-webpack-plugin"); // Importer le plugin ESLint

const env = process.env.NODE_ENV;
const devMode = process.env.NODE_ENV !== "production";

const plugins = [
  new MiniCssExtractPlugin({
    filename: "./css/[name].css",
    chunkFilename: "[id].css",
  }),
  new ESLintPlugin({ // Ajouter ESLintPlugin
    extensions: ['js'], // Fichiers à vérifier
    exclude: 'node_modules', // Exclure le dossier node_modules
    fix: true, // Corrige automatiquement les erreurs simples
  }),
];

module.exports = {
  plugins,
  mode: env || "development",
  entry: {
    "global-style": "./src/js/global-style.js",
    "vendor-style": "./src/js/vendor-style.js",
    "mail-style": "./src/js/mail-style.js",
  },
  output: {
    path: path.resolve(__dirname, "../"),
    filename: "./js/[name].js",
  },
  devtool: devMode ? "inline-source-map" : false,
  cache: {
    type: "filesystem", // Active le cache
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: [
          {
            loader: "babel-loader",
            options: {
              presets: ["@babel/preset-env"],
            },
          },
          // Supprimer eslint-loader
        ],
      },
      {
        test: /\.(sa|sc|c)ss$/,
        use: [
          devMode
            ? "style-loader"
            : {
                loader: MiniCssExtractPlugin.loader,
                options: {
                  publicPath: "../",
                },
              },
          {
            loader: "css-loader",
            options: {
              importLoaders: 1,
              url: false, // Désactive le traitement des URLs
            },
          },
          {
            loader: "postcss-loader",
            options: {
              sourceMap: true,
              postcssOptions: {
                plugins: [require("autoprefixer")],
              },
            },
          },
          {
            loader: "sass-loader",
            options: {
              sourceMap: true,
              implementation: require("sass"),
            },
          },
        ],
      },
      {
        test: /\.(gif|png|jpe?g|svg)$/i,
        type: "asset/resource",
        generator: {
          filename: "images/[name][ext]",
        },
      },
      {
        test: /\.svg$/i,
        type: "asset/resource",
        generator: {
          filename: "icons/[name][ext]",
        },
      },
      {
        test: /\.(eot|ttf|woff|woff2)$/,
        type: "asset/resource",
        generator: {
          filename: "fonts/[name][ext]",
        },
      },
    ],
  },
  devServer: {
    contentBase: path.resolve(__dirname, "./public"),
    port: 3000,
    publicPath: "/dist/",
    watchContentBase: true,
    hot: true,
  },
  optimization: {
    minimizer: [
      new CssMinimizerPlugin(),
      new TerserPlugin(),
    ],
  },
};