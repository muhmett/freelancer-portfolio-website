<?php
/**
 * Campagnes de bannières présentées dans le portfolio.
 *
 * Quatre marques, quatre secteurs, quatre partis pris graphiques. Elles
 * n'existent pas : ce sont des démonstrations, et la section le dit à
 * l'écran. Un portfolio qui présente une marque inventée comme un client
 * est un faux témoignage — sur Fiverr comme sur Upwork, c'est un motif de
 * suspension, et c'est précisément le compte que ce thème cherche à
 * protéger avec son mode Fiverr.
 *
 * Les textes des bannières restent en anglais dans les trois langues : ils
 * font partie du visuel. Une publicité display se montre telle qu'elle a été
 * livrée, on ne la retraduit pas plus qu'on ne retraduit une photo.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Les campagnes de démonstration.
 *
 * @return array
 */
function jmk_banner_work() {
	$img = JMK_URI . '/assets/img/work/';
	$v   = '?v=' . JMK_VERSION;

	return array(

		array(
			'slug'   => 'velt',
			'brand'  => 'VELT',
			'niche'  => jmk_t( 'bwork_velt_niche' ),
			'note'   => jmk_t( 'bwork_velt_note' ),
			'style'  => 'bold',
			'image'  => $img . 'sneaker.jpg' . $v,
			'bg'     => '#0E1114',
			'accent' => '#C8FF2E',
			'ink'    => '#FFFFFF',
			'scrim'  => 0.66,
			'cta'    => 'Shop the drop',
			'frames' => array(
				array( 'kicker' => 'New release',  'title' => 'Built for the *last mile*', 'sub' => 'Lightweight knit, 220 g' ),
				array( 'kicker' => 'Limited run',  'title' => 'Only *500 pairs*',          'sub' => 'Numbered, one per customer' ),
				array( 'kicker' => '',             'title' => 'Free returns, *30 days*',   'sub' => 'No questions asked' ),
			),
		),

		array(
			'slug'   => 'meridian',
			'brand'  => 'MERIDIAN',
			'niche'  => jmk_t( 'bwork_meridian_niche' ),
			'note'   => jmk_t( 'bwork_meridian_note' ),
			'style'  => 'luxe',
			'image'  => $img . 'watch.jpg' . $v,
			'bg'     => '#0A0A0C',
			'accent' => '#C9A227',
			'ink'    => '#F4F1EA',
			'scrim'  => 0.6,
			'cta'    => 'Discover',
			'frames' => array(
				array( 'kicker' => 'Calibre 7',      'title' => 'Seventy-two hours, *untouched*', 'sub' => 'In-house automatic movement' ),
				array( 'kicker' => 'Hand assembled', 'title' => 'Nine weeks, *one watch*',        'sub' => 'Each piece numbered and signed' ),
				array( 'kicker' => '',               'title' => 'Book a *private viewing*',       'sub' => 'By appointment' ),
			),
		),

		array(
			'slug'   => 'aura',
			'brand'  => 'AURA',
			'niche'  => jmk_t( 'bwork_aura_niche' ),
			'note'   => jmk_t( 'bwork_aura_note' ),
			'style'  => 'clean',
			'image'  => $img . 'eyewear.jpg' . $v,
			'bg'     => '#F3EDE3',
			'accent' => '#1F6B5B',
			'ink'    => '#221F1B',
			'scrim'  => 0.46,
			'cta'    => 'Try at home',
			'frames' => array(
				array( 'kicker' => 'Home trial',      'title' => 'Five frames, *seven days*',     'sub' => 'Free both ways' ),
				array( 'kicker' => 'Italian acetate', 'title' => 'Hand polished, *not moulded*',  'sub' => 'Lighter than it looks' ),
				array( 'kicker' => '',                'title' => 'Lenses from *490 MAD*',         'sub' => 'Anti-glare included' ),
			),
		),

		array(
			'slug'   => 'sillage',
			'brand'  => 'SILLAGE',
			'niche'  => jmk_t( 'bwork_sillage_niche' ),
			'note'   => jmk_t( 'bwork_sillage_note' ),
			'style'  => 'soft',
			'image'  => $img . 'scent.jpg' . $v,
			'bg'     => '#3A1F30',
			'accent' => '#E8B4C4',
			'ink'    => '#FFF6F3',
			'scrim'  => 0.5,
			'cta'    => 'Find your note',
			'frames' => array(
				array( 'kicker' => 'Discovery set',   'title' => 'Six vials, *one to keep*', 'sub' => 'Credit the price on your first bottle' ),
				array( 'kicker' => 'Rose de Kelaa',   'title' => 'Picked at *dawn*',         'sub' => 'Three weeks a year, that is all' ),
				array( 'kicker' => '',                'title' => 'Refill and *save 30%*',    'sub' => 'Bring the bottle back' ),
			),
		),

	);
}

/**
 * Les formats montrés pour chaque campagne.
 *
 * Assez pour montrer que la mise en page change avec la forme — une boîte,
 * un bandeau, un gratte-ciel, un mobile — sans remplir la page.
 *
 * @return array
 */
function jmk_banner_work_sizes() {
	return array(
		array( 'w' => 728, 'h' => 90,  'name' => 'Leaderboard' ),
		array( 'w' => 160, 'h' => 600, 'name' => 'Wide Skyscraper' ),
		array( 'w' => 300, 'h' => 250, 'name' => 'Medium Rectangle' ),
		array( 'w' => 320, 'h' => 100, 'name' => 'Large Mobile Banner' ),
	);
}

/**
 * La forme d'un format, pour la mise en page du mur.
 *
 * Le mur ne peut pas ranger des formats qu'il ne distingue pas : un bandeau
 * de 728 px et un gratte-ciel de 160 sont deux objets sans rapport, et les
 * traiter pareil laissait la moitié de la ligne vide. La forme se déduit du
 * rapport, pas d'une liste de tailles — un 970×250 ajouté demain sera rangé
 * sans qu'on touche à la feuille de style.
 *
 * @param int $w Largeur en pixels.
 * @param int $h Hauteur en pixels.
 * @return string « wide », « tall » ou « box ».
 */
function jmk_banner_shape( $w, $h ) {
	$w = (int) $w;
	$h = (int) $h;

	if ( $w < 1 || $h < 1 ) {
		return 'box';
	}
	if ( $h >= $w * 2 ) {
		return 'tall';
	}
	if ( $w >= $h * 4 ) {
		return 'wide';
	}
	return 'box';
}

/**
 * Les campagnes, mises en forme pour le navigateur.
 *
 * @return array
 */
function jmk_banner_work_config() {
	$out = array();
	foreach ( jmk_banner_work() as $c ) {
		$out[] = array(
			'slug'   => $c['slug'],
			'brand'  => $c['brand'],
			'style'  => $c['style'],
			'image'  => $c['image'],
			'bg'     => $c['bg'],
			'accent' => $c['accent'],
			'ink'    => $c['ink'],
			'scrim'  => $c['scrim'],
			'cta'    => $c['cta'],
			'frames' => $c['frames'],
			'hold'   => 2600,
			'loops'  => 3,
		);
	}
	return $out;
}
