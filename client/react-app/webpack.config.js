// next.config.js
const path = require("path");

module.exports = {
	reactStrictMode: true,
	sassOptions: { includePaths: [path.join(__dirname, "styles")] },
	experimental: { forceSwcTransforms: true },

	webpack(config) {
		// 1) Exclude .svg from the built-in asset loader:
		const fileLoaderRule = config.module.rules.find((rule) =>
			rule.test?.test(".svg")
		);
		fileLoaderRule.exclude = /\.svg$/i;

		// 2) Add SVGR:
		config.module.rules.push({
			test: /\.svg$/i,
			issuer: /\.[jt]sx?$/,
			use: [{ loader: "@svgr/webpack", options: { ref: true } }],
		});

		return config;
	},
};
