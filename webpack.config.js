const webpack = require('webpack');
const path = require('path');
const CopyPlugin = require('copy-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');

// https://github.com/webpack-contrib/copy-webpack-plugin/issues/349
// https://github.com/webpack/loader-utils/issues/121
const hashDigestLength = 10;

// noinspection JSUnresolvedVariable
module.exports = {
  mode: 'production',
  context: path.resolve(__dirname, 'assets'),
  watchOptions: {
    ignored: ['node_modules/**', 'vendor/**'],
    poll: 1000,
  },
  entry: {
    bundle: ['./js/index.js'],
  },
  devtool: 'source-map',
  output: {
    path: path.resolve(__dirname, 'public/static'),
    filename: '[name].[chunkhash].js',
    hashDigestLength: hashDigestLength,
  },
  module: {
    rules: [
      {
        test: /\.css$/,
        use: [
          {
            loader: MiniCssExtractPlugin.loader,
            options: {
              publicPath: '',
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
        use: 'imports-loader?wrapper=window',
      },
      {
        test: /\.(png|jpe?g|gif|svg)$/i,
        use: [
          {
            loader: 'file-loader',
            options: {
              outputPath: 'common',
              name: `[name].[contenthash:hex:${hashDigestLength}].[ext]`,
              emitFile: false,
            },
          },
        ],
      },
    ],
  },
  plugins: [
    new WebpackManifestPlugin({
      publicPath: '',
      removeKeyHash: new RegExp(`(\\.[a-f0-9]{${hashDigestLength}})(\\..*)`),
    }),
    new CleanWebpackPlugin(),
    new webpack.ProvidePlugin({
      $: 'jquery',
      jQuery: 'jquery',
    }),
    new MiniCssExtractPlugin({
      filename: '[name].[contenthash].css',
    }),
    new CopyPlugin({
      patterns: [{ from: 'common', to: `common/[name].[contenthash:${hashDigestLength}].[ext]` }],
    }),
  ],
  optimization: {
    splitChunks: {
      cacheGroups: {
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: 'vendor',
          chunks: 'all',
          enforce: true,
        },
      },
    },
  },
};
