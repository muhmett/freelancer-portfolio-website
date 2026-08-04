<?php
/**
 * Trois langues, sans extension.
 *
 * Le thème est vendu : le client ne peut pas être obligé d'installer WPML
 * ou Polylang pour que sa page s'affiche en anglais. Tout tient donc ici —
 * un dictionnaire par langue dans inc/lang/, la langue courante choisie par
 * l'adresse puis par un cookie, et rien à configurer pour que ça marche.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Langues livrées.
 *
 * @return array Code => array( nom affiché, sens d'écriture, code HTML ).
 */
function jmk_langs() {
	return array(
		'en' => array( 'name' => 'English',  'dir' => 'ltr', 'html' => 'en', 'short' => 'EN' ),
		'fr' => array( 'name' => 'Français', 'dir' => 'ltr', 'html' => 'fr', 'short' => 'FR' ),
		'ar' => array( 'name' => 'العربية',  'dir' => 'rtl', 'html' => 'ar', 'short' => 'ع' ),
	);
}

/**
 * Langue par défaut du site, réglable dans l'administration.
 *
 * @return string
 */
function jmk_default_lang() {
	$saved = get_option( 'jmk_settings', array() );
	$lang  = isset( $saved['lang'] ) ? $saved['lang'] : 'en';
	return isset( jmk_langs()[ $lang ] ) ? $lang : 'en';
}

/**
 * Langue de la page en cours.
 *
 * Priorité : ?lang= dans l'adresse, puis le cookie posé par le sélecteur,
 * puis la langue par défaut du site. L'adresse passe avant tout pour qu'un
 * lien partagé s'ouvre toujours dans la bonne langue.
 *
 * @return string
 */
function jmk_lang() {
	static $lang = null;
	if ( null !== $lang ) {
		return $lang;
	}

	$langs = jmk_langs();

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- simple choix d'affichage, sans effet de bord.
	if ( isset( $_GET['lang'] ) ) {
		$asked = sanitize_key( wp_unslash( $_GET['lang'] ) );
		if ( isset( $langs[ $asked ] ) ) {
			$lang = $asked;
			return $lang;
		}
	}
	// phpcs:enable

	if ( isset( $_COOKIE['jmk_lang'] ) ) {
		$cookie = sanitize_key( wp_unslash( $_COOKIE['jmk_lang'] ) );
		if ( isset( $langs[ $cookie ] ) ) {
			$lang = $cookie;
			return $lang;
		}
	}

	$lang = jmk_default_lang();
	return $lang;
}

/**
 * Mémorise la langue demandée pour les pages suivantes.
 */
function jmk_remember_lang() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( is_admin() || ! isset( $_GET['lang'] ) || headers_sent() ) {
		return;
	}
	$lang = jmk_lang();
	setcookie(
		'jmk_lang',
		$lang,
		array(
			'expires'  => time() + YEAR_IN_SECONDS,
			'path'     => defined( 'COOKIEPATH' ) ? COOKIEPATH : '/',
			'samesite' => 'Lax',
			'secure'   => is_ssl(),
		)
	);
}
add_action( 'template_redirect', 'jmk_remember_lang' );

/**
 * Dictionnaire complet d'une langue.
 *
 * @param string $lang Code de langue.
 * @return array
 */
function jmk_pack( $lang = null ) {
	static $cache = array();
	$lang = $lang ? $lang : jmk_lang();
	if ( ! isset( jmk_langs()[ $lang ] ) ) {
		$lang = 'en';
	}
	if ( ! isset( $cache[ $lang ] ) ) {
		$file           = JMK_DIR . '/inc/lang/' . $lang . '.php';
		$cache[ $lang ] = is_readable( $file ) ? (array) include $file : array();
	}
	return $cache[ $lang ];
}

/**
 * Une chaîne d'interface.
 *
 * Repli sur l'anglais si la clé manque dans la langue courante, puis sur la
 * clé elle-même : une traduction oubliée n'efface jamais le texte.
 *
 * @param string $key Clé du dictionnaire.
 * @return string
 */
function jmk_t( $key ) {
	$pack = jmk_pack();
	if ( isset( $pack['ui'][ $key ] ) && '' !== $pack['ui'][ $key ] ) {
		return $pack['ui'][ $key ];
	}
	$en = jmk_pack( 'en' );
	return isset( $en['ui'][ $key ] ) ? $en['ui'][ $key ] : $key;
}

/**
 * Affiche une chaîne d'interface, échappée.
 *
 * @param string $key Clé du dictionnaire.
 */
function jmk_e( $key ) {
	echo esc_html( jmk_t( $key ) );
}

/**
 * Adresse de la page courante dans une autre langue.
 *
 * @param string $lang Code de langue.
 * @return string
 */
function jmk_lang_url( $lang ) {
	global $wp;
	$base = home_url( add_query_arg( array(), $wp->request ? $wp->request : '' ) );
	return esc_url( add_query_arg( 'lang', $lang, $base ) );
}

/**
 * Sélecteur de langue.
 */
function jmk_lang_switcher() {
	$current = jmk_lang();
	echo '<nav class="jmk-langs" aria-label="' . esc_attr( jmk_t( 'lang_label' ) ) . '">';
	foreach ( jmk_langs() as $code => $info ) {
		$is = ( $code === $current );
		printf(
			'<a href="%1$s" hreflang="%2$s" lang="%2$s"%3$s>%4$s</a>',
			esc_url( jmk_lang_url( $code ) ),
			esc_attr( $info['html'] ),
			$is ? ' aria-current="true"' : '',
			esc_html( $info['short'] )
		);
	}
	echo '</nav>';
}

/**
 * Sens d'écriture de la langue courante.
 *
 * @return string
 */
function jmk_dir() {
	$langs = jmk_langs();
	$lang  = jmk_lang();
	return isset( $langs[ $lang ] ) ? $langs[ $lang ]['dir'] : 'ltr';
}
