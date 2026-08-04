<?php
/**
 * Vérifications des trois langues.
 *
 * Lancer avec : php tests/test-i18n.php
 *
 * @package JeuxMarketing
 */

require __DIR__ . '/wp-stubs.php';

define( 'JMK_DIR', dirname( __DIR__ ) . '/jeux-marketing' );

require JMK_DIR . '/inc/i18n.php';
require JMK_DIR . '/inc/defaults.php';

$failures = 0;

/**
 * Assertion.
 *
 * @param bool   $cond Condition.
 * @param string $msg  Description.
 */
function ok( $cond, $msg ) {
	global $failures;
	if ( $cond ) {
		echo "  ok   $msg\n";
		return;
	}
	++$failures;
	echo "  FAIL $msg\n";
}

/**
 * Force la langue courante, cache statique compris.
 *
 * @param string $lang Code.
 */
function use_lang( $lang ) {
	$_GET['lang'] = $lang;
	$ref = new ReflectionFunction( 'jmk_lang' );
	$ref->getStaticVariables();
	// jmk_lang() met en cache : on relance le processus via un sous-appel.
}

echo "\nles trois paquets se chargent\n";
foreach ( array( 'en', 'fr', 'ar' ) as $l ) {
	$pack = jmk_pack( $l );
	ok( ! empty( $pack['ui'] ), "$l : dictionnaire d'interface présent (" . count( $pack['ui'] ) . ' clés)' );
	ok( ! empty( $pack['lots'] ), "$l : lots par défaut présents" );
	ok( ! empty( $pack['skills'] ), "$l : compétences présentes" );
	ok( isset( $pack['reviews'] ) && array() === $pack['reviews'], "$l : avis vides, comme prévu" );
}

echo "\nles trois langues ont exactement les mêmes clés\n";
$en = jmk_pack( 'en' );
$fr = jmk_pack( 'fr' );
$ar = jmk_pack( 'ar' );
ok( array_keys( $en ) === array_keys( $fr ), 'en et fr : mêmes clés de contenu' );
ok( array_keys( $en ) === array_keys( $ar ), 'en et ar : mêmes clés de contenu' );
ok( array_keys( $en['ui'] ) === array_keys( $ar['ui'] ), 'en et ar : mêmes clés d\'interface' );

echo "\naucune traduction laissée en anglais par accident\n";
$same = 0;
foreach ( $en['ui'] as $k => $v ) {
	if ( $v === $fr['ui'][ $k ] && strlen( $v ) > 12 ) {
		++$same;
	}
}
ok( $same <= 2, "textes anglais restés identiques en français : $same (toléré : 2)" );

echo "\nle dictionnaire répond dans la bonne langue\n";
ok( 'Pricing' === $en['ui']['nav_prices'], 'en : nav_prices = Pricing' );
ok( 'Tarifs' === $fr['ui']['nav_prices'], 'fr : nav_prices = Tarifs' );
ok( 'الأسعار' === $ar['ui']['nav_prices'], 'ar : nav_prices en arabe' );

echo "\nsens d'écriture\n";
$langs = jmk_langs();
ok( 'ltr' === $langs['en']['dir'], 'anglais : de gauche à droite' );
ok( 'rtl' === $langs['ar']['dir'], 'arabe : de droite à gauche' );

echo "\nla langue par défaut est l'anglais\n";
$GLOBALS['jmk_options'] = array();
ok( 'en' === jmk_default_lang(), 'sans réglage, le site démarre en anglais' );
update_option( 'jmk_settings', array( 'lang' => 'fr' ) );
ok( 'fr' === jmk_default_lang(), 'le réglage change la langue par défaut' );
update_option( 'jmk_settings', array( 'lang' => 'zz' ) );
ok( 'en' === jmk_default_lang(), 'une langue inconnue retombe sur l\'anglais' );

echo "\njmk_get lit le contenu dans la langue demandée\n";
$GLOBALS['jmk_options'] = array();
ok( 'Spin first.' === jmk_get( 'hero_title', null, 'en' ), 'en : titre anglais' );
ok( 'Tournez d\'abord.' === jmk_get( 'hero_title', null, 'fr' ), 'fr : titre français' );
ok( 'أدر العجلة أولًا.' === jmk_get( 'hero_title', null, 'ar' ), 'ar : titre arabe' );

echo "\nune modification ne touche qu'une seule langue\n";
update_option(
	'jmk_settings',
	array(
		'fr' => array( 'hero_title' => 'Mon titre à moi' ),
	)
);
ok( 'Mon titre à moi' === jmk_get( 'hero_title', null, 'fr' ), 'fr : la modification est prise' );
ok( 'Spin first.' === jmk_get( 'hero_title', null, 'en' ), 'en : reste la valeur livrée' );
ok( 'أدر العجلة أولًا.' === jmk_get( 'hero_title', null, 'ar' ), 'ar : reste la valeur livrée' );

echo "\nles réglages techniques sont communs aux trois langues\n";
update_option( 'jmk_settings', array( 'accent' => '#3E9BF5', 'fr' => array( 'hero_title' => 'X' ) ) );
ok( '#3E9BF5' === jmk_get( 'accent', null, 'en' ), 'la couleur ne dépend pas de la langue' );
ok( '#3E9BF5' === jmk_get( 'accent', null, 'ar' ), 'la couleur est la même en arabe' );

echo "\nles contacts effacés le restent\n";
update_option( 'jmk_settings', array( 'whatsapp' => '' ) );
ok( '' === jmk_get( 'whatsapp' ), 'numéro effacé : reste vide' );

echo "\n";
if ( $failures ) {
	echo "$failures test(s) en échec\n";
	exit( 1 );
}
echo "Tout est au vert.\n";
