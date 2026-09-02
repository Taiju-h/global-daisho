<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'daisho' );

/** Database username */
define( 'DB_USER', 'daisho' );

/** Database password */
define( 'DB_PASSWORD', 'vRIhSYE6' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '3=)u5+ @`xCX:r.G rJKwLRglL+w+5N Mx@q|dgoQ?Zh=&*ETn([.I[X8<SI48%y' );
define( 'SECURE_AUTH_KEY',  'i+o{vhmN5ZPWk.Ipkvp.,XxMkfhYz5BUe!;9;o040I%kV{YkjM pX;+h]p3ng77u' );
define( 'LOGGED_IN_KEY',    'SSCWu2gl]}5cjhhyWNOOfhU>f  y-C([ci rjKtZZ8ar2wQ0rlF^f vO>bi=n1kX' );
define( 'NONCE_KEY',        'FKBZzt+dU4N0cM0}v6Cqs9!Vt{-;U:j).Zua.h4OwY^}OvHx)~hRpuhm]jl7it:y' );
define( 'AUTH_SALT',        '+U^U%,?0G ^8PM#Riu6u;x*Xjw=vCyybtUa~_:R0&Y3]gQkJH[.`vBPmNMGj{.w,' );
define( 'SECURE_AUTH_SALT', 'hEe4{CzVZe~vVxF45!%Q]Rfzt;h8goQ3gW1+rV{DZ5to^@Hs ZbC|?0?@>H9A*K?' );
define( 'LOGGED_IN_SALT',   'y4_XsX;bye}VH=:}LF*V@8:kZ_V:Wz9MR067W]zezJChch-w,Id:l-*adT!Ymb/o' );
define( 'NONCE_SALT',       '2qGkaF5<*>aFf-9NOGLp{cb}Ir=oO/~8D#.gHKR@xHq!9Y)yPG>;|a|KY y` uL;' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'daisho_wp';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
