const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { ProvidePlugin } = require('webpack');
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');

module.exports = {
  mode: 'production',
  context: path.resolve(__dirname, 'assets'),
  watchOptions: {
    ignored: ['**/node_modules/**', '**/vendor/**', '**/gen/**', '**/*.php'],
    poll: 1000,
  },
  entry: {
    bundle: ['./js/index.js'],
  },
  devtool: 'source-map',
  output: {
    path: path.resolve(__dirname, 'public/static'),
    publicPath: '',
    filename: '[name].[chunkhash].js',
    hashFunction: 'xxhash64',
    clean: true,
  },
  module: {
    rules: [
      {
        test: /\.(less|css)$/,
        use: [
          {
            loader: MiniCssExtractPlugin.loader,
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
          {
            loader: 'less-loader',
            options: {
              lessOptions: {
                strictMath: true,
              },
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
        type: 'asset/resource',
        generator: {
          filename: `common/[name].[contenthash][ext]`,
        },
      },
    ],
  },
  plugins: [
    new WebpackManifestPlugin({}),
    new ProvidePlugin({
      $: 'jquery',
      jQuery: 'jquery',
    }),
    new MiniCssExtractPlugin({
      filename: '[name].[contenthash].css',
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
