# addon-for-post-meta-translation-using-deepl

Translate post meta data when using DeepL translate.

<img width="626" height="235" alt="screenshot-3" src="https://github.com/user-attachments/assets/b1a1b142-9bca-4913-b382-de482df65f59" />

## Why Addon for Post Meta Translation using DeepL?

By default, the __DeepL__ plugin only translates the post `title`, `content` and `excerpt`.

This plugin enables you to translate post meta data using __DeepL__. It integrates nicely with the __DeepL__ & __DeepL pro__ plugin and is useful for translating post meta for popular plugins like __WooCommerce__, __Yoast SEO__, __EasyAccordion__ and so on.

<img width="666" height="682" alt="screenshot-1" src="https://github.com/user-attachments/assets/7bcb6c1b-7e81-4a2c-b7cd-a0559f37c4af" />

---

<img width="626" height="235" alt="screenshot-3" src="https://github.com/user-attachments/assets/9682a73c-7725-4485-92ef-6a0a1178956e" />

## Hooks

### PHP Hooks

#### `addon_for_post_meta_translation_using_deepl_excluded_meta_keys`

This custom hook (filter) provides a way to exclude specific meta keys from being translated alongside others.

```php
add_filter( 'addon_for_post_meta_translation_using_deepl_excluded_meta_keys', [ $this, 'exclude_meta_keys' ], 10, 1 );

public function exclude_meta_keys( $meta_keys ) {
    $meta_keys = [ 'meta_key_1', 'meta_key_2' ];

    return $meta_keys;
}
```

**Parameters**

- meta_keys _`{string[]}`_ By default this will be an empty string array which could contain post meta keys you wish to exclude from translation.
<br/>

## Contribute

Contributions are __welcome__ and will be fully __credited__. To contribute, please fork this repo and raise a PR (Pull Request) against the `master` branch.

### Pre-requisites

You should have the following tools before proceeding to the next steps:

- Composer
- Yarn
- Docker

To enable you start development, please run:

```bash
yarn start
```

This should spin up a local WP env instance for you to work with at:

```bash
http://addon-for-post-meta-translation-using-deepl.localhost:9929
```

You should now have a functioning local WP env to work with. To login to the `wp-admin` backend, please username as `admin` & password as `password`.

__Awesome!__ - Thanks for being interested in contributing your time and code to this project!
