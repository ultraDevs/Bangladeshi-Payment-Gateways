const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path          = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		'bdpg-blocks': path.resolve( process.cwd(), 'blocks', 'src', 'index.js' ),
	},
	output: {
		...defaultConfig.output,
		path: path.resolve( process.cwd(), 'dist' ),
	},
	externals: {
		'@woocommerce/blocks-registry': 'wc.wcBlocksRegistry',
		'@woocommerce/settings': 'wc.wcSettings',
		'@woocommerce/blocks-components': 'wc.wcBlocksComponents',
		'@woocommerce/base-context/hooks': 'wc.wcBaseContext',
		'@woocommerce/base-context': 'wc.wcBaseContext',
		'@woocommerce/block-data': 'wc.wcBlockData',
	},
};
