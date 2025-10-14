<?php
/**
 * Admin Service.
 *
 * This service manages the admin area of the
 * plugin. It provides functionality for registering
 * the plugin options/settings.
 *
 * @package AddonForPostMetaTranslationUsingDeepL
 */

namespace AddonForPostMetaTranslationUsingDeepL\Services;

use AddonForPostMetaTranslationUsingDeepL\Abstracts\Service;
use AddonForPostMetaTranslationUsingDeepL\Interfaces\Kernel;

class Admin extends Service implements Kernel {
	/**
	 * Plugin Option.
	 *
	 * @var string
	 */
	const PLUGIN_SLUG = 'addon-for-post-meta-translation-using-deepl';

	/**
	 * Plugin Option.
	 *
	 * @var string
	 */
	const PLUGIN_OPTION = 'addon_for_post_meta_translation_using_deepl';

	/**
	 * Plugin Group.
	 *
	 * @var string
	 */
	const PLUGIN_GROUP = 'addon-for-post-meta-translation-using-deepl-group';

	/**
	 * Plugin Options.
	 *
	 * @var mixed[]
	 */
	public array $options;

	/**
	 * Bind to WP.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', [ $this, 'register_options_page' ] );
	}

	/**
	 * Register Options Page.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_options_page(): void {
		add_menu_page(
			esc_html__( 'Addon for Post Meta Translation using DeepL', 'addon-for-post-meta-translation-using-deepl' ),
			esc_html__( 'Addon for Post Meta Translation using DeepL', 'addon-for-post-meta-translation-using-deepl' ),
			'manage_options',
			self::PLUGIN_SLUG,
			[ $this, 'register_options_cb' ],
			'dashicons-admin-customizer',
			100
		);
	}

	/**
	 * Register Options Callback.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_options_cb(): void {
		$this->options = get_option( self::PLUGIN_OPTION, [] );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Addon for Post Meta Translation using DeepL', 'addon-for-post-meta-translation-using-deepl' ); ?></h1>
			<p><?php esc_html_e( 'Translate post meta data when using DeepL translate.', 'addon-for-post-meta-translation-using-deepl' ); ?></p>
			<form method="post" action="options.php">
			<?php
				settings_fields( self::PLUGIN_GROUP );
				do_settings_sections( self::PLUGIN_SLUG );
				submit_button();
			?>
			</form>
		</div>
		<?php
	}
}
