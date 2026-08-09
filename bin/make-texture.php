<?php
/**
 * Prépare une image d'ambiance pour le thème.
 *
 * Les fonds sortent du générateur en 1024 px et en PNG : trop lourds pour
 * une page, et parfois trop clairs pour un thème sombre où ils ne sont
 * qu'un décor. Ce script les recadre en « couverture », les assombrit si
 * on le demande, et sort les deux formats — WebP pour tout le monde, JPEG
 * pour le reste.
 *
 * Le recadrage est en couverture et non en déformation : un tissu étiré
 * n'a plus la trame d'un tissu.
 *
 * Usage : php bin/make-texture.php <entrée> <sortie-sans-extension> <l> <h> [assombrir 0-1]
 */

$in     = $argv[1] ?? '';
$out    = $argv[2] ?? '';
$w      = (int) ( $argv[3] ?? 0 );
$h      = (int) ( $argv[4] ?? 0 );
$sombre = (float) ( $argv[5] ?? 0 );

if ( ! $in || ! $out || $w < 1 || $h < 1 ) {
	fwrite( STDERR, "Usage : php bin/make-texture.php <entrée> <sortie> <l> <h> [assombrir 0-1]\n" );
	exit( 1 );
}
if ( ! is_readable( $in ) ) {
	fwrite( STDERR, "Fichier illisible : $in\n" );
	exit( 1 );
}

$src = @imagecreatefrompng( $in );
if ( ! $src ) {
	$src = @imagecreatefromjpeg( $in );
}
if ( ! $src ) {
	fwrite( STDERR, "Image illisible : $in\n" );
	exit( 1 );
}

$sw = imagesx( $src );
$sh = imagesy( $src );

// Couverture : on prend la plus grande échelle des deux, puis on centre.
$k  = max( $w / $sw, $h / $sh );
$cw = (int) round( $w / $k );
$ch = (int) round( $h / $k );
$cx = (int) round( ( $sw - $cw ) / 2 );
$cy = (int) round( ( $sh - $ch ) / 2 );

$dst = imagecreatetruecolor( $w, $h );
imagecopyresampled( $dst, $src, 0, 0, $cx, $cy, $w, $h, $cw, $ch );

if ( $sombre > 0 ) {
	$voile = imagecreatetruecolor( $w, $h );
	imagefilledrectangle( $voile, 0, 0, $w, $h, imagecolorallocate( $voile, 0, 0, 0 ) );
	imagecopymerge( $dst, $voile, 0, 0, 0, 0, $w, $h, (int) round( $sombre * 100 ) );
	imagedestroy( $voile );
}

imagewebp( $dst, $out . '.webp', 80 );
imagejpeg( $dst, $out . '.jpg', 82 );

printf(
	"%s → %s.webp (%d Ko) + %s.jpg (%d Ko), %dx%d\n",
	basename( $in ),
	basename( $out ),
	(int) round( filesize( $out . '.webp' ) / 1024 ),
	basename( $out ),
	(int) round( filesize( $out . '.jpg' ) / 1024 ),
	$w,
	$h
);
