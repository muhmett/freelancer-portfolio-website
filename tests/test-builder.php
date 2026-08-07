<?php
/**
 * Vérifications du créateur de jeu.
 *
 * Lancer avec : php tests/test-builder.php
 *
 * Ce qui compte ici : rien de ce qui arrive du navigateur ne doit ressortir
 * tel quel dans le fichier téléchargé, et le fichier produit doit être
 * réellement autonome.
 *
 * @package JeuxMarketing
 */

require __DIR__ . '/wp-stubs.php';

define( 'JMK_DIR', dirname( __DIR__ ) . '/jeux-marketing' );
define( 'JMK_URI', 'https://exemple.test/wp-content/themes/jeux-marketing' );
define( 'JMK_VERSION', '1.0.0' );

require JMK_DIR . '/inc/i18n.php';
require JMK_DIR . '/inc/defaults.php';

/**
 * Version d'un fichier, doublure de celle de functions.php.
 *
 * @param string $rel Chemin relatif.
 * @return string
 */
function jmk_asset_version( $rel ) {
	return JMK_VERSION;
}

/**
 * Langue éditée, doublure de celle de inc/admin.php.
 *
 * @return string
 */
function jmk_admin_lang() {
	return 'en';
}

require JMK_DIR . '/inc/builder.php';

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

echo "\nnettoyage de la configuration reçue\n";

$dirty = array(
	'game'   => 'roue-qui-nexiste-pas',
	'accent' => 'javascript:alert(1)',
	'skin'   => 'inconnu',
	'dir'    => 'ltr',
	'brand'  => '<script>alert(1)</script>Ma marque',
	'title'  => "Titre\navec saut",
	'onePlay' => '1',
	'drawUrl' => 'javascript:alert(1)',
	'leadUrl' => 'https://exemple.test/hook',
	'lots'   => array(
		array( 'label' => '-10 %', 'weight' => '9999', 'cap' => '-4', 'code' => 'pro mo/10', 'hue' => '999', 'losing' => 0 ),
		array( 'label' => '', 'weight' => 10 ),
		array( 'label' => 'Perdu', 'weight' => 5, 'cap' => 0, 'code' => 'NEDOITPASSORTIR', 'hue' => 40, 'losing' => 1 ),
	),
	'form'   => array( 'on' => 1, 'name' => 1, 'privacy' => 'javascript:alert(1)' ),
);

$c = jmk_builder_sanitize( $dirty );

ok( 'wheel' === $c['game'], 'une mécanique inconnue retombe sur la roue' );
ok( '#D9A441' === $c['accent'], 'une couleur invalide retombe sur celle du thème' );
ok( 'elegant' === $c['skin'], 'un habillage inconnu retombe sur le sobre' );
ok( false === strpos( $c['brand'], '<script' ), 'le nom de marque est nettoyé' );
ok( '' === $c['drawUrl'], 'une adresse de tirage non http est rejetée' );
ok( 'https://exemple.test/hook' === $c['leadUrl'], 'une adresse de réception valide passe' );
ok( '' === $c['form']['privacy'], 'un lien RGPD non http est rejeté' );
ok( true === $c['onePlay'], 'une seule partie : la case est reprise' );

echo "\nles lots\n";

ok( 2 === count( $c['lots'] ), 'un lot sans libellé est retiré' );
ok( 1000 === (int) $c['lots'][0]['weight'], 'un poids démesuré est ramené au maximum' );
ok( null === $c['lots'][0]['cap'], 'un plafond négatif vaut illimité' );
ok( 'PROMO10' === $c['lots'][0]['code'], 'le code est mis en majuscules et débarrassé du reste' );
ok( 180 === (int) $c['lots'][0]['hue'], 'une teinte hors bornes est ramenée dans les bornes' );
ok( '' === $c['lots'][1]['code'], 'un lot perdant ne garde aucun code' );
ok( null === $c['lots'][1]['hue'], 'un lot perdant n’a pas de teinte' );
ok( true === $c['lots'][1]['losing'], 'le lot perdant reste perdant' );

echo "\naucun lot exploitable\n";

$empty = jmk_builder_sanitize( array( 'lots' => array( array( 'label' => '' ) ) ) );
ok( is_array( $empty['lots'] ) && 0 === count( $empty['lots'] ), 'la liste peut être vide côté PHP' );

echo "\nles questions du quiz\n";

$q = jmk_builder_sanitize(
	array(
		'quiz' => array(
			array( 'q' => 'Une question ?', 'o' => array( 'A', 'B', '' ), 'a' => 7 ),
			array( 'q' => 'Sans réponses', 'o' => array( 'Seule' ), 'a' => 0 ),
			array( 'q' => '', 'o' => array( 'A', 'B' ), 'a' => 0 ),
		),
	)
);
ok( 1 === count( $q['quiz'] ), 'une question sans deux réponses est retirée' );
ok( 2 === count( $q['quiz'][0]['o'] ), 'les réponses vides sont retirées' );
ok( 1 === $q['quiz'][0]['a'], 'une bonne réponse hors bornes est ramenée dans les bornes' );

echo "\nle fichier téléchargé\n";

$html = jmk_builder_html( $c );

ok( 0 === strpos( $html, '<!doctype html>' ), 'c’est bien une page complète' );
ok( false !== strpos( $html, 'JMKGame.mount' ), 'le jeu est monté dans la page' );
ok( false !== strpos( $html, 'var VERSION' ), 'le moteur est recopié dans le fichier' );
ok( false === strpos( $html, 'wp-content/themes' ), 'le fichier ne renvoie à aucun chemin WordPress' );
ok( false !== strpos( $html, 'dir="ltr"' ), 'le sens de lecture est posé sur la page' );

// Deux blocs, et deux seulement : le moteur puis l'appel. Compter les balises
// fermantes plutôt que les ouvrantes — le moteur cite `<script src=…>` dans
// son commentaire d'en-tête, et une balise ouvrante à l'intérieur d'un script
// est inerte. Seule une fermante peut en sortir.
ok( 2 === substr_count( $html, '</script>' ), 'la page ne porte que ses deux blocs de script' );
ok( false === strpos( $html, '<script src=' ) || false === strpos( $html, '<script src="http' ), 'aucun fichier extérieur n’est appelé' );

// Une balise fermante dans un libellé refermerait le <script> qui porte le
// moteur : la page se briserait en deux au milieu du code.
$evil = jmk_builder_sanitize(
	array(
		'lots' => array( array( 'label' => 'Fin </script><script>alert(1)</script>', 'weight' => 10 ) ),
	)
);
$evilHtml = jmk_builder_html( $evil );
$closing  = substr_count( strtolower( $evilHtml ), '</script>' );
ok( 2 === $closing, 'un libellé ne peut pas refermer le script (' . $closing . ' fermetures)' );
ok( false === strpos( $evilHtml, 'alert(1)</script>' ), 'la balise fermante injectée est neutralisée' );

echo "\nle fichier tourne vraiment\n";

$engine = file_get_contents( JMK_DIR . '/assets/js/jmk-embed.js' );
ok( false !== strpos( $engine, 'JMKGame' ), 'le moteur existe et expose JMKGame' );
ok( strlen( $html ) > strlen( $engine ), 'le fichier produit contient au moins le moteur' );

echo "\nreprise des réglages sur le site\n";

$GLOBALS['jmk_options'] = array();
$_POST                  = array( 'config' => wp_json_encode( $c ) );

// La doublure de wp_safe_redirect lève : sans cela le `exit` qui la suit
// couperait la série de tests au milieu.
try {
	jmk_builder_apply();
	ok( false, 'jmk_builder_apply() aurait dû rediriger' );
} catch ( JmkRedirect $e ) {
	ok( false !== strpos( $e->getMessage(), 'jmk-builder' ), 'on revient sur le créateur après reprise' );
}

$saved = get_option( 'jmk_settings', array() );
ok( isset( $saved['en']['lots'] ) && 2 === count( $saved['en']['lots'] ), 'les lots atterrissent dans la langue éditée' );
ok( isset( $saved['accent'] ) && '#D9A441' === $saved['accent'], 'la couleur est reprise' );
ok( isset( $saved['skin'] ) && 'elegant' === $saved['skin'], 'l’habillage est repris' );
ok( 0 === (int) $saved['en']['lots'][0]['cap'], 'un plafond illimité redevient 0 côté réglages' );
ok( ! isset( $saved['en']['hero_title'] ), 'les textes du site ne sont pas touchés' );

echo "\n" . ( $failures ? "$failures échec(s).\n" : "Tout est au vert.\n" );
exit( $failures ? 1 : 0 );
