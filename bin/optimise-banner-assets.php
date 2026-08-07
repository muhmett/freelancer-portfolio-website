<?php
/**
 * Prépare les visuels des campagnes de démonstration pour le thème.
 *
 *   php bin/optimise-banner-assets.php
 *
 * Les générations d'origine font 1,3 Mo en PNG. Le plus grand format de
 * bannière est un 970×250 : une source de 1200 px de large suffit largement,
 * et en JPEG elle tombe à une centaine de kilos. Un thème ne se traîne pas
 * cinq mégaoctets pour quatre images de portfolio.
 *
 * @package JeuxMarketing
 */

$root = dirname( __DIR__ );
$src  = $root . '/bin/banner-assets';
$dst  = $root . '/jeux-marketing/assets/img/work';

@mkdir( $dst, 0777, true );

$files = array( 'sneaker', 'watch', 'eyewear', 'scent' );
$total = 0;

foreach ( $files as $name ) {
	$in = $src . '/' . $name . '.png';
	if ( ! file_exists( $in ) ) {
		echo "  absent : $name.png\n";
		continue;
	}

	$im = imagecreatefrompng( $in );
	$sw = imagesx( $im );
	$sh = imagesy( $im );

	$tw = 1200;
	$th = (int) round( $sh * ( $tw / $sw ) );

	$out = imagecreatetruecolor( $tw, $th );
	imagecopyresampled( $out, $im, 0, 0, 0, 0, $tw, $th, $sw, $sh );

	$path = $dst . '/' . $name . '.jpg';
	imagejpeg( $out, $path, 78 );

	imagedestroy( $out );
	imagedestroy( $im );

	$size   = filesize( $path );
	$total += $size;
	printf( "  %-10s %4d×%-4d  %s\n", $name . '.jpg', $tw, $th, round( $size / 1024 ) . ' Ko' );
}

printf( "\n  total : %s\n", round( $total / 1024 ) . ' Ko' );
