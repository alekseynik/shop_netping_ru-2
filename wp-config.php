<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
$db_name = getenv("DBPROD_NAME");
$db_user = getenv("DBPROD_USER");
$db_host = getenv("DBPROD_host");
$db_password = getenv("DBPROD_PASSWORD");

define( 'DB_NAME', $db_name );

/** MySQL database username */
define( 'DB_USER', $db_user );

/** MySQL database password */
define( 'DB_PASSWORD', $db_password );

/** MySQL hostname */
define( 'DB_HOST', $db_host );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'a>kyu#(na7-gc=#Ya$e5=s5>^$t<BE*0J/<Ms?_IO][.cQQ:@*%DnC7@s9g2KCCz' );
define( 'SECURE_AUTH_KEY',   'd0Ip;X_<J=28$iry;PK-)>Q5{5 p%w{DbZ. =:EE[n(|v7zmsG_;a;Ah+PgV],?#' );
define( 'LOGGED_IN_KEY',     'p>{AAZ3vJ&:CI?zC-s oojxF].nB&@$~LtO>j? K{Zzu$jT=)]!.Dn{C1#,nj+o=' );
define( 'NONCE_KEY',         '94rGTXUP2X){rJ#_+F62U[h.C4q:}F&dW59V{O5:n@T6Kg[^8vnsJL)>SyZ}|N:[' );
define( 'AUTH_SALT',         '4f3P4sqlO%$J6$YTiOf7bM}fe9N2W}LIKmb]?RdnC&1.2u]=p6T``c(V7_$|=cJ8' );
define( 'SECURE_AUTH_SALT',  'C-F-N4FngMoF){8}M1l5,_-@f)d+Ax)JGt[w[<#0^chk^=Q>1AQt@0T1)q<6(fX3' );
define( 'LOGGED_IN_SALT',    '!re}7EXB6$_PZRdr{h[#|`aho5P:AjL#t^n3pp[#[]7?SS-%Cw!`$5fBfvB%E(NS' );
define( 'NONCE_SALT',        't+LrRf,#-FOUC<z(L0}l76;IPz~}mrzdWDIe9]#Gw7H{vcxA/FydMWr(,SG5RPVm' );
define( 'WP_CACHE_KEY_SALT', 'IBVG9/iCg#wh:OFB~^i!<U)Htz1W0U-!sll,SIlHp)iE)[9aA.K>NZo8TG):A>87' );

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';




define( 'WP_DEBUG', true );
define( 'WP_DEBUG_DISPLAY', true );
define( 'WP_DEBUG_LOG', true );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
