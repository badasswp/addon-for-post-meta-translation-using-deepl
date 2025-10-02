<?php
/**
 * DeepL Service.
 *
 * This service extends the translation functionality
 * to post meta values of a translated post using DeepL.
 *
 * @package AddonForPostMetaTranslationUsingDeepL
 */

namespace AddonForPostMetaTranslationUsingDeepL\Services;

use AddonForPostMetaTranslationUsingDeepL\Abstracts\Service;
use AddonForPostMetaTranslationUsingDeepL\Interfaces\Kernel;

class DeepL extends Service implements Kernel {
	/**
	 * Bind to WP.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register(): void {
		add_action(
			'deepl_translate_after_post_update',
			[ $this, 'update_translated_post_meta' ],
			10,
			7
		);

		add_filter(
			'deepl_translate_post_link_strings',
			[ $this, 'add_post_meta_to_list_of_strings_to_translate' ],
			10,
			6
		);
	}

	/**
	 * Modify strings to translate.
	 *
	 * By default DeepL only translates core WP Post fields
	 * like title, content, excerpt. This function extends the
	 * translation functionality to post meta.
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
	public function add_post_meta_to_list_of_strings_to_translate( $strings_to_translate, $WP_Post, $target_lang, $source_lang, $bulk, $bulk_action ): array {
		// Get all post meta for the given post ID
		$all_meta = get_post_meta( $WP_Post->ID );

		foreach ( $all_meta as $meta_key => $meta_value ) {
			// Exclude disallowed meta keys.
			if ( in_array( $meta_key, $this->get_excluded_meta_keys(), true ) ) {
				continue;
			}

			// Get meta value.
			$meta_value = get_post_meta( $WP_Post->ID, $meta_key, true );

			// Only translate strings.
			if ( is_string( $meta_value ) ) {
				$strings_to_translate[ $meta_key ] = $meta_value;
			}
		}

		return $strings_to_translate;
	}

	/**
	 * Update Translated Post Meta.
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
	public function update_translated_post_meta( $post_array, $strings_to_translate, $response, $WP_Post, $no_translation, $bulk, $bulk_action ): void {
		// Get list of WP core keys.
		$wp_core_keys = [ 'post_title', 'post_content', 'post_excerpt' ];

		// Unset core WP keys.
		foreach ( $wp_core_keys as $key ) {
			if ( isset( $post_array[ $key ] ) ) {
				unset( $post_array[ $key ] );
			}
		}

		foreach ( $post_array as $meta_key => $meta_value ) {
			update_post_meta( $WP_Post->ID, $meta_key, $post_array[ $meta_key ] );
		}
	}

	/**
	 * Get Excluded meta keys.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_excluded_meta_keys(): array {
		/**
		 * Filter excluded meta keys.
		 *
		 * We provide a way to enable users to exclude
		 * specific meta keys from being translated by DeepL.
		 *
		 * @since 1.0.0
		 *
		 * @param string[] $excluded_meta_keys Excluded meta keys.
		 * @return string[]
		 */
		return apply_filters( 'addon_for_post_meta_translation_using_deepl_excluded_meta_keys', [] );
	}
}
