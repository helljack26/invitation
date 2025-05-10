// next.config.js
const path = require('path');

module.exports = {
  reactStrictMode: true,
  sassOptions: { includePaths: [path.join(__dirname, 'styles')] },
  experimental: { forceSwcTransforms: true },

  webpack(config) {
    config.module.rules.push({
      test: /\.svg$/i,
      issuer: /\.[jt]sx?$/,
      use: [
        {
          loader: '@svgr/webpack',
          options: { ref: true },   // ← forwardRef wrapper
        },
      ],
    });
    return config;
  },
};
