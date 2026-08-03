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
