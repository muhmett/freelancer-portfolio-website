<?php
/**
 * Fonds du carrousel d'accueil, générés — pas de banque d'images, pas de
 * licence à vérifier. Même logique que le feutre ou la trame métal : du
 * dégradé, du grain, quelques formes, et deux formats en sortie.
 *
 * Le dégradé est calculé pixel à pixel en basse résolution puis agrandi :
 * imagefilledellipse en transparence empilée donne un disque dur avec un
 * simple liséré flou, pas le halo continu de .aurora en CSS. Ici chaque
 * pixel basse résolution reçoit sa propre intensité (smoothstep sur la
 * distance aux foyers), et l'agrandissement final rejoue le flou.
 *
 * Quatre ambiances, une par ce que le métier montre le plus souvent : le
 * confetti d'un gain, l'aurore déjà posée derrière la page, la grille d'un
 * mur de bannières, les rayons d'une roue. Aucune ne porte de texte — le
 * texte reste au thème, en surimpression. Les foyers restent en bord de
 * cadre : le centre-gauche, où s'assoit le titre, reste sombre.
 *
 * Usage : php bin/make-hero-bg.php <dossier-sortie> [largeur] [hauteur]
 */

$out   = rtrim( $argv[1] ?? __DIR__ . '/../jeux-marketing/assets/img/hero', '/' );
$W     = (int) ( $argv[2] ?? 1920 );
$H     = (int) ( $argv[3] ?? 1080 );
$SCALE = 5; // basse résolution = 1/5, puis agrandissement en flou.

if ( ! is_dir( $out ) && ! mkdir( $out, 0777, true ) && ! is_dir( $out ) ) {
	fwrite( STDERR, "Impossible de créer $out\n" );
	exit( 1 );
}

function jmk_smooth( $t ) {
	$t = max( 0, min( 1, $t ) );
	return $t * $t * ( 3 - 2 * $t );
}

/**
 * Champ de lumière basse résolution : chaque foyer est un cercle dont
 * l'intensité décroît en douceur (smoothstep) jusqu'à son rayon. Les
 * foyers se peignent dans l'ordre, mélangés vers leur couleur — pas
 * additionnés — pour ne jamais dépasser le blanc.
 *
 * $foyers[] = [fx 0..1, fy 0..1, rayon (fraction de max(W,H)), [r,g,b], intensité 0..1]
 * $rayons_conf : optionnel, ajoute des rayons coniques autour d'un centre.
 */
function jmk_hero_field( $w, $h, $scale, $ink, $foyers, $rayons_conf = null ) {
	$lw = (int) round( $w / $scale );
	$lh = (int) round( $h / $scale );
	$im = imagecreatetruecolor( $lw, $lh );

	$maxdim = max( $w, $h ) / $scale;

	for ( $y = 0; $y < $lh; $y++ ) {
		for ( $x = 0; $x < $lw; $x++ ) {
			$r = $ink[0];
			$g = $ink[1];
			$b = $ink[2];

			foreach ( $foyers as $f ) {
				list( $fx, $fy, $fr, $col, $inten ) = $f;
				$cx   = $fx * $lw;
				$cy   = $fy * $lh;
				$rad  = $fr * $maxdim;
				$dist = sqrt( ( $x - $cx ) ** 2 + ( $y - $cy ) ** 2 );
				$t    = jmk_smooth( 1 - ( $dist / max( 1, $rad ) ) ) * $inten;
				if ( $t > 0.002 ) {
					$r = $r + ( $col[0] - $r ) * $t;
					$g = $g + ( $col[1] - $g ) * $t;
					$b = $b + ( $col[2] - $b ) * $t;
				}
			}

			if ( $rayons_conf ) {
				list( $rcx, $rcy, $rcount, $rcol, $rmax, $rinten ) = $rayons_conf;
				$dx   = $x - $rcx * $lw;
				$dy   = $y - $rcy * $lh;
				$dist = sqrt( $dx * $dx + $dy * $dy );
				if ( $dist < $rmax * $maxdim ) {
					$ang  = atan2( $dy, $dx );
					$wave = ( sin( $ang * $rcount ) + 1 ) / 2; // 0..1, alternance des rayons
					$fall = jmk_smooth( 1 - ( $dist / ( $rmax * $maxdim ) ) );
					$t    = $wave * $fall * $rinten;
					if ( $t > 0.002 ) {
						$r = $r + ( $rcol[0] - $r ) * $t;
						$g = $g + ( $rcol[1] - $g ) * $t;
						$b = $b + ( $rcol[2] - $b ) * $t;
					}
				}
			}

			imagesetpixel( $im, $x, $y, imagecolorallocate(
				$im,
				(int) round( max( 0, min( 255, $r ) ) ),
				(int) round( max( 0, min( 255, $g ) ) ),
				(int) round( max( 0, min( 255, $b ) ) )
			) );
		}
	}

	$big = imagecreatetruecolor( $w, $h );
	imagecopyresampled( $big, $im, 0, 0, 0, 0, $w, $h, $lw, $lh );
	imagedestroy( $im );
	return $big;
}

/** Grain fin, pour casser l'aplat numérique du dégradé agrandi. */
function jmk_hero_grain( $im, $w, $h, $amount = 8, $density = 60000, $seed = 42 ) {
	mt_srand( $seed );
	for ( $i = 0; $i < $density; $i++ ) {
		$x = mt_rand( 0, $w - 1 );
		$y = mt_rand( 0, $h - 1 );
		$d = mt_rand( -$amount, $amount );
		$rgb = imagecolorat( $im, $x, $y );
		$r = max( 0, min( 255, ( ( $rgb >> 16 ) & 0xFF ) + $d ) );
		$g = max( 0, min( 255, ( ( $rgb >> 8 ) & 0xFF ) + $d ) );
		$b = max( 0, min( 255, ( $rgb & 0xFF ) + $d ) );
		imagesetpixel( $im, $x, $y, imagecolorallocate( $im, $r, $g, $b ) );
	}
}

function jmk_hero_save( $im, $path ) {
	imagewebp( $im, $path . '.webp', 78 );
	imagejpeg( $im, $path . '.jpg', 80 );
	imagedestroy( $im );
	printf( "  → %s.webp (%d Ko) + %s.jpg (%d Ko)\n",
		basename( $path ), (int) round( filesize( $path . '.webp' ) / 1024 ),
		basename( $path ), (int) round( filesize( $path . '.jpg' ) / 1024 ) );
}

$ink   = array( 0x15, 0x0C, 0x1D ); // --ink
$rose  = array( 0xF2, 0x50, 0x6B );
$or    = array( 0xD9, 0xA4, 0x41 );
$or2   = array( 0xF0, 0xC9, 0x6B );
$mint  = array( 0x48, 0xC9, 0xA9 );
$paper = array( 0xF5, 0xEF, 0xE6 );

echo "Confettis…\n";
$im = jmk_hero_field( $W, $H, $SCALE, $ink, array(
	array( 0.92, 0.06, 0.42, $rose, 0.62 ),
	array( 0.06, 0.94, 0.40, $or, 0.6 ),
	array( 0.80, 0.90, 0.30, $mint, 0.42 ),
) );
mt_srand( 11 );
$palette = array( $rose, $or, $or2, $mint );
for ( $i = 0; $i < 220; $i++ ) {
	$x = mt_rand( 0, $W );
	$y = mt_rand( 0, $H );
	$s = mt_rand( 4, 14 );
	$c = $palette[ array_rand( $palette ) ];
	$a = mt_rand( 40, 85 );
	$col = imagecolorallocatealpha( $im, $c[0], $c[1], $c[2], $a );
	if ( 0 === $i % 3 ) {
		imagefilledrectangle( $im, $x, $y, $x + $s, $y + (int) ( $s * 0.4 ), $col );
	} else {
		imagefilledellipse( $im, $x, $y, $s, $s, $col );
	}
}
jmk_hero_grain( $im, $W, $H );
jmk_hero_save( $im, $out . '/confetti' );

echo "Aurore…\n";
$im = jmk_hero_field( $W, $H, $SCALE, $ink, array(
	array( 0.85, -0.05, 0.5, $or, 0.55 ),
	array( 0.98, 0.55, 0.46, $rose, 0.5 ),
	array( 0.15, 1.05, 0.42, $mint, 0.4 ),
) );
jmk_hero_grain( $im, $W, $H, 6 );
jmk_hero_save( $im, $out . '/aurore' );

echo "Rayons…\n";
$im = jmk_hero_field( $W, $H, $SCALE, $ink, array(
	array( 0.82, 0.5, 0.5, $or, 0.38 ),
), array( 0.82, 0.5, 34, $or2, 0.58, 0.5 ) );
jmk_hero_grain( $im, $W, $H, 6 );
jmk_hero_save( $im, $out . '/rayons' );

echo "Grille…\n";
$im = jmk_hero_field( $W, $H, $SCALE, $ink, array(
	array( 0.10, 0.85, 0.44, $mint, 0.35 ),
	array( 0.95, 0.15, 0.46, $rose, 0.32 ),
) );
$cols = 7;
$rows = 4;
$gap  = 18;
$cw   = ( $W - $gap * ( $cols + 1 ) ) / $cols;
$ch   = ( $H - $gap * ( $rows + 1 ) ) / $rows;
for ( $r = 0; $r < $rows; $r++ ) {
	for ( $c = 0; $c < $cols; $c++ ) {
		$x   = $gap + $c * ( $cw + $gap );
		$y   = $gap + $r * ( $ch + $gap );
		$col = imagecolorallocatealpha( $im, $paper[0], $paper[1], $paper[2], 108 );
		imagefilledrectangle( $im, (int) $x, (int) $y, (int) ( $x + $cw ), (int) ( $y + $ch ), $col );
		$edge = imagecolorallocatealpha( $im, $paper[0], $paper[1], $paper[2], 98 );
		imagerectangle( $im, (int) $x, (int) $y, (int) ( $x + $cw ), (int) ( $y + $ch ), $edge );
	}
}
jmk_hero_grain( $im, $W, $H, 5 );
jmk_hero_save( $im, $out . '/grille' );

echo "Terminé : $out\n";
