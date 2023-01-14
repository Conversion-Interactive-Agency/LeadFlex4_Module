const path = require('path');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const ImageMinimizerPlugin = require("image-minimizer-webpack-plugin");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const TerserPlugin = require("terser-webpack-plugin");

const { extendDefaultPlugins } = require("svgo");
const BrowserSyncPlugin = require('browser-sync-webpack-plugin');
require('dotenv').config();

module.exports = (env) => {
    const devTool = env.mode === "development" ? "source-map" : false;
    return {
        entry: path.resolve(__dirname, './src/app.js'),
        mode: env.mode,
        output: {
            filename: "app.js",
            path: path.resolve(__dirname,'./web/dist/' ),
            assetModuleFilename: 'images/[name][ext]'
        },
        devtool: devTool,
        plugins: [
            new MiniCssExtractPlugin({
                filename: "[name].css"
            }),
            //Comment this out if you do not want to use this, this was put in place to proxy mamp development.
            /*new BrowserSyncPlugin({
                // browse to http://localhost:3000/ during development,
                // ./public directory is being served
                host: 'localhost',
                port: 3000,
                files:['./templates/!**!/!*.twig', './web/webpack/!**!/!*.css', ],
                // server: { baseDir: ['public'] }
                proxy: process.env.SITE_BASEURL
            })*/
        ],
        module: {
            rules: [
                {
                    test: /\.s[ac]ss$/i,
                    use: [
                        // minicssextract plugin
                        MiniCssExtractPlugin.loader,
                        // Translates CSS into CommonJS
                        "css-loader",
                        // Compiles Sass to CSS
                        "sass-loader",
                        //Post CSS
                        "postcss-loader"
                    ],
                },
                {
                    test: /\.(png|svg|jpg|jpeg|gif)$/i,
                    type: 'asset/resource'
                }
            ],
        },
        optimization: {
            minimize: true,
            minimizer: [
                new ImageMinimizerPlugin({
                    minimizer: {
                        implementation: ImageMinimizerPlugin.imageminMinify,
                        options: {
                            plugins: [
                                "imagemin-gifsicle",
                                "imagemin-mozjpeg",
                                "imagemin-pngquant",
                                "imagemin-svgo",
                            ],
                        },
                    },
                    // Disable `loader`
                    loader: false,
                }),
                new CssMinimizerPlugin(),
                new TerserPlugin()
            ]
        }
    }
}