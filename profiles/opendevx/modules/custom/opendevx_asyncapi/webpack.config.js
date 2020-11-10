const path = require('path');

const config = {
  entry: {
    main: ["./js/src/index.tsx"]
  },
  output: {
    path: path.resolve(__dirname, "js/dist"),
    filename: '[name].min.js'
  },
  resolve: {
    extensions: ['.js', '.jsx', '.ts', '.tsx'],
  },
  module: {
    rules: [
      {
        test: /\.tsx?$/,
        loader: 'babel-loader',
        exclude: /node_modules/,
        include: path.join(__dirname, 'js/src'),
      },
      {
        test: /\.css$/,
        loader: 'style-loader!css-loader',
      }
    ],
  },
};

module.exports = config;
