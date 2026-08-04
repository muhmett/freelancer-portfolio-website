<?php
/**
 * Valeurs par défaut et lecture des réglages.
 *
 * Deux familles de réglages :
 *
 * - les réglages *techniques* (mode de contact, couleur, sections affichées,
 *   plafonds…) sont communs à toutes les langues ;
 * - les réglages *de contenu* (titres, lots, packs, FAQ…) existent une fois
 *   par langue, et sont stockés sous jmk_settings[<langue>][<clé>].
 *
 * Les valeurs livrées viennent de inc/lang/<langue>.php.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Réglages communs à toutes les langues.
 *
 * @return array
 */
function jmk_defaults() {
	return array(

		// Contact.
		'mode'          => 'direct',
		'whatsapp'      => '',
		'fiverr_url'    => '',
		'email'         => 'old@outlook.fr',

		// Marque.
		'brand_name'    => 'anomalydev by Hsk',
		'accent'        => '#D9A441',
		'lang'          => 'en',

		// Jeux actifs.
		'game_wheel'    => 1,
		'game_scratch'  => 1,
		'game_tap'      => 1,
		'game_quiz'     => 1,
		'game_slot'     => 1,
		'game_plinko'   => 1,

		// Sections affichées.
		'sec_about'     => 1,
		'sec_services'  => 1,
		'sec_skills'    => 1,
		'sec_work'      => 1,
		'sec_process'   => 1,
		'sec_reviews'   => 1,
		'sec_brand'     => 1,
		'sec_lab'       => 1,
		'sec_leads'     => 1,
		'sec_roi'       => 1,
		'sec_quote'     => 1,
		'sec_specs'     => 1,
		'sec_faq'       => 1,

		// Règles de jeu.
		'one_play'      => 1,
		'webhook'       => '',
		'privacy_url'   => '',

		// Devis et calculateur.
		'currency'      => '€',
		'roi_visitors'  => 8000,
		'roi_part'      => 24,
		'roi_conv'      => 4,
		'roi_cart'      => 65,
	);
}

/**
 * Réglages dont le contenu existe une fois par langue.
 *
 * @return array
 */
function jmk_translatable() {
	return array(
		'hero_eyebrow', 'hero_title', 'hero_title_2', 'hero_text', 'chips',
		'about_title', 'about_text', 'about_stats',
		'work_title', 'work_text',
		'lots', 'options', 'packs', 'process', 'skills', 'faq', 'specs', 'quiz', 'reviews',
	);
}

/**
 * Réglages pour lesquels « vide » est une valeur volontaire.
 *
 * Pour un texte, un champ laissé vide reprend la valeur d'exemple : c'est
 * pratique. Pour un moyen de contact, ce serait dangereux — effacer le
 * numéro WhatsApp doit vraiment l'effacer.
 *
 * @return array
 */
function jmk_blankable() {
	return array( 'whatsapp', 'fiverr_url', 'email', 'webhook', 'privacy_url', 'brand_name' );
}

/**
 * Lit un réglage, dans la langue en cours s'il est traduisible.
 *
 * @param string $key      Clé.
 * @param mixed  $fallback Valeur de repli.
 * @param string $lang     Forcer une langue (sinon : celle de la page).
 * @return mixed
 */
function jmk_get( $key, $fallback = null, $lang = null ) {
	$saved = get_option( 'jmk_settings', array() );

	if ( in_array( $key, jmk_translatable(), true ) ) {
		$lang = $lang ? $lang : jmk_lang();
		if ( isset( $saved[ $lang ][ $key ] ) && '' !== $saved[ $lang ][ $key ] ) {
			return $saved[ $lang ][ $key ];
		}
		$pack = jmk_pack( $lang );
		if ( isset( $pack[ $key ] ) ) {
			return $pack[ $key ];
		}
		$en = jmk_pack( 'en' );
		return isset( $en[ $key ] ) ? $en[ $key ] : $fallback;
	}

	if ( isset( $saved[ $key ] ) ) {
		if ( '' !== $saved[ $key ] || in_array( $key, jmk_blankable(), true ) ) {
			return $saved[ $key ];
		}
	}

	$defaults = jmk_defaults();
	return isset( $defaults[ $key ] ) ? $defaults[ $key ] : $fallback;
}
