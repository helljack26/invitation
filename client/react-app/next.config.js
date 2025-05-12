// next.config.js
const path = require("path");
const withSvgr = require("next-svgr");

module.exports = withSvgr({
	reactStrictMode: true,
	experimental: { forceSwcTransforms: true },
	sassOptions: {
		includePaths: [path.join(__dirname, "styles")],
	},

});
