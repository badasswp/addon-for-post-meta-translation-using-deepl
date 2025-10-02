<?php

namespace AddonForPostMetaTranslationUsingDeepL\Tests\Core;

use Mockery;
use WP_Mock\Tools\TestCase;
use AddonForPostMetaTranslationUsingDeepL\Core\Container;
use AddonForPostMetaTranslationUsingDeepL\Services\DeepL;
use AddonForPostMetaTranslationUsingDeepL\Services\EasyAccordion;

/**
 * @covers \AddonForPostMetaTranslationUsingDeepL\Core\Container::__construct
 * @covers \AddonForPostMetaTranslationUsingDeepL\Services\DeepL::register
 * @covers \AddonForPostMetaTranslationUsingDeepL\Services\EasyAccordion::register
 */
class ContainerTest extends TestCase {
	public Container $container;

	public function setUp(): void {
		\WP_Mock::setUp();
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	public function test_container_contains_required_services() {
		$this->container = new Container();

		$this->assertTrue( in_array( DeepL::class, Container::$services, true ) );
		$this->assertTrue( in_array( EasyAccordion::class, Container::$services, true ) );
	}
}
