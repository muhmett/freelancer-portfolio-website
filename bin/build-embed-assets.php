<?php
/**
 * Injecte les pièces de la roue dans le moteur autonome.
 *
 * jmk-embed.js est livré seul : un client colle une balise et c'est tout.
 * Le laiton de la roue doit donc voyager dedans, en base64, plutôt qu'à
 * côté — un fichier de plus, c'est un chemin à régler et une panne à
 * assurer.
 *
 * Le bloc est délimité par des marqueurs et remplacé à chaque exécution :
 * les images restent la source, le script n'est jamais la copie qu'on
 * modifie à la main.
 *
 * Usage : php bin/build-embed-assets.php
 */

$racine = dirname( __DIR__ );
$moteur = $racine . '/jeux-marketing/assets/js/jmk-embed.js';
$img    = $racine . '/jeux-marketing/assets/img/wheel';

$pieces = array(
	'rim' => $img . '/rim.webp',
	'hub' => $img . '/hub.webp',
);

foreach ( $pieces as $nom => $chemin ) {
	if ( ! is_readable( $chemin ) ) {
		fwrite( STDERR, "Pièce manquante : $chemin\n" );
		fwrite( STDERR, "La produire d'abord avec bin/cut-disc.php.\n" );
		exit( 1 );
	}
}

$js = file_get_contents( $moteur );
if ( false === $js ) {
	fwrite( STDERR, "Moteur illisible : $moteur\n" );
	exit( 1 );
}

$debut = '/* ── PIÈCES:DÉBUT ── */';
$fin   = '/* ── PIÈCES:FIN ── */';
$i     = strpos( $js, $debut );
$j     = strpos( $js, $fin );

if ( false === $i || false === $j || $j <= $i ) {
	fwrite( STDERR, "Marqueurs introuvables dans jmk-embed.js.\n" );
	exit( 1 );
}

$lignes = array();
$total  = 0;
foreach ( $pieces as $nom => $chemin ) {
	$b64      = base64_encode( file_get_contents( $chemin ) );
	$total   += strlen( $b64 );
	$lignes[] = "\t\t" . $nom . ": 'data:image/webp;base64," . $b64 . "'";
}

$bloc = $debut . "\n"
	. "\tvar ART = {\n"
	. implode( ",\n", $lignes ) . "\n"
	. "\t};\n\t"
	. $fin;

$js = substr( $js, 0, $i ) . $bloc . substr( $js, $j + strlen( $fin ) );

file_put_contents( $moteur, $js );

printf(
	"jmk-embed.js : %d pièces injectées, %d Ko de base64, fichier à %d Ko\n",
	count( $pieces ),
	(int) round( $total / 1024 ),
	(int) round( filesize( $moteur ) / 1024 )
);
