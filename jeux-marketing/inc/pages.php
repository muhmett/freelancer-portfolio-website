<?php
/**
 * Pages du site.
 *
 * Le thème est parti d'une page unique où tout s'empilait : les jeux, les
 * bannières, le devis, le portfolio. À la lecture c'est long, sur téléphone
 * c'est interminable, et surtout ça ne se partage pas — on ne peut pas
 * envoyer « la page des bannières » à un client qui n'achète que ça.
 *
 * Chaque savoir-faire a donc sa page. L'accueil devient un carrefour.
 *
 * Les pages sont créées à l'activation du thème : demander à quelqu'un de
 * créer trois pages et d'y affecter le bon modèle, c'est trois occasions de
 * se tromper et un site à moitié monté.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Les pages du site, et le modèle qui va avec chacune.
 *
 * @return array
 */
function jmk_pages() {
	return array(
		'games' => array(
			'slug'     => 'jeux',
			'template' => 'template-games.php',
			'title'    => 'nav_games',
			'eyebrow'  => 'page_games_eyebrow',
			'lede'     => 'page_games_lede',
		),
		'banners' => array(
			'slug'     => 'bannieres',
			'template' => 'template-banners.php',
			'title'    => 'nav_banners',
			'eyebrow'  => 'page_banners_eyebrow',
			'lede'     => 'page_banners_lede',
		),
	);
}

/**
 * Déclare les modèles auprès de WordPress.
 *
 * @param array $templates Modèles connus.
 * @return array
 */
function jmk_page_templates( $templates ) {
	$templates['template-games.php']   = __( 'Jeux marketing', 'jeux-marketing' );
	$templates['template-banners.php'] = __( 'Bannières display', 'jeux-marketing' );
	return $templates;
}
add_filter( 'theme_page_templates', 'jmk_page_templates' );

/**
 * L'adresse d'une page du site.
 *
 * Retombe sur l'ancre de l'accueil tant que la page n'existe pas : un menu
 * qui pointe dans le vide est pire qu'un menu qui reste sur place.
 *
 * @param string $key Clé dans jmk_pages().
 * @return string
 */
function jmk_page_url( $key ) {
	$pages = jmk_pages();
	if ( ! isset( $pages[ $key ] ) ) {
		return home_url( '/' );
	}

	$id = (int) get_option( 'jmk_page_' . $key );
	if ( $id && 'publish' === get_post_status( $id ) ) {
		return get_permalink( $id );
	}

	$page = get_page_by_path( $pages[ $key ]['slug'] );
	if ( $page ) {
		update_option( 'jmk_page_' . $key, $page->ID );
		return get_permalink( $page );
	}

	return home_url( '/#' . ( 'games' === $key ? 'autres' : 'bannieres-work' ) );
}

/**
 * Crée les pages manquantes et leur affecte leur modèle.
 */
function jmk_create_pages() {
	foreach ( jmk_pages() as $key => $page ) {
		$id = (int) get_option( 'jmk_page_' . $key );
		if ( $id && 'publish' === get_post_status( $id ) ) {
			continue;
		}

		$existing = get_page_by_path( $page['slug'] );
		if ( $existing ) {
			update_post_meta( $existing->ID, '_wp_page_template', $page['template'] );
			update_option( 'jmk_page_' . $key, $existing->ID );
			continue;
		}

		$new = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_name'    => $page['slug'],
				'post_title'   => jmk_t( $page['title'] ),
				'post_status'  => 'publish',
				'post_content' => '',
			)
		);

		if ( $new && ! is_wp_error( $new ) ) {
			update_post_meta( $new, '_wp_page_template', $page['template'] );
			update_option( 'jmk_page_' . $key, $new );
		}
	}
}
add_action( 'after_switch_theme', 'jmk_create_pages' );

/**
 * Le savoir-faire mis en avant sur l'accueil.
 *
 * Chaque entrée mène à sa page. La vignette n'est pas une image : c'est la
 * démonstration en miniature, comme partout ailleurs dans ce thème.
 *
 * @return array
 */
function jmk_skills_hub() {
	return array(
		array(
			'key'     => 'games',
			'kind'    => 'game',
			'eyebrow' => jmk_t( 'hub_games_eyebrow' ),
			'title'   => jmk_t( 'hub_games_title' ),
			'text'    => jmk_t( 'hub_games_text' ),
			'cta'     => jmk_t( 'hub_games_cta' ),
			'points'  => array( jmk_t( 'hub_games_p1' ), jmk_t( 'hub_games_p2' ), jmk_t( 'hub_games_p3' ) ),
		),
		array(
			'key'     => 'banners',
			'kind'    => 'banner',
			'eyebrow' => jmk_t( 'hub_banners_eyebrow' ),
			'title'   => jmk_t( 'hub_banners_title' ),
			'text'    => jmk_t( 'hub_banners_text' ),
			'cta'     => jmk_t( 'hub_banners_cta' ),
			'points'  => array( jmk_t( 'hub_banners_p1' ), jmk_t( 'hub_banners_p2' ), jmk_t( 'hub_banners_p3' ) ),
		),
	);
}
