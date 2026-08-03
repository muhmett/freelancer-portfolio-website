<?php
/**
 * Vérifications de la logique de tirage.
 *
 * Lancer avec : php tests/test-draw.php
 *
 * @package JeuxMarketing
 */

require __DIR__ . '/wp-stubs.php';
require __DIR__ . '/../jeux-marketing/inc/defaults.php';
require __DIR__ . '/../jeux-marketing/inc/leads.php';

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

/** Repart d'un état vierge. */
function reset_state() {
	$GLOBALS['jmk_options']    = array();
	$GLOBALS['jmk_transients'] = array();
	$GLOBALS['jmk_meta']       = array();
}

echo "\njmk_get — un contact effacé le reste\n";
reset_state();
ok( '212665827222' === jmk_get( 'whatsapp' ), 'sans réglage, le numéro d\'exemple s\'applique' );
update_option( 'jmk_settings', array( 'whatsapp' => '', 'hero_title' => '' ) );
ok( '' === jmk_get( 'whatsapp' ), 'numéro effacé volontairement : reste vide' );
ok( '' !== jmk_get( 'hero_title' ), 'texte laissé vide : reprend l\'exemple' );

echo "\njmk_draw — les plafonds sont respectés\n";
reset_state();
update_option(
	'jmk_settings',
	array(
		'lots' => array(
			array( 'label' => 'Rare', 'weight' => 50, 'cap' => 3, 'code' => 'RARE', 'hue' => 0, 'losing' => 0 ),
			array( 'label' => 'Perdu', 'weight' => 50, 'cap' => 0, 'code' => '', 'hue' => 0, 'losing' => 1 ),
		),
	)
);
$rare = 0;
for ( $i = 0; $i < 400; $i++ ) {
	list( $idx, $lot ) = jmk_draw();
	if ( 0 === $idx ) {
		++$rare;
		jmk_award( 0 );
	}
}
ok( 3 === $rare, "le lot plafonné à 3 est sorti exactement 3 fois (obtenu : $rare)" );
ok( 3 === jmk_awarded( 0 ), 'le compteur d\'attribution vaut 3' );

echo "\njmk_draw — le poids détermine la fréquence\n";
reset_state();
update_option(
	'jmk_settings',
	array(
		'lots' => array(
			array( 'label' => 'Souvent', 'weight' => 80, 'cap' => 0, 'code' => 'A', 'hue' => 0, 'losing' => 0 ),
			array( 'label' => 'Rare', 'weight' => 20, 'cap' => 0, 'code' => 'B', 'hue' => 0, 'losing' => 0 ),
		),
	)
);
$hits = array( 0, 0 );
for ( $i = 0; $i < 8000; $i++ ) {
	list( $idx, $lot ) = jmk_draw();
	++$hits[ $idx ];
}
$share = ( $hits[0] / 8000 ) * 100;
ok( $share > 74 && $share < 86, sprintf( 'poids 80/20 → environ 80%% (obtenu : %.1f%%)', $share ) );

echo "\njmk_draw — aucun lot configuré\n";
reset_state();
update_option( 'jmk_settings', array( 'lots' => array() ) );
list( $idx, $lot ) = jmk_draw();
ok( isset( $lot['label'], $lot['code'] ), 'renvoie un lot neutre au lieu d\'échouer' );
ok( ! empty( $lot['losing'] ), 'ce lot neutre est perdant' );

echo "\njmk_ajax_play — le lot gagné est mémorisé sous un jeton\n";
reset_state();
try {
	jmk_ajax_play();
	ok( false, 'la réponse JSON interrompt le flux' );
} catch ( JMK_Json_Response $r ) {
	$data = $r->payload;
	ok( $r->success, 'réponse en succès' );
	ok( ! empty( $data['token'] ), 'un jeton est renvoyé' );
	$stored = get_transient( 'jmk_draw_' . $data['token'] );
	ok( is_array( $stored ), 'le tirage est stocké côté serveur' );
	ok( $stored['code'] === $data['code'], 'le code stocké est celui annoncé' );
	ok( $stored['label'] === $data['label'], 'le libellé stocké est celui annoncé' );
}

echo "\njmk_ajax_lead — un lot inventé par le navigateur est ignoré\n";
reset_state();
$_POST = array(
	'name'  => 'Camille',
	'email' => 'camille@exemple.fr',
	'lot'   => 'Produit offert',
	'code'  => 'CADEAU-XXXX',
	'token' => 'jetoninexistant',
);
try {
	jmk_ajax_lead();
	ok( false, 'la réponse JSON interrompt le flux' );
} catch ( JMK_Json_Response $r ) {
	ok( $r->success, 'le participant est tout de même enregistré' );
	ok( '' === $r->payload['lot'], 'le lot annoncé par le navigateur est ignoré' );
	ok( '' === $r->payload['code'], 'le code annoncé par le navigateur est ignoré' );
}

echo "\njmk_ajax_lead — un jeton valide attribue le bon lot\n";
reset_state();
set_transient( 'jmk_draw_abc123', array( 'label' => '-25 %', 'code' => 'PROMO25-KLMN' ) );
$_POST = array(
	'name'  => 'Camille',
	'email' => 'camille2@exemple.fr',
	'token' => 'abc123',
);
try {
	jmk_ajax_lead();
	ok( false, 'la réponse JSON interrompt le flux' );
} catch ( JMK_Json_Response $r ) {
	ok( '-25 %' === $r->payload['lot'], 'le lot du tirage serveur est enregistré' );
	ok( 'PROMO25-KLMN' === $r->payload['code'], 'le code du tirage serveur est enregistré' );
	ok( false === get_transient( 'jmk_draw_abc123' ), 'le jeton est consommé, donc non réutilisable' );
}

echo "\n";
if ( $failures ) {
	echo "$failures test(s) en échec\n";
	exit( 1 );
}
echo "Tout est au vert.\n";
