const path = require("path");

module.exports = {
	reactStrictMode: true,
	sassOptions: {
		includePaths: [path.join(__dirname, "styles")],
	},
};

const withFonts = require("next-fonts");
module.exports = withFonts({
	webpack(config, options) {
		return config;
	},
});

module.exports = {
	experimental: {
		forceSwcTransforms: true,
	},
};

// module.exports = {
// 	async rewrites() {
// 		return [
// 			{
// 				source: "/api/:path*",
// 				destination: "http://127.0.0.1/api/:path*", // Proxy to your PHP API
// 			},
// 		];
// 	},
// };
