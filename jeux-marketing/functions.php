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

require_once JMK_DIR . '/inc/i18n.php';
require_once JMK_DIR . '/inc/defaults.php';
require_once JMK_DIR . '/inc/admin.php';
require_once JMK_DIR . '/inc/builder.php';
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
 * Réglages de la section « bannières ».
 *
 * La bannière reprend la couleur de la marque : c'est le même argument que
 * pour la roue — le visiteur doit voir ses couleurs, pas les nôtres.
 *
 * @return array
 */
function jmk_banner_config() {
	$frames = array();
	foreach ( (array) jmk_get( 'banner_frames' ) as $f ) {
		if ( empty( $f['title'] ) ) {
			continue;
		}
		$frames[] = array(
			'kicker' => isset( $f['kicker'] ) ? (string) $f['kicker'] : '',
			'title'  => (string) $f['title'],
			'sub'    => isset( $f['sub'] ) ? (string) $f['sub'] : '',
		);
	}

	return array(
		'brand'  => (string) jmk_get( 'brand_name' ),
		'accent' => (string) jmk_get( 'accent' ),
		'bg'     => ( 'casino' === jmk_get( 'skin' ) ) ? '#0B3325' : ( ( 'arcade' === jmk_get( 'skin' ) ) ? '#2A0F4D' : '#1B1226' ),
		'cta'    => (string) jmk_get( 'banner_cta' ),
		'frames' => $frames,
		'hold'   => 2400,
		// Dans la page, la bannière tourne sans fin : c'est une vitrine, pas
		// une diffusion. Le plafond de trois boucles ne vaut que pour ce qui
		// part en régie.
		'loops'  => 3,
	);
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

	// Le feutre de l'habillage casino passe par une variable plutôt que par
	// une adresse écrite dans main.css : la feuille de style est servie telle
	// quelle, elle ne connaît pas le dossier du thème.
	$felt = sprintf(
		'url(%s?v=%s)',
		JMK_URI . '/assets/img/felt.jpg',
		jmk_asset_version( 'assets/img/felt.jpg' )
	);

	return sprintf(
		':root{--accent:%1$s;--accent-2:%2$s;--accent-ink:%3$s;--felt:%4$s;}',
		esc_attr( $accent ),
		esc_attr( $light ),
		esc_attr( $ink ),
		esc_attr( $felt )
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
		'foil'      => JMK_URI . '/assets/img/foil.jpg?v=' . jmk_asset_version( 'assets/img/foil.jpg' ),
		// Le code d'intégration montré au visiteur pointe le vrai moteur, pas
		// un nom de fichier d'illustration : l'adresse est copiable telle
		// quelle et le jeu tourne.
		'engine'    => JMK_URI . '/assets/js/jmk-embed.js',
		'banners'   => jmk_banner_config(),
		'brand'     => (string) jmk_get( 'brand_name' ),
		'accent'    => (string) jmk_get( 'accent' ),
		'skin'      => (string) jmk_get( 'skin' ),
		'lang'      => jmk_lang(),
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
			'win'      => jmk_t( 'js_win' ),
			'lose'     => jmk_t( 'js_lose' ),
			'loseSub'  => jmk_t( 'js_loseSub' ),
			'already'  => jmk_t( 'js_already' ),
			'copied'   => jmk_t( 'js_copied' ),
			'copy'     => jmk_t( 'js_copy' ),
			'copyCode' => jmk_t( 'copy_short' ),
			'scratch'  => jmk_t( 'js_scratch' ),
			'revealed' => jmk_t( 'js_revealed' ),
			'errName'  => jmk_t( 'js_errName' ),
			'errMail'  => jmk_t( 'js_errMail' ),
			'errCons'  => jmk_t( 'js_errCons' ),
			'errDupe'  => jmk_t( 'js_errDupe' ),
			'saved'    => jmk_t( 'js_saved' ),
			'spin'     => jmk_t( 'js_spin' ),
			'score'    => jmk_t( 'js_score' ),
			'exhausted' => jmk_t( 'js_exhausted' ),
			'waSend'   => jmk_t( 'js_waSend' ),
			'waChat'   => jmk_t( 'js_waChat' ),
			'order'    => jmk_t( 'js_order' ),
			'contact'  => jmk_t( 'contact' ),
			'delay'    => jmk_t( 'js_delay' ),
			'day'      => jmk_t( 'js_day' ),
			'days'     => jmk_t( 'js_days' ),
			'briefHi'  => jmk_t( 'js_briefHi' ),
			'briefBrand' => jmk_t( 'js_briefBrand' ),
			'briefColor' => jmk_t( 'js_briefColor' ),
			'briefLang'  => jmk_t( 'js_briefLang' ),
			'briefConf'  => jmk_t( 'js_briefConf' ),
			'briefTotal' => jmk_t( 'js_briefTotal' ),
			'briefEnd'   => jmk_t( 'js_briefEnd' ),
			'noLot'      => jmk_t( 'js_noLot' ),
		),
	);
}

/**
 * Version d'un fichier du thème, tirée de sa date de modification.
 *
 * JMK_VERSION ne bouge qu'aux versions publiées : entre deux, un
 * navigateur ou une extension de cache continuerait de servir l'ancien
 * fichier, et la mise à jour n'arriverait jamais chez le client.
 *
 * @param string $rel Chemin relatif au thème.
 * @return string
 */
function jmk_asset_version( $rel ) {
	$file = JMK_DIR . '/' . ltrim( $rel, '/' );
	$time = is_readable( $file ) ? filemtime( $file ) : 0;
	return $time ? JMK_VERSION . '.' . $time : JMK_VERSION;
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
	wp_enqueue_style( 'jmk-main', JMK_URI . '/assets/css/main.css', array( 'jmk-fonts' ), jmk_asset_version( 'assets/css/main.css' ) );
	wp_add_inline_style( 'jmk-main', jmk_inline_css() );

	wp_enqueue_style( 'jeux-marketing-style', get_stylesheet_uri(), array( 'jmk-main' ), JMK_VERSION );

	wp_enqueue_script( 'jmk-app', JMK_URI . '/assets/js/app.js', array(), jmk_asset_version( 'assets/js/app.js' ), true );

	// Le moteur de bannières n'est chargé que si la section est affichée :
	// c'est 14 Ko qui n'ont rien à faire sur une page qui ne les montre pas.
	if ( jmk_get( 'sec_banners' ) && is_front_page() ) {
		wp_enqueue_script( 'jmk-banner', JMK_URI . '/assets/js/jmk-banner.js', array(), jmk_asset_version( 'assets/js/jmk-banner.js' ), true );
	}
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
	if ( 'rtl' === jmk_dir() ) {
		$classes[] = 'jmk-rtl';
	}
	$classes[] = 'jmk-lang-' . jmk_lang();
	$classes[] = 'jmk-skin-' . jmk_get( 'skin' );
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
