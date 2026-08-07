<?php
/**
 * Doublures minimales des fonctions WordPress.
 *
 * Juste assez pour charger inc/defaults.php et inc/leads.php hors de
 * WordPress et vérifier la logique de tirage. Ce n'est pas une émulation
 * de WordPress : tout ce qui touche à la base ou au réseau est neutralisé.
 *
 * @package JeuxMarketing
 */

define( 'ABSPATH', __DIR__ );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'YEAR_IN_SECONDS', 31536000 );

$GLOBALS['jmk_options']    = array();
$GLOBALS['jmk_transients'] = array();
$GLOBALS['jmk_actions']    = array();

function get_option( $k, $d = false ) {
	return array_key_exists( $k, $GLOBALS['jmk_options'] ) ? $GLOBALS['jmk_options'][ $k ] : $d;
}
function update_option( $k, $v, $a = null ) {
	$GLOBALS['jmk_options'][ $k ] = $v;
	return true;
}
function delete_option( $k ) {
	unset( $GLOBALS['jmk_options'][ $k ] );
	return true;
}
function set_transient( $k, $v, $t = 0 ) {
	$GLOBALS['jmk_transients'][ $k ] = $v;
	return true;
}
function get_transient( $k ) {
	return array_key_exists( $k, $GLOBALS['jmk_transients'] ) ? $GLOBALS['jmk_transients'][ $k ] : false;
}
function delete_transient( $k ) {
	unset( $GLOBALS['jmk_transients'][ $k ] );
	return true;
}

function add_action( $h, $c, $p = 10, $a = 1 ) {
	$GLOBALS['jmk_actions'][] = $h;
}
function add_filter( $h, $c, $p = 10, $a = 1 ) {}
function register_post_type( $t, $a = array() ) {}
function __( $s, $d = null ) {
	return $s;
}
function esc_html__( $s, $d = null ) {
	return $s;
}
function wp_rand( $min = 0, $max = 0 ) {
	return random_int( $min, $max );
}
function wp_generate_password( $len = 12, $special = true, $extra = false ) {
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$out   = '';
	for ( $i = 0; $i < $len; $i++ ) {
		$out .= $chars[ random_int( 0, strlen( $chars ) - 1 ) ];
	}
	return $out;
}

/** Interrompt le flux comme le fait wp_send_json_*. */
class JMK_Json_Response extends Exception {
	public $payload;
	public function __construct( $payload, $success ) {
		parent::__construct( 'json' );
		$this->payload = $payload;
		$this->success = $success;
	}
	public $success;
}
function wp_send_json_success( $d = null ) {
	throw new JMK_Json_Response( $d, true );
}
function wp_send_json_error( $d = null ) {
	throw new JMK_Json_Response( $d, false );
}
function check_ajax_referer( $a = -1, $q = false, $die = true ) {
	return true;
}
function sanitize_text_field( $s ) {
	return trim( strip_tags( (string) $s ) );
}
function sanitize_email( $s ) {
	return filter_var( (string) $s, FILTER_SANITIZE_EMAIL );
}
function is_email( $s ) {
	return (bool) filter_var( (string) $s, FILTER_VALIDATE_EMAIL );
}
function wp_unslash( $s ) {
	return is_string( $s ) ? stripslashes( $s ) : $s;
}
function get_posts( $a = array() ) {
	return array();
}
function wp_insert_post( $a, $e = false ) {
	static $id = 100;
	return ++$id;
}
function is_wp_error( $t ) {
	return false;
}
function update_post_meta( $i, $k, $v ) {
	$GLOBALS['jmk_meta'][ $i ][ $k ] = $v;
	return true;
}
function wp_remote_post( $u, $a = array() ) {
	return array();
}
function wp_mail( $to, $s, $m ) {
	return true;
}
function wp_json_encode( $d ) {
	return json_encode( $d );
}
function current_time( $t ) {
	return gmdate( 'c' );
}
function home_url() {
	return 'https://exemple.test';
}
function current_user_can( $c ) {
	return true;
}

function sanitize_key( $k ) {
	return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $k ) );
}
function is_admin() {
	return false;
}
function is_ssl() {
	return false;
}
function esc_html( $s ) {
	return htmlspecialchars( (string) $s, ENT_QUOTES );
}
function esc_attr( $s ) {
	return htmlspecialchars( (string) $s, ENT_QUOTES );
}
function esc_url( $s ) {
	return (string) $s;
}
function add_query_arg() {
	$a = func_get_args();
	if ( 3 === count( $a ) ) {
		$url = $a[2];
		$q   = array( $a[0] => $a[1] );
	} elseif ( 2 === count( $a ) && is_array( $a[0] ) ) {
		$url = $a[1];
		$q   = $a[0];
	} else {
		return isset( $a[0] ) ? (string) $a[0] : '';
	}
	$sep = ( false === strpos( (string) $url, '?' ) ) ? '?' : '&';
	return $url . $sep . http_build_query( $q );
}

/* ── juste ce qu'il faut pour inc/builder.php ── */
function add_menu_page() {}
function add_submenu_page() {}
function wp_enqueue_style() {}
function wp_localize_script() {}
function wp_nonce_field() {}
function check_admin_referer( $a = -1, $q = '_wpnonce' ) {
	return true;
}
function admin_url( $p = '' ) {
	return 'https://exemple.test/wp-admin/' . $p;
}
function wp_die( $m = '' ) {
	throw new RuntimeException( (string) $m );
}
/** Signale une redirection : le exit qui la suit couperait les tests. */
class JmkRedirect extends RuntimeException {}

function wp_safe_redirect( $u, $s = 302 ) {
	$GLOBALS['jmk_redirect'] = $u;
	throw new JmkRedirect( (string) $u );
}
function nocache_headers() {}
function sanitize_hex_color( $c ) {
	$c = trim( (string) $c );
	return preg_match( '/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $c ) ? $c : '';
}
function sanitize_title( $t ) {
	$t = strtolower( remove_accents( (string) $t ) );
	$t = preg_replace( '/[^a-z0-9]+/', '-', $t );
	return trim( $t, '-' );
}
function remove_accents( $s ) {
	$map = array( 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'à' => 'a', 'ç' => 'c', 'ù' => 'u', 'ô' => 'o', 'î' => 'i' );
	return strtr( (string) $s, $map );
}
function esc_url_raw( $u ) {
	$u = trim( (string) $u );
	return preg_match( '#^https?://#i', $u ) ? $u : '';
}
function esc_attr_e( $s, $d = null ) {
	echo esc_attr( $s );
}
function esc_html_e( $s, $d = null ) {
	echo esc_html( $s );
}
function get_template_directory_uri() {
	return 'https://exemple.test/wp-content/themes/jeux-marketing';
}

function wp_list_pluck( $list, $field ) {
	$out = array();
	foreach ( (array) $list as $k => $v ) {
		$out[ $k ] = is_array( $v ) && isset( $v[ $field ] ) ? $v[ $field ] : null;
	}
	return $out;
}

/* Ce qu'il faut de plus pour charger functions.php en entier et vérifier
   l'ordre de mise en file des scripts. */
$GLOBALS['jmk_scripts'] = array();

function wp_enqueue_script( $handle = '', $src = '', $deps = array(), $ver = false, $footer = false ) {
	$GLOBALS['jmk_scripts'][] = array( 'handle' => $handle, 'deps' => (array) $deps );
}
function wp_add_inline_style( $h, $c ) {}
function get_stylesheet_uri() {
	return 'https://exemple.test/style.css';
}
function register_nav_menus( $m ) {}
function add_theme_support() {}
function load_theme_textdomain( $d, $p ) {
	return true;
}
function add_shortcode( $tag, $cb ) {}
function shortcode_atts( $pairs, $atts, $tag = '' ) {
	return array_merge( (array) $pairs, (array) $atts );
}
function add_meta_box() {}
function register_taxonomy() {}
function flush_rewrite_rules() {}
function wp_parse_args( $a, $d = array() ) {
	return array_merge( (array) $d, (array) $a );
}

function is_front_page() {
	return isset( $GLOBALS['jmk_is_front'] ) ? (bool) $GLOBALS['jmk_is_front'] : true;
}

function wp_create_nonce( $a = -1 ) {
	return 'nonce';
}

