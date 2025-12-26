// const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
// const path          = require( 'path' );

// module.exports = {
// 	...defaultConfig,
// 	entry: {
// 		'bdpg-blocks': path.resolve( process.cwd(), 'blocks', 'src', 'index.js' ),
// 	},
// 	output: {
// 		...defaultConfig.output,
// 		path: path.resolve( process.cwd(), 'dist' ),
// 	},
// 	externals: {
// 		'@woocommerce/blocks-registry': 'wc.wcBlocksRegistry',
// 		'@woocommerce/settings': 'wc.wcSettings',
// 		'@woocommerce/blocks-components': 'wc.wcBlocksComponents',
// 		'@woocommerce/base-context/hooks': 'wc.wcBaseContext',
// 		'@woocommerce/base-context': 'wc.wcBaseContext',
// 		'@woocommerce/block-data': 'wc.wcBlockData',
// 	},
// };
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const WooCommerceDependencyExtractionWebpackPlugin = require('@woocommerce/dependency-extraction-webpack-plugin');
const path = require('path');

const wcDepMap = {
	'@woocommerce/blocks-registry': ['wc', 'wcBlocksRegistry'],
	'@woocommerce/settings'       : ['wc', 'wcSettings']
};

const wcHandleMap = {
	'@woocommerce/blocks-registry': 'wc-blocks-registry',
	'@woocommerce/settings'       : 'wc-settings'
};

const requestToExternal = (request) => {
	if (wcDepMap[request]) {
		return wcDepMap[request];
	}
};

const requestToHandle = (request) => {
	if (wcHandleMap[request]) {
		return wcHandleMap[request];
	}
};

// Export configuration.
module.exports = {
	...defaultConfig,
	entry: {
		'bdpg-blocks': path.resolve( process.cwd(), 'blocks', 'src', 'index.js' ),

	},
	output: {
		path: path.resolve( process.cwd(), 'dist' ),
		filename: '[name].js',
	},
	plugins: [
		...defaultConfig.plugins.filter(
			(plugin) =>
				plugin.constructor.name !== 'DependencyExtractionWebpackPlugin'
		),
		new WooCommerceDependencyExtractionWebpackPlugin({
			requestToExternal,
			requestToHandle
		})
	]
};
