<?php
/**
 * EasyAccordion Service.
 *
 * This service extends post meta translation for
 * the EasyAccordion post types using DeepL.
 *
 * @package AddonForPostMetaTranslationUsingDeepL
 */

namespace AddonForPostMetaTranslationUsingDeepL\Services;

use AddonForPostMetaTranslationUsingDeepL\Abstracts\Service;
use AddonForPostMetaTranslationUsingDeepL\Interfaces\Kernel;

class EasyAccordion extends Service implements Kernel {
	/**
	 * Bind to WP.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_init', [ $this, 'translate_ea_post_types' ] );
		add_filter( 'deepl_translate_post_link_strings', [ $this, 'handle_ea_content' ], 100, 6 );
		add_action( 'deepl_translate_after_post_update', [ $this, 'update_translated_ea_content' ], 20, 7 );
	}

	/**
	 * Translate EA post types.
	 *
	 * Filter the list of post types available to
	 * DeepL for translation. Add the Easy Accordion
	 * post types via this filter.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function translate_ea_post_types(): void {
		// Bail out, if DeepL is not loaded.
		if ( ! class_exists( 'DeepLProConfiguration' ) ) {
			return;
		}

		add_filter(
			'DeepLProConfiguration::getProBulkPostTypes',
			[ $this, 'update_bulk_post_types' ]
		);
	}

	/**
	 * Update Bulk Post types.
	 *
	 * This function adds the EA post types to the
	 * list of post types available to DeepL for translation.
	 *
	 * @since 1.0.0
	 *
	 * @param string[] $post_types
	 * @return string[]
	 */
	public function update_bulk_post_types( $post_types ): array {
		$ea_post_types = [ 'sp_easy_accordion', 'sp_accordion_faqs' ];

		foreach ( $ea_post_types as $post_type ) {
			if ( ! in_array( $post_type, $post_types, true ) ) {
				$post_types[] = $post_type;
			}
		}

		return $post_types;
	}

	/**
	 * Handle EA Content.
	 *
	 * By default, Easy Accordion stores the content as
	 * serialized post meta strings. So, this needs to be handled
	 * specially in this function.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed[]  $strings_to_translate Strings to Translate.
	 * @param \WP_Post $WP_Post              WP Post object.
	 * @param string   $target_lang          Target Lang e.g. 'RU'
	 * @param string   $source_lang          Source Lang e.g. 'EN'
	 * @param string   $bulk Bulk            Bulk
	 * @param string   $bulk_action Bulk     Action e.g. 'create'.
	 *
	 * @return array
	 */
	public function handle_ea_content( $strings_to_translate, $WP_Post, $target_lang, $source_lang, $bulk, $bulk_action ): array {
		// Bail out, if not EA post type.
		if ( 'sp_easy_accordion' !== get_post_type( $WP_Post ) ) {
			return $strings_to_translate;
		}

		$content = get_post_meta( $WP_Post->ID, 'sp_eap_upload_options', true );

		// Bail out, if no content.
		if ( empty( $content['accordion_content_source'] ) ) {
			return $strings_to_translate;
		}

		// Populate strings to translate with EA strings.
		foreach ( $content['accordion_content_source'] as $key => $value ) {
			$title       = 'accordion_content_title';
			$description = 'accordion_content_description';

			$title_key       = sprintf( '%s-%s', $key, $title );
			$description_key = sprintf( '%s-%s', $key, $description );

			$strings_to_translate[ $title_key ]       = $value[ $title ] ?? '';
			$strings_to_translate[ $description_key ] = $value[ $description ] ?? '';
		}

		return $strings_to_translate;
	}

	/**
	 * Update Translated EA Content.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed[]  $post_array           Post Array.
	 * @param mixed[]  $strings_to_translate Strings to Translate.
	 * @param mixed    $response             WP_REST_Response or WP_Error.
	 * @param \WP_Post $WP_Post              WP Post object.
	 * @param mixed[]  $no_translation       [ 'source_lang' => 'EN', 'target_lang' => 'RU' ]
	 * @param string   $bulk Bulk            Bulk
	 * @param string   $bulk_action Bulk     Action e.g. 'create'.
	 *
	 * @return void
	 */
	public function update_translated_ea_content( $post_array, $strings_to_translate, $response, $WP_Post, $no_translation, $bulk, $bulk_action ): void {
		// Bail out, if not EA post type.
		if ( 'sp_easy_accordion' !== get_post_type( $WP_Post ) ) {
			return;
		}

		// Get list of WP core keys.
		$wp_core_keys = [ 'post_title', 'post_content', 'post_excerpt' ];

		// Unset core WP keys.
		foreach ( $wp_core_keys as $key ) {
			if ( isset( $post_array[ $key ] ) ) {
				unset( $post_array[ $key ] );
			}
		}

		$content = get_post_meta( $WP_Post->ID, 'sp_eap_upload_options', true );

		// Get accordion content array.
		$translated_content = [];

		foreach ( $post_array as $meta_key => $meta_value ) {
			if ( false === strpos( $meta_key, '-' ) ) {
				continue;
			}

			[ $index, $key ] = explode( '-', $meta_key ) + [ '', '' ];

			if ( is_numeric( $index ) ) {
				$i                                = absint( $index );
				$translated_content[ $i ][ $key ] = $meta_value;

				/**
				 * Finally, remove post meta that was added
				 * by DeepL Service in the same filter earlier on.
				 *
				 * We do this to ensure that the DB postmeta table
				 * is not polluted with unnecessary post meta that is
				 * not used by Easy Accordion.
				 *
				 * @since 1.0.0
				 */
				delete_post_meta( $WP_Post->ID, $meta_key, $meta_value );
			}
		}

		// Substitute content for translation.
		$content['accordion_content_source'] = $translated_content;

		// Update content.
		update_post_meta( $WP_Post->ID, 'sp_eap_upload_options', $content );
	}
}
