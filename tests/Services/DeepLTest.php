<?php

namespace AddonForPostMetaTranslationUsingDeepL\Tests\Services;

use WP_Mock;
use WP_Post;
use Mockery;
use WP_Mock\Tools\TestCase;
use AddonForPostMetaTranslationUsingDeepL\Services\DeepL;

/**
 * @covers \AddonForPostMetaTranslationUsingDeepL\Services\DeepL::register
 * @covers \AddonForPostMetaTranslationUsingDeepL\Services\DeepL::add_post_meta_to_list_of_strings_to_translate
 * @covers \AddonForPostMetaTranslationUsingDeepL\Services\DeepL::update_translated_post_meta
 */
class DeepLTest extends TestCase {
	public DeepL $deepl;

	public function setUp(): void {
		WP_Mock::setUp();

		WP_Mock::userFunction( 'absint' )
			->andReturnUsing( fn( $arg ) => intval( $arg ) );

		$this->deepl = new DeepL();
	}

	public function tearDown(): void {
		WP_Mock::tearDown();
	}

	public function test_register() {
		WP_Mock::expectActionAdded( 'deepl_translate_after_post_update', [ $this->deepl, 'update_translated_post_meta' ], 10, 7 );
		WP_Mock::expectFilterAdded( 'deepl_translate_post_link_strings', [ $this->deepl, 'add_post_meta_to_list_of_strings_to_translate' ], 10, 6 );

		$register = $this->deepl->register();

		$this->assertHooksAdded();
		$this->assertConditionsMet();
	}

	public function test_add_post_meta_to_list_of_strings_to_translate_returns_default_if_post_meta_is_empty() {
		WP_Mock::userFunction( 'get_post_meta' )
			->andReturn( [] );

		$strings_to_translate = [
			'post_title'   => 'Hello World',
			'post_content' => 'Welcome to WordPress!',
			'post_excerpt' => '',
		];

		$WP_Post     = Mockery::mock( WP_Post::class )->makePartial();
		$WP_Post->ID = 1;

		$response = $this->deepl->add_post_meta_to_list_of_strings_to_translate(
			$strings_to_translate,
			$WP_Post,
			'RU',
			'EN',
			'bulk',
			'create'
		);

		$this->assertSame( $response, $strings_to_translate );
		$this->assertConditionsMet();
	}

	public function test_add_post_meta_to_list_of_strings_to_translate_returns_new_array_with_post_meta_that_contains_only_string() {
		WP_Mock::userFunction( 'get_post_meta' )
			->andReturnUsing(
				function ( $arg1, $arg2 = null, $arg3 = null ) {
					$post_meta = [
						'post_meta_key_1' => 'What a Wonderful World!',
						'post_meta_key_2' => 'The Adventures of Huckleberry Finn.',
						'post_meta_key_3' => [
							'Such a jolly good fellow!',
							'Keep Calm! Carry on Coding!',
						],
					];

					if ( null === $arg2 && null === $arg3 ) {
						return $post_meta;
					}

					return $post_meta[ $arg2 ];
				}
			);

		WP_Mock::expectFilter( 'addon_for_post_meta_translation_using_deepl_excluded_meta_keys', [] );

		$strings_to_translate = [
			'post_title'   => 'Hello World',
			'post_content' => 'Welcome to WordPress!',
			'post_excerpt' => '',
		];

		$WP_Post     = Mockery::mock( WP_Post::class )->makePartial();
		$WP_Post->ID = 1;

		$response = $this->deepl->add_post_meta_to_list_of_strings_to_translate(
			$strings_to_translate,
			$WP_Post,
			'RU',
			'EN',
			'bulk',
			'create'
		);

		$this->assertSame(
			$response,
			[
				'post_title'      => 'Hello World',
				'post_content'    => 'Welcome to WordPress!',
				'post_excerpt'    => '',
				'post_meta_key_1' => 'What a Wonderful World!',
				'post_meta_key_2' => 'The Adventures of Huckleberry Finn.',
			]
		);
		$this->assertConditionsMet();
	}

	public function test_add_post_meta_to_list_of_strings_to_translate_returns_new_array_and_excludes_meta_keys_mentioned_in_the_filter() {
		WP_Mock::userFunction( 'get_post_meta' )
			->andReturnUsing(
				function ( $arg1, $arg2 = null, $arg3 = null ) {
					$post_meta = [
						'post_meta_key_1' => 'What a Wonderful World!',
						'post_meta_key_2' => 'The Adventures of Huckleberry Finn.',
						'post_meta_key_3' => [
							'Such a jolly good fellow!',
							'Keep Calm! Carry on Coding!',
						],
					];

					if ( null === $arg2 && null === $arg3 ) {
						return $post_meta;
					}

					return $post_meta[ $arg2 ];
				}
			);

		WP_Mock::onFilter( 'addon_for_post_meta_translation_using_deepl_excluded_meta_keys' )
			->with( [] )
			->reply( [ 'post_meta_key_2' ] );

		$strings_to_translate = [
			'post_title'   => 'Hello World',
			'post_content' => 'Welcome to WordPress!',
			'post_excerpt' => '',
		];

		$WP_Post     = Mockery::mock( WP_Post::class )->makePartial();
		$WP_Post->ID = 1;

		$response = $this->deepl->add_post_meta_to_list_of_strings_to_translate(
			$strings_to_translate,
			$WP_Post,
			'RU',
			'EN',
			'bulk',
			'create'
		);

		$this->assertSame(
			$response,
			[
				'post_title'      => 'Hello World',
				'post_content'    => 'Welcome to WordPress!',
				'post_excerpt'    => '',
				'post_meta_key_1' => 'What a Wonderful World!',
			]
		);
		$this->assertConditionsMet();
	}

	public function test_update_translated_post_meta_bails_if_no_post_meta() {
		$post_array = [
			'post_title'   => 'Hallo Welt',
			'post_content' => 'Willkommen bei WordPress!',
			'post_excerpt' => '',
		];

		$strings_to_translate = [
			'post_title'   => 'Hello World',
			'post_content' => 'Welcome to WordPress!',
			'post_excerpt' => '',
		];

		$WP_Post     = Mockery::mock( WP_Post::class )->makePartial();
		$WP_Post->ID = 1;

		$response = $this->deepl->update_translated_post_meta(
			$post_array,
			$strings_to_translate,
			Mockery::mock( \WP_REST_Response::class )->makePartial(),
			$WP_Post,
			[
				'source_lang' => 'EN',
				'target_lang' => 'DE',
			],
			'bulk',
			'create'
		);

		$this->assertNull( $response );
		$this->assertConditionsMet();
	}

	public function test_update_translated_post_meta_updates_and_runs_twice() {
		$post_array = [
			'post_title'      => 'Hallo Welt',
			'post_content'    => 'Willkommen bei WordPress!',
			'post_excerpt'    => '',
			'post_meta_key_1' => 'Was für eine wundervolle Welt!',
			'post_meta_key_2' => 'Die Abenteuer des Huckleberry Finn.',
		];

		$strings_to_translate = [
			'post_title'      => 'Hello World',
			'post_content'    => 'Welcome to WordPress!',
			'post_excerpt'    => '',
			'post_meta_key_1' => 'What a Wonderful World!',
			'post_meta_key_2' => 'The Adventures of Huckleberry Finn.',
		];

		$WP_Post     = Mockery::mock( WP_Post::class )->makePartial();
		$WP_Post->ID = 1;

		WP_Mock::userFunction( 'update_post_meta' )
			->twice();

		$response = $this->deepl->update_translated_post_meta(
			$post_array,
			$strings_to_translate,
			Mockery::mock( \WP_REST_Response::class )->makePartial(),
			$WP_Post,
			[
				'source_lang' => 'EN',
				'target_lang' => 'DE',
			],
			'bulk',
			'create'
		);

		$this->assertNull( $response );
		$this->assertConditionsMet();
	}

	public function test_get_excluded_meta_keys() {
		WP_Mock::onFilter( 'addon_for_post_meta_translation_using_deepl_excluded_meta_keys' )
			->with( [] )
			->reply( [ 'post_meta_key_1' ] );

		$response = $this->deepl->get_excluded_meta_keys();

		$this->assertSame( [ 'post_meta_key_1' ], $response );
		$this->assertConditionsMet();
	}
}
