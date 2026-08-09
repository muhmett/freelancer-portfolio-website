<?php
/**
 * Découpe un anneau ou un disque rendu sur fond noir.
 *
 * Les pièces métalliques de la roue sont générées sur fond noir, et le
 * centre de l'anneau est noir lui aussi. Une découpe par luminance les
 * traiterait pareil : le fond partirait, mais les creux sombres de la
 * gravure deviendraient translucides et laisseraient passer les couleurs
 * des quartiers. Une pièce de métal n'est pas translucide.
 *
 * On découpe donc par géométrie, pas par couleur : la pièce est centrée et
 * circulaire, ses rayons se mesurent, et le masque est plein entre les
 * deux. Le métal reste opaque quelle que soit sa teinte.
 *
 * Deux fichiers sortent : un WebP, que les navigateurs prennent, et un PNG
 * de secours pour le reste. En laiton dégradé, l'écart n'est pas marginal —
 * 65 Ko contre 611 Ko pour la même image. Le PNG peut donc être plus petit :
 * il n'est chargé que là où le WebP n'existe pas.
 *
 * Usage : php bin/cut-disc.php <entrée.png> <sortie> <ring|disc> [taille] [taille-png]
 */

$in    = $argv[1] ?? '';
$out   = $argv[2] ?? '';
$mode  = $argv[3] ?? 'ring';
$size  = (int) ( $argv[4] ?? 0 );
$sizeP = (int) ( $argv[5] ?? 0 );

if ( ! $in || ! $out || ! in_array( $mode, array( 'ring', 'disc' ), true ) ) {
	fwrite( STDERR, "Usage : php bin/cut-disc.php <entrée.png> <sortie.png> <ring|disc> [taille]\n" );
	exit( 1 );
}
if ( ! is_readable( $in ) ) {
	fwrite( STDERR, "Fichier illisible : $in\n" );
	exit( 1 );
}

$src = imagecreatefrompng( $in );
if ( ! $src ) {
	fwrite( STDERR, "PNG invalide : $in\n" );
	exit( 1 );
}

$w  = imagesx( $src );
$h  = imagesy( $src );
$cx = ( $w - 1 ) / 2;
$cy = ( $h - 1 ) / 2;

/** Luminance d'un pixel, 0-255. */
$luma = function ( $x, $y ) use ( $src ) {
	$c = imagecolorat( $src, (int) $x, (int) $y );
	return 0.2126 * ( ( $c >> 16 ) & 0xFF ) + 0.7152 * ( ( $c >> 8 ) & 0xFF ) + 0.0722 * ( $c & 0xFF );
};

// Le fond est un noir franc ; le métal, même dans l'ombre, s'en détache.
$seuil = 26;
$rMax  = min( $cx, $cy );
$rays  = 720;

$dehors = array();
$dedans = array();

for ( $i = 0; $i < $rays; $i++ ) {
	$a  = 2 * M_PI * $i / $rays;
	$dx = cos( $a );
	$dy = sin( $a );

	for ( $r = $rMax; $r >= 1; $r -= 0.5 ) {
		if ( $luma( $cx + $dx * $r, $cy + $dy * $r ) > $seuil ) {
			$dehors[] = $r;
			break;
		}
	}

	if ( 'ring' === $mode ) {
		for ( $r = 1; $r <= $rMax; $r += 0.5 ) {
			if ( $luma( $cx + $dx * $r, $cy + $dy * $r ) > $seuil ) {
				$dedans[] = $r;
				break;
			}
		}
	}
}

if ( ! $dehors ) {
	fwrite( STDERR, "Aucune matière trouvée : l'image est-elle bien sur fond noir ?\n" );
	exit( 1 );
}

/** La médiane ignore les clous qui dépassent et les rayons manqués. */
$mediane = function ( array $v ) {
	sort( $v );
	$n = count( $v );
	return 0 === $n % 2
		? ( $v[ $n / 2 - 1 ] + $v[ $n / 2 ] ) / 2
		: $v[ (int) ( $n / 2 ) ];
};

$rOut = $mediane( $dehors );
$rIn  = ( 'ring' === $mode && $dedans ) ? $mediane( $dedans ) : 0.0;

// Les clous du pourtour dépassent la médiane : on rouvre d'un cheveu pour
// ne pas les raboter.
$rOut = min( $rMax, $rOut + 2 );
if ( $rIn > 0 ) {
	$rIn = max( 0, $rIn - 1 );
}

$dst = imagecreatetruecolor( $w, $h );
imagealphablending( $dst, false );
imagesavealpha( $dst, true );
imagefill( $dst, 0, 0, imagecolorallocatealpha( $dst, 0, 0, 0, 127 ) );

$plume = 1.5;

for ( $y = 0; $y < $h; $y++ ) {
	for ( $x = 0; $x < $w; $x++ ) {
		$d = sqrt( ( $x - $cx ) ** 2 + ( $y - $cy ) ** 2 );

		// 1 au cœur de la pièce, 0 dehors, dégradé sur la plume.
		$k = min(
			max( ( $rOut - $d ) / $plume, 0 ),
			$rIn > 0 ? min( max( ( $d - $rIn ) / $plume, 0 ), 1 ) : 1
		);
		$k = min( $k, 1 );

		if ( $k <= 0 ) {
			continue;
		}

		$c = imagecolorat( $src, $x, $y );
		imagesetpixel(
			$dst,
			$x,
			$y,
			imagecolorallocatealpha(
				$dst,
				( $c >> 16 ) & 0xFF,
				( $c >> 8 ) & 0xFF,
				$c & 0xFF,
				(int) round( 127 * ( 1 - $k ) )
			)
		);
	}
}

/** Réduit une image en gardant la transparence. */
$reduire = function ( $img, $de, $vers ) {
	if ( $vers < 1 || $vers === $de ) {
		return $img;
	}
	$petit = imagecreatetruecolor( $vers, $vers );
	imagealphablending( $petit, false );
	imagesavealpha( $petit, true );
	imagefill( $petit, 0, 0, imagecolorallocatealpha( $petit, 0, 0, 0, 127 ) );
	imagecopyresampled( $petit, $img, 0, 0, 0, 0, $vers, $vers, $de, $de );
	return $petit;
};

$base  = preg_replace( '/\.(png|webp)$/i', '', $out );
$size  = $size > 0 ? $size : $w;
$sizeP = $sizeP > 0 ? $sizeP : $size;

$web = $reduire( $dst, $w, $size );
imagewebp( $web, $base . '.webp', 82 );

$png = $reduire( $dst, $w, $sizeP );
imagepng( $png, $base . '.png', 9 );

printf(
	"%s → %s.webp (%d px, %d Ko) + %s.png (%d px, %d Ko) — %s, rayon %.1f–%.1f sur %d px\n",
	basename( $in ),
	basename( $base ),
	$size,
	(int) round( filesize( $base . '.webp' ) / 1024 ),
	basename( $base ),
	$sizeP,
	(int) round( filesize( $base . '.png' ) / 1024 ),
	$mode,
	$rIn,
	$rOut,
	$w
);
