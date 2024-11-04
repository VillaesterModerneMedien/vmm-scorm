// config/webpack/webpack.common.js
const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
    // Korrigierte Entry-Points
    entry: {
        'vmm-scorm': path.resolve(__dirname, '../../assets/js/core/VMMScorm.js')
    },

    output: {
        path: path.resolve(__dirname, '../../dist'),
        filename: 'js/[name].js',
        clean: true
    },

    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env']
                    }
                }
            },
            {
                test: /\.(sa|sc|c)ss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader',
                        options: {
                            sourceMap: true
                        }
                    },
                    {
                        loader: 'sass-loader',
                        options: {
                            sourceMap: true
                        }
                    }
                ]
            }
        ]
    },

    plugins: [
        new MiniCssExtractPlugin({
            filename: 'css/[name].css'
        })
    ],

    resolve: {
        extensions: ['.js', '.scss', '.css'],
        alias: {
            '@': path.resolve(__dirname, '../../assets'),
            '@js': path.resolve(__dirname, '../../assets/js'),
            '@scss': path.resolve(__dirname, '../../assets/scss'),
            '@core': path.resolve(__dirname, '../../assets/js/core'),
            '@elementor': path.resolve(__dirname, '../../assets/js/elementor'),
            '@utils': path.resolve(__dirname, '../../assets/js/utils')
        }
    }
};
