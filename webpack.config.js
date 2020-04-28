const webpack = require('webpack');
const path = require('path');
const CopyPlugin = require('copy-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const ManifestPlugin = require('webpack-manifest-plugin');

// https://github.com/webpack-contrib/copy-webpack-plugin/issues/349
// https://github.com/webpack/loader-utils/issues/121
const hashDigestLength = 10;

/*
  copy-webpack-plugin uses md5 internally we need to ensure that file-loader uses same algo
  so that the urls resolved from css-loader match emmited files on filesystem
 */
const hashMethod = 'md5';

module.exports = {
  mode: 'none',
  watchOptions: {
    ignored: ['node_modules/**', 'vendor/**'],
    poll: 1000,
  },
  entry: {
    bundle: ['./assets/js/index.js'],
  },
  devtool: 'source-map',
  output: {
    path: path.resolve(__dirname, 'public/static'),
    filename: '[name].[chunkhash].js',
    hashDigestLength: hashDigestLength,
    hashFunction: hashMethod,
  },
  module: {
    rules: [
      {
        test: /\.css$/,
        use: [
          {
            loader: MiniCssExtractPlugin.loader,
            options: {
              hmr: false,
            },
          },
          {
            loader: 'css-loader',
            options: {
              importLoaders: 1,
              sourceMap: true,
            },
          },
          {
            loader: 'postcss-loader',
            options: {
              sourceMap: true,
            },
          },
        ],
      },
      {
        test: /[\/\\]node_modules[\/\\]@claviska[\/\\]jquery-dropdown[\/\\]jquery.dropdown\.js$/,
        use: 'imports-loader?this=>window',
      },
      {
        test: /\.(png|jpe?g|gif|svg)$/i,
        use: [
          {
            loader: 'file-loader',
            options: {
              outputPath: 'common',
              name: `[name].[${hashMethod}:contenthash:hex:${hashDigestLength}].[ext]`,
              emitFile: false,
            },
          },
        ],
      },
    ],
  },
  plugins: [
    new ManifestPlugin({
      map: (file) => {
        file.name = file.name.replace(new RegExp(`(\\.[a-f0-9]{${hashDigestLength}})(\\..*)$`), '$2');
        return file;
      },
    }),
    new CleanWebpackPlugin(),
    new webpack.ProvidePlugin({
      $: 'jquery',
      jQuery: 'jquery',
    }),
    new MiniCssExtractPlugin({
      filename: '[name].[contenthash].css',
    }),
    new CopyPlugin([{ from: 'assets/common', to: `common/[name].[contenthash:${hashDigestLength}].[ext]` }]),
  ],
  optimization: {
    moduleIds: 'hashed',
    splitChunks: {
      cacheGroups: {
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: 'vendor',
          chunks: 'all',
        },
      },
    },
  },
};