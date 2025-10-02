<?php
/**
 * Container class.
 *
 * This class is responsible for registering the
 * plugin's services.
 *
 * @package AddonForPostMetaTranslationUsingDeepL
 */

namespace AddonForPostMetaTranslationUsingDeepL\Core;

use AddonForPostMetaTranslationUsingDeepL\Interfaces\Kernel;
use AddonForPostMetaTranslationUsingDeepL\Services\DeepL;
use AddonForPostMetaTranslationUsingDeepL\Services\EasyAccordion;

class Container implements Kernel {
	/**
	 * Services.
	 *
	 * @since 1.0.0
	 *
	 * @var mixed[]
	 */
	public static array $services = [];

	/**
	 * Prepare Singletons.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		static::$services = [
			DeepL::class,
			EasyAccordion::class,
		];
	}

	/**
	 * Register Service.
	 *
	 * Establish singleton version for each Service
	 * concrete class.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register(): void {
		foreach ( static::$services as $service ) {
			( $service::get_instance() )->register();
		}
	}
}
