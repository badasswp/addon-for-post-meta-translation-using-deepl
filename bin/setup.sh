#!/bin/bash

wp-env run cli wp theme activate twentytwentythree
wp-env run cli wp rewrite structure /%postname%
wp-env run cli wp option update blogname "Addon for Post Meta Translation using DeepL"
wp-env run cli wp option update blogdescription "Translate post meta data when using DeepL translate."
