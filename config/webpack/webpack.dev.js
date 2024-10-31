// config/webpack/webpack.dev.js
const { merge } = require('webpack-merge');
const common = require('./webpack.common.js');
const path = require('path');

module.exports = merge(common, {
    mode: 'development',
    output: {
        path: path.resolve(__dirname, '../../dist'),
        filename: 'js/vmm-scorm.js'
    },
    devtool: 'inline-source-map',
    watch: true,
    watchOptions: {
        ignored: /node_modules/,
        poll: 1000
    }
});
