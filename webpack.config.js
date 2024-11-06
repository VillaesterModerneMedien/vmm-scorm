const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

console.log('Loading webpack.config.js');

module.exports = {
    entry: {
        'vmm-scorm': path.resolve(__dirname, 'assets/js/core/VMMScorm.js')
    },
    output: {
        path: path.resolve(__dirname, 'dist'),
        filename: 'js/[name].js',
        clean: true,
        publicPath: '../',
        assetModuleFilename: 'assets/[hash][ext][query]'
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
                test: /\.scss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader',
                    {
                        loader: 'sass-loader',
                        options: {
                            sassOptions: {
                                includePaths: ['node_modules']
                            },
                            additionalData: `$fa-font-path: "~@fortawesome/fontawesome-free/webfonts";`
                        }
                    }
                ]
            },
            {
                test: /\.css$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader'
                ]
            },
            {
                test: /\.(woff|woff2|eot|ttf|otf|svg)$/i,
                type: 'asset/resource',
                generator: {
                    filename: 'webfonts/[name][ext]'
                }
            }
        ]
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: 'css/[name].css'
        }),

    ],
    resolve: {
        extensions: ['.js', '.scss', '.css'],
        alias: {
            '@': path.resolve(__dirname, 'assets'),
            '@js': path.resolve(__dirname, 'assets/js'),
            '@scss': path.resolve(__dirname, 'assets/scss'),
            '@core': path.resolve(__dirname, 'assets/js/core'),
            '@elementor': path.resolve(__dirname, 'assets/js/elementor'),
            '@utils': path.resolve(__dirname, 'assets/js/utils')
        }
    }
};
