<?php
/**
 * IDE-only stubs for WordPress runtime-defined constants that the wordpress-stubs
 * package omits because they're set dynamically in wp-load.php / wp-config.php.
 * Never required at runtime; the file lives outside any WP scan path.
 */

if ( false ) {
    define( 'ABSPATH', '/var/www/html/' );
    define( 'WPINC', 'wp-includes' );
    define( 'WP_DEBUG', false );
    define( 'WP_DEBUG_LOG', false );
    define( 'WP_DEBUG_DISPLAY', true );
    define( 'WP_HOME', '' );
    define( 'WP_SITEURL', '' );
    define( 'KUMIAI_ENV', 'development' );
    define( 'DISALLOW_FILE_EDIT', false );
    define( 'AUTOMATIC_UPDATER_DISABLED', false );
    define( 'FORCE_SSL_ADMIN', false );
}
