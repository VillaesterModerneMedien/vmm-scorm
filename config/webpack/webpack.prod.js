// config/webpack/webpack.prod.js
const { merge } = require('webpack-merge');
const common = require('./webpack.common.js');
const path = require('path');
const TerserPlugin = require('terser-webpack-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');

console.log('Loading webpack.prod.js');


module.exports = merge(common, {
    mode: 'production',
    stats: 'errors-only',

    output: {
        path: path.resolve(__dirname, '../../dist'),
        filename: 'js/vmm-scorm.min.js'
    },
    optimization: {
        minimizer: [
            new TerserPlugin({
                extractComments: false,
                terserOptions: {
                    format: {
                        comments: false,
                    },
                },
            }),
            new CssMinimizerPlugin()
        ],
        splitChunks: {
            cacheGroups: {
                jquery: {
                    test: /[\\/]jquery[\\/]/,
                    name: 'jquery',
                    chunks: 'all'
                }
            }
        }
    }
});
