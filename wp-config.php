<?php

/**

 * The base configuration for WordPress

 *

 * The wp-config.php creation script uses this file during the installation.

 * You don't have to use the web site, you can copy this file to "wp-config.php"

 * and fill in the values.

 *

 * This file contains the following configurations:

 *

 * * MySQL settings

 * * Secret keys

 * * Database table prefix

 * * ABSPATH

 *

 * @link https://wordpress.org/support/article/editing-wp-config-php/

 *

 * @package WordPress

 */


// ** MySQL settings - You can get this info from your web host ** //

/** The name of the database for WordPress */

define( 'DB_NAME', 'wp_lwehg' );


/** MySQL database username */

define( 'DB_USER', 'wp_ev1ty' );


/** MySQL database password */

define( 'DB_PASSWORD', '^nU1b1j84' );


/** MySQL hostname */

define( 'DB_HOST', '203.150.225.102:3306' );


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

define( 'AUTH_KEY',         'DfT|ex#~FP0ne9V_fK}gYfac@v[D:=j#l29Z@@GM>g,Lc=?_p0m F+5k,Cp2]#q5' );

define( 'SECURE_AUTH_KEY',  'uGm5Wgw3Mnq|;OZ&8Zi.%@Fazh.Avjzb%,vq=x;zPVH56+[oSmV{[s*mzL}J_6H%' );

define( 'LOGGED_IN_KEY',    'pzb|omt`Yh%N;6IJZ4;yWDnM0zb%-~!EF=vMG980NNnDV%|VPd.^qV*,<;(.AjEW' );

define( 'NONCE_KEY',        'l+8R-5-iW+W1,x5@Y&&:,{D>{kn9m5t^d*9+{iiJno`[>A&Ux@OCx0X9pmw{/.DS' );

define( 'AUTH_SALT',        '1{GGk5q2+$`ir 5l4)qBA1x4RM!Q?$Ct9ep#]ArFA,VdTX[bH+]uy>IpmCw.,;0F' );

define( 'SECURE_AUTH_SALT', 'b1A$i=ZMN& 7%uVWZ7leJI@3G/0DIwvjDewVS.v?v<lxhw)BVxfZTMR0p?FH_}Tm' );

define( 'LOGGED_IN_SALT',   'n}bAUmw--!#>UW+Ei.yi;fU9XrK0-/8<ptuZ=E3nEc6<]CgbpX)#nE83@43)({ U' );

define( 'NONCE_SALT',       'AuXY.p$[dGd9Jm~:7`$V 2zqBzdWOmoeue^B,+JGJ5nd%^$}j0C BE/m|f3U0HuD' );


/**#@-*/


/**

 * WordPress database table prefix.

 *

 * You can have multiple installations in one database if you give each

 * a unique prefix. Only numbers, letters, and underscores please!

 */

$table_prefix = 'wp_';


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

 * @link https://wordpress.org/support/article/debugging-in-wordpress/

 */

define( 'WP_DEBUG', true );
// define( 'WP_DEBUG', true );

// Enable WP_DEBUG mode


/* Add any custom values between this line and the "stop editing" line. */


define( 'WP_DEBUG_DISPLAY', true );
/* That's all, stop editing! Happy publishing. */


/** Absolute path to the WordPress directory. */

if ( ! defined( 'ABSPATH' ) ) {

	define( 'ABSPATH', __DIR__ . '/' );

}


/** Sets up WordPress vars and included files. */

require_once ABSPATH . 'wp-settings.php';