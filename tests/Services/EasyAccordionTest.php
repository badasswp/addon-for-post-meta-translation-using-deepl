<?php

namespace AddonForPostMetaTranslationUsingDeepL\Tests\Services;

use WP_Mock;
use Mockery;
use WP_Mock\Tools\TestCase;
use AddonForPostMetaTranslationUsingDeepL\Services\EasyAccordion;

/**
 * @covers \AddonForPostMetaTranslationUsingDeepL\Services\EasyAccordion::register
 * @covers \AddonForPostMetaTranslationUsingDeepL\Services\EasyAccordion::translate_ea_post_types
 * @covers \AddonForPostMetaTranslationUsingDeepL\Services\EasyAccordion::update_bulk_post_types
 * @covers \AddonForPostMetaTranslationUsingDeepL\Services\EasyAccordion::handle_ea_content
 * @covers \AddonForPostMetaTranslationUsingDeepL\Services\EasyAccordion::update_translated_ea_content
 */
class EasyAccordionTest extends TestCase {
	public EasyAccordion $easy_accordion;

	public function setUp(): void {
		WP_Mock::setUp();

		WP_Mock::userFunction( 'absint' )
			->andReturnUsing( fn( $arg ) => intval( $arg ) );

		$this->easy_accordion = new EasyAccordion();
	}

	public function tearDown(): void {
		WP_Mock::tearDown();
	}

	public function test_register() {
		WP_Mock::expectActionAdded( 'admin_init', [ $this->easy_accordion, 'translate_ea_post_types' ] );
		WP_Mock::expectActionAdded( 'deepl_translate_after_post_update', [ $this->easy_accordion, 'update_translated_ea_content' ], 20, 7 );
		WP_Mock::expectFilterAdded( 'deepl_translate_post_link_strings', [ $this->easy_accordion, 'handle_ea_content' ], 100, 6 );

		$register = $this->easy_accordion->register();

		$this->assertNull( $register );
		$this->assertConditionsMet();
	}

	public function test_translate_ea_post_types_bails_if_deepl_pro_configuration_class_does_not_exist() {
		$response = $this->easy_accordion->translate_ea_post_types();

		$this->assertNull( $response );
		$this->assertConditionsMet();
	}

	public function test_translate_ea_post_types_updates_bulk_post_types() {
		if ( ! class_exists( 'DeepLProConfiguration', false ) ) {
			eval( 'class DeepLProConfiguration {}' );
		}

		WP_Mock::expectFilterAdded(
			'DeepLProConfiguration::getProBulkPostTypes',
			[ $this->easy_accordion, 'update_bulk_post_types' ]
		);

		$response = $this->easy_accordion->translate_ea_post_types();

		$this->assertHooksAdded();
		$this->assertNull( $response );
		$this->assertConditionsMet();
	}

	public function test_update_bulk_post_types_adds_ea_post_types() {
		$response = $this->easy_accordion->update_bulk_post_types( [ 'post', 'page' ] );

		$this->assertSame(
			$response,
			[
				'post',
				'page',
				'sp_easy_accordion',
				'sp_accordion_faqs',
			]
		);
	}

	public function test_update_bulk_post_types_adds_ea_post_types_if_they_are_not_already_in_bulk_array() {
		$response = $this->easy_accordion->update_bulk_post_types( [ 'post', 'page', 'sp_accordion_faqs' ] );

		$this->assertSame(
			$response,
			[
				'post',
				'page',
				'sp_accordion_faqs',
				'sp_easy_accordion',
			]
		);
	}

	public function test_handle_ea_content_bails_out_if_not_easy_accordion_post_type() {
		WP_Mock::userFunction( 'get_post_type' )
			->andReturn( 'post' );

		$strings_to_translate = [
			'post_title'   => 'Hello World',
			'post_content' => 'Welcome to WordPress!',
			'post_excerpt' => '',
		];

		$WP_Post     = Mockery::mock( \WP_Post::class )->makePartial();
		$WP_Post->ID = 1;

		$response = $this->easy_accordion->handle_ea_content(
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

	public function test_handle_ea_content_bails_out_if_empty_accordion_content() {
		WP_Mock::userFunction( 'get_post_type' )
			->andReturn( 'sp_easy_accordion' );

		WP_Mock::userFunction( 'get_post_meta' )
			->with( 1, 'sp_eap_upload_options', true )
			->andReturn(
				[
					'accordion_content_source' => [],
				]
			);

		$strings_to_translate = [
			'post_title'   => 'Hello World',
			'post_content' => 'Welcome to WordPress!',
			'post_excerpt' => '',
		];

		$WP_Post     = Mockery::mock( \WP_Post::class )->makePartial();
		$WP_Post->ID = 1;

		$response = $this->easy_accordion->handle_ea_content(
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

	public function test_handle_ea_content_populates_strings_to_translate_with_ea_strings() {
		WP_Mock::userFunction( 'get_post_type' )
			->andReturn( 'sp_easy_accordion' );

		WP_Mock::userFunction( 'get_post_meta' )
			->with( 1, 'sp_eap_upload_options', true )
			->andReturn(
				[
					'accordion_content_source' => [
						[
							'accordion_content_title' => 'Title 1',
							'accordion_content_description' => 'Description 1',
						],
					],
				]
			);

		$strings_to_translate = [
			'post_title'   => 'Hello World',
			'post_content' => 'Welcome to WordPress!',
			'post_excerpt' => '',
		];

		$WP_Post     = Mockery::mock( \WP_Post::class )->makePartial();
		$WP_Post->ID = 1;

		$response = $this->easy_accordion->handle_ea_content(
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
				'post_title'                      => 'Hello World',
				'post_content'                    => 'Welcome to WordPress!',
				'post_excerpt'                    => '',
				'0-accordion_content_title'       => 'Title 1',
				'0-accordion_content_description' => 'Description 1',
			]
		);
		$this->assertConditionsMet();
	}

	public function test_update_translated_ea_content_bails_if_not_ea_post_type() {
		WP_Mock::userFunction( 'get_post_type' )
			->andReturn( 'post' );

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

		$WP_Post     = Mockery::mock( \WP_Post::class )->makePartial();
		$WP_Post->ID = 1;

		$response = $this->easy_accordion->update_translated_ea_content(
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

	public function test_update_translated_ea_content_updates_only_ea_content() {
		WP_Mock::userFunction( 'get_post_type' )
			->andReturn( 'sp_easy_accordion' );

		WP_Mock::userFunction( 'get_post_meta' )
			->with( 1, 'sp_eap_upload_options', true )
			->andReturn(
				[
					'accordion_content_source' => [
						[
							'accordion_content_title' => 'Title 1',
							'accordion_content_description' => 'Description 1',
						],
						[
							'accordion_content_title' => 'Title 2',
							'accordion_content_description' => 'Description 2',
						],
					],
				]
			);

		WP_Mock::userFunction( 'delete_post_meta' )
			->times( 4 );

		WP_Mock::userFunction( 'update_post_meta' )
			->once()
			->with(
				1,
				'sp_eap_upload_options',
				[
					'accordion_content_source' => [
						[
							'accordion_content_title' => 'Titel 1',
							'accordion_content_description' => 'Beschreibung 1',
						],
						[
							'accordion_content_title' => 'Titel 2',
							'accordion_content_description' => 'Beschreibung 2',
						],
					],
				]
			);

		$post_array = [
			'post_title'                      => 'Hallo Welt',
			'post_content'                    => 'Willkommen bei WordPress!',
			'post_excerpt'                    => '',
			'0-accordion_content_title'       => 'Titel 1',
			'0-accordion_content_description' => 'Beschreibung 1',
			'1-accordion_content_title'       => 'Titel 2',
			'1-accordion_content_description' => 'Beschreibung 2',
			'other_data'                      => '',
			'other-data'                      => '',
		];

		$strings_to_translate = [
			'post_title'                      => 'Hello World',
			'post_content'                    => 'Welcome to WordPress!',
			'post_excerpt'                    => '',
			'0-accordion_content_title'       => 'Title 1',
			'0-accordion_content_description' => 'Description 1',
			'1-accordion_content_title'       => 'Title 2',
			'1-accordion_content_description' => 'Description 2',
		];

		$WP_Post     = Mockery::mock( \WP_Post::class )->makePartial();
		$WP_Post->ID = 1;

		$response = $this->easy_accordion->update_translated_ea_content(
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
}
