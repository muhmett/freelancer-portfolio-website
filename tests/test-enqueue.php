<?php
/**
 * Vérifie l'ordre de chargement des scripts publics.
 *
 * Lancer avec : php tests/test-enqueue.php
 *
 * app.js monte les bannières et renonce si le moteur n'est pas encore là.
 * Chargé dans le mauvais ordre il ne lève aucune erreur : il laisse des
 * cadres vides. C'est exactement ce qui est parti en ligne, et aucune
 * vérification navigateur ne pouvait l'attraper — le harnais chargeait les
 * deux fichiers dans le bon ordre par construction.
 *
 * @package JeuxMarketing
 */

require __DIR__ . '/wp-stubs.php';

/**
 * Racine du thème, lue par functions.php pour poser ses constantes.
 *
 * @return string
 */
function get_template_directory() {
	return dirname( __DIR__ ) . '/jeux-marketing';
}

require get_template_directory() . '/functions.php';

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
 * Position d'un script dans la file.
 *
 * @param string $handle Nom.
 * @return int|false
 */
function jmk_pos( $handle ) {
	foreach ( $GLOBALS['jmk_scripts'] as $i => $s ) {
		if ( $handle === $s['handle'] ) {
			return $i;
		}
	}
	return false;
}

/**
 * Dépendances déclarées d'un script.
 *
 * @param string $handle Nom.
 * @return array
 */
function jmk_deps( $handle ) {
	foreach ( $GLOBALS['jmk_scripts'] as $s ) {
		if ( $handle === $s['handle'] ) {
			return $s['deps'];
		}
	}
	return array();
}

echo "\npage d'accueil, sections de bannières actives\n";

$GLOBALS['jmk_is_front'] = true;
$GLOBALS['jmk_scripts']  = array();
jmk_assets();

ok( false !== jmk_pos( 'jmk-app' ), 'app.js est mis en file' );
ok( false !== jmk_pos( 'jmk-banner' ), 'le moteur de bannières est mis en file' );
ok( false !== jmk_pos( 'jmk-banner' ) && jmk_pos( 'jmk-banner' ) < jmk_pos( 'jmk-app' ),
	'le moteur passe avant app.js' );
ok( in_array( 'jmk-banner', jmk_deps( 'jmk-app' ), true ),
	'app.js le déclare en dépendance' );

echo "\nailleurs que sur la page d'accueil\n";

$GLOBALS['jmk_is_front'] = false;
$GLOBALS['jmk_scripts']  = array();
jmk_assets();

ok( false === jmk_pos( 'jmk-banner' ), 'le moteur n’est pas chargé pour rien' );
ok( false !== jmk_pos( 'jmk-app' ), 'app.js reste chargé' );
ok( array() === jmk_deps( 'jmk-app' ), 'app.js ne dépend de rien qui ne soit pas là' );

echo "\nles deux sections désactivées\n";

$GLOBALS['jmk_is_front'] = true;
$saved = get_option( 'jmk_settings', array() );
$saved['sec_banners'] = 0;
$saved['sec_bwork']   = 0;
update_option( 'jmk_settings', $saved );

$GLOBALS['jmk_scripts'] = array();
jmk_assets();

ok( false === jmk_pos( 'jmk-banner' ), 'aucune section : aucun moteur' );
ok( ! jmk_banners_visible(), 'jmk_banners_visible() dit la même chose' );

echo "\nsur la page « bannières », hors accueil\n";

/* La page du métier bannière n'est pas l'accueil : sans cette branche, elle
   sortirait avec le portfolio et aucun moteur pour le peindre. */
$GLOBALS['jmk_is_front']     = false;
$GLOBALS['jmk_page_template'] = 'template-banners.php';
$GLOBALS['jmk_scripts']       = array();
jmk_assets();

ok( false !== jmk_pos( 'jmk-banner' ), 'le moteur est chargé sur la page bannières' );
ok( in_array( 'jmk-banner', jmk_deps( 'jmk-app' ), true ), 'et reste une dépendance de app.js' );

$GLOBALS['jmk_page_template'] = '';

echo "\n" . ( $failures ? "$failures échec(s).\n" : "Tout est au vert.\n" );
exit( $failures ? 1 : 0 );
