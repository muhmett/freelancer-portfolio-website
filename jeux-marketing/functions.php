<?php
/**
 * Jeux Marketing — fonctions du thème.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JMK_VERSION', '1.0.0' );
define( 'JMK_DIR', get_template_directory() );
define( 'JMK_URI', get_template_directory_uri() );

require_once JMK_DIR . '/inc/defaults.php';
require_once JMK_DIR . '/inc/admin.php';
require_once JMK_DIR . '/inc/leads.php';
require_once JMK_DIR . '/inc/shortcode.php';
require_once JMK_DIR . '/inc/portfolio.php';

/**
 * Support du thème.
 */
function jmk_setup() {
	load_theme_textdomain( 'jeux-marketing', JMK_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'custom-logo', array( 'height' => 60, 'width' => 220, 'flex-width' => true ) );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);
	register_nav_menus( array( 'primary' => __( 'Navigation principale', 'jeux-marketing' ) ) );
}
add_action( 'after_setup_theme', 'jmk_setup' );

/**
 * Convertit un hex en HSL.
 *
 * @param string $hex Couleur hexadécimale.
 * @return array
 */
function jmk_hex_to_hsl( $hex ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( 6 !== strlen( $hex ) ) {
		$hex = 'D9A441';
	}
	$r  = hexdec( substr( $hex, 0, 2 ) ) / 255;
	$g  = hexdec( substr( $hex, 2, 2 ) ) / 255;
	$b  = hexdec( substr( $hex, 4, 2 ) ) / 255;
	$mx = max( $r, $g, $b );
	$mn = min( $r, $g, $b );
	$d  = $mx - $mn;
	$h  = 0;
	if ( $d > 0 ) {
		if ( $mx === $r ) {
			$h = fmod( ( $g - $b ) / $d, 6 );
		} elseif ( $mx === $g ) {
			$h = ( $b - $r ) / $d + 2;
		} else {
			$h = ( $r - $g ) / $d + 4;
		}
		$h *= 60;
		if ( $h < 0 ) {
			$h += 360;
		}
	}
	$l = ( $mx + $mn ) / 2;
	$s = ( $d > 0 ) ? $d / ( 1 - abs( 2 * $l - 1 ) ) : 0;
	return array( $h, $s * 100, $l * 100 );
}

/**
 * CSS variables dérivées de la couleur choisie.
 *
 * @return string
 */
function jmk_inline_css() {
	$accent          = jmk_get( 'accent' );
	list( $h, $s, $l ) = jmk_hex_to_hsl( $accent );
	$light           = 'hsl(' . round( $h ) . ' ' . round( $s ) . '% ' . round( min( 78, $l + 13 ) ) . '%)';
	$ink             = ( $l > 58 ) ? '#221503' : '#FFF6E4';

	return sprintf(
		':root{--accent:%1$s;--accent-2:%2$s;--accent-ink:%3$s;}',
		esc_attr( $accent ),
		esc_attr( $light ),
		esc_attr( $ink )
	);
}

/**
 * Configuration transmise au JavaScript.
 *
 * @return array
 */
function jmk_js_config() {
	$lots  = jmk_get( 'lots' );
	$clean = array();

	foreach ( (array) $lots as $i => $lot ) {
		$clean[] = array(
			'id'      => 'lot' . $i,
			'label'   => (string) $lot['label'],
			'weight'  => (float) $lot['weight'],
			'cap'     => ( (int) $lot['cap'] > 0 ) ? (int) $lot['cap'] : null,
			'awarded' => jmk_awarded( $i ),
			'code'    => empty( $lot['losing'] ) ? (string) $lot['code'] : '',
			'hue'     => ! empty( $lot['losing'] ) ? null : (float) $lot['hue'],
			'losing'  => ! empty( $lot['losing'] ),
		);
	}

	$options = array();
	foreach ( (array) jmk_get( 'options' ) as $opt ) {
		$options[] = array(
			'label' => (string) $opt['label'],
			'price' => (float) $opt['price'],
			'days'  => (int) $opt['days'],
			'on'    => ! empty( $opt['on'] ),
			'fixed' => ! empty( $opt['fixed'] ),
		);
	}

	$quiz = array();
	foreach ( (array) jmk_get( 'quiz' ) as $q ) {
		$answers = array_values( array_filter( array_map( 'trim', explode( "\n", (string) $q['a'] ) ) ) );
		if ( empty( $q['q'] ) || count( $answers ) < 2 ) {
			continue;
		}
		$quiz[] = array(
			'q' => (string) $q['q'],
			'o' => $answers,
			'a' => max( 0, min( count( $answers ) - 1, (int) $q['c'] - 1 ) ),
		);
	}

	$mode = jmk_get( 'mode' );

	return array(
		'ajax'      => admin_url( 'admin-ajax.php' ),
		'nonce'     => wp_create_nonce( 'jmk_public' ),
		'mode'      => $mode,
		// En mode Fiverr le numéro ne doit apparaître nulle part, pas même
		// dans le code source de la page : Fiverr y voit une sortie de
		// plateforme et cela suffit à faire suspendre un compte.
		'whatsapp'  => ( 'fiverr' === $mode ) ? '' : preg_replace( '/\D/', '', (string) jmk_get( 'whatsapp' ) ),
		'fiverr'    => esc_url_raw( jmk_get( 'fiverr_url' ) ),
		'foil'      => JMK_URI . '/assets/img/foil.jpg',
		'brand'     => (string) jmk_get( 'brand_name' ),
		'accent'    => (string) jmk_get( 'accent' ),
		'lang'      => (string) jmk_get( 'lang' ),
		'onePlay'   => (bool) jmk_get( 'one_play' ),
		'currency'  => (string) jmk_get( 'currency' ),
		'lots'      => $clean,
		'options'   => $options,
		'quiz'      => $quiz,
		'roi'       => array(
			'visitors' => (float) jmk_get( 'roi_visitors' ),
			'part'     => (float) jmk_get( 'roi_part' ),
			'conv'     => (float) jmk_get( 'roi_conv' ),
			'cart'     => (float) jmk_get( 'roi_cart' ),
		),
		'i18n'      => array(
			'win'      => __( 'Vous gagnez :', 'jeux-marketing' ),
			'lose'     => __( 'Perdu cette fois.', 'jeux-marketing' ),
			'loseSub'  => __( 'Revenez demain — une partie par jour.', 'jeux-marketing' ),
			'already'  => __( 'Vous avez déjà joué. Une participation par personne.', 'jeux-marketing' ),
			'copied'   => __( 'Copié', 'jeux-marketing' ),
			'copy'     => __( 'Copier le récapitulatif', 'jeux-marketing' ),
			'copyCode' => __( 'Copier', 'jeux-marketing' ),
			'scratch'  => __( 'GRATTEZ ICI', 'jeux-marketing' ),
			'revealed' => __( 'Révélé', 'jeux-marketing' ),
			'scratched'=> __( '%s %% gratté', 'jeux-marketing' ),
			'errName'  => __( 'Indiquez un prénom.', 'jeux-marketing' ),
			'errMail'  => __( 'Cette adresse email n\'est pas valide.', 'jeux-marketing' ),
			'errCons'  => __( 'Le consentement est obligatoire pour enregistrer une participation.', 'jeux-marketing' ),
			'errDupe'  => __( 'Cette adresse a déjà participé. Une seule participation par personne.', 'jeux-marketing' ),
			'saved'    => __( 'Participation enregistrée et code envoyé.', 'jeux-marketing' ),
			'spin'     => __( 'TOURNEZ', 'jeux-marketing' ),
			'question' => __( 'Question %1$s / %2$s', 'jeux-marketing' ),
			'score'    => __( '%1$s / %2$s bonnes réponses', 'jeux-marketing' ),
			'exhausted'=> __( 'ÉPUISÉ', 'jeux-marketing' ),
			'waSend'   => __( 'Envoyer ce devis sur WhatsApp', 'jeux-marketing' ),
			'waChat'   => __( 'Discuter sur WhatsApp', 'jeux-marketing' ),
			'order'    => __( 'Commander sur Fiverr', 'jeux-marketing' ),
			'contact'  => __( 'Me contacter', 'jeux-marketing' ),
			'delay'    => __( 'Délai estimé : %s', 'jeux-marketing' ),
			'day'      => __( 'jour', 'jeux-marketing' ),
			'days'     => __( 'jours', 'jeux-marketing' ),
			'briefHi'  => __( 'Bonjour, je viens de la page de démonstration.', 'jeux-marketing' ),
			'briefBrand'  => __( 'Marque', 'jeux-marketing' ),
			'briefColor'  => __( 'Couleur', 'jeux-marketing' ),
			'briefLang'   => __( 'Langue', 'jeux-marketing' ),
			'briefConf'   => __( 'Ma configuration :', 'jeux-marketing' ),
			'briefTotal'  => __( 'Total estimé', 'jeux-marketing' ),
			'briefEnd'    => __( 'Mes lots et ma plateforme : ', 'jeux-marketing' ),
			'noLot'       => __( '(à préciser)', 'jeux-marketing' ),
		),
	);
}

/**
 * Chargement des styles et scripts publics.
 */
function jmk_assets() {
	wp_enqueue_style(
		'jmk-fonts',
		'https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,800&family=Inter+Tight:wght@400;500;600&family=JetBrains+Mono:wght@400;700&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'jmk-main', JMK_URI . '/assets/css/main.css', array( 'jmk-fonts' ), JMK_VERSION );
	wp_add_inline_style( 'jmk-main', jmk_inline_css() );

	wp_enqueue_style( 'jeux-marketing-style', get_stylesheet_uri(), array( 'jmk-main' ), JMK_VERSION );

	wp_enqueue_script( 'jmk-app', JMK_URI . '/assets/js/app.js', array(), JMK_VERSION, true );
	wp_localize_script( 'jmk-app', 'JMK', jmk_js_config() );
}
add_action( 'wp_enqueue_scripts', 'jmk_assets' );

/**
 * Direction RTL si la langue du jeu est l'arabe.
 *
 * @param array $classes Classes du body.
 * @return array
 */
function jmk_body_class( $classes ) {
	if ( 'ar' === jmk_get( 'lang' ) ) {
		$classes[] = 'jmk-rtl';
	}
	return $classes;
}
add_filter( 'body_class', 'jmk_body_class' );

/**
 * Retourne le lien de contact principal selon le mode.
 *
 * Chaîne vide si rien n'est configuré : à l'appelant de ne pas afficher
 * de bouton dans ce cas.
 *
 * @param string $text Message pré-rempli.
 * @return string
 */
function jmk_contact_url( $text = '' ) {
	if ( 'fiverr' === jmk_get( 'mode' ) ) {
		return esc_url( jmk_get( 'fiverr_url' ) );
	}
	$num = preg_replace( '/\D/', '', (string) jmk_get( 'whatsapp' ) );
	if ( '' === $num ) {
		return '';
	}
	$url = 'https://wa.me/' . $num;
	if ( $text ) {
		$url .= '?text=' . rawurlencode( $text );
	}
	return esc_url( $url );
}
