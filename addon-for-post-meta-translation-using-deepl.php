<?php
/**
 * Plugin Name: Addon for Post Meta Translation using DeepL
 * Plugin URI:  https://github.com/badasswp/addon-for-post-meta-translation-using-deepl
 * Description: Translate post meta data when using DeepL for WordPress.
 * Version:     1.0.1
 * Author:      badasswp
 * Author URI:  https://github.com/badasswp
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: addon-for-post-meta-translation-using-deepl
 * Domain Path: /languages
 *
 * @package AddonForPostMetaTranslationUsingDeepL
 */

namespace badasswp\AddonForPostMetaTranslationUsingDeepL;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

define( 'ADDON_FOR_POST_META_TRANSLATION_USING_DEEPL_AUTOLOAD', __DIR__ . '/vendor/autoload.php' );

// Composer Check.
if ( ! file_exists( ADDON_FOR_POST_META_TRANSLATION_USING_DEEPL_AUTOLOAD ) ) {
	add_action(
		'admin_notices',
		function () {
			vprintf(
				/* translators: Plugin directory path. */
				esc_html__( 'Fatal Error: Composer not setup in %s', 'addon-for-post-meta-translation-using-deepl' ),
				[ __DIR__ ]
			);
		}
	);

	return;
}

// Run Plugin.
require_once ADDON_FOR_POST_META_TRANSLATION_USING_DEEPL_AUTOLOAD;
( \AddonForPostMetaTranslationUsingDeepL\Plugin::get_instance() )->run();
