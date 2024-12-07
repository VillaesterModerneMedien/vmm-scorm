// config/webpack/webpack.dev.js
const { merge } = require('webpack-merge');
const common = require('./webpack.common.js');
const path = require('path');

console.log('Loading webpack.dev.js');


module.exports = merge(common, {
    mode: 'development',
    devtool: 'source-map',
    stats: 'errors-only',

    output: {
        path: path.resolve(__dirname, '../../dist'),
        filename: 'js/[name].js'
    },

    watchOptions: {
        aggregateTimeout: 300,
        poll: 1000,
        ignored: /node_modules/
    }
});
