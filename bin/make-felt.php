<?php
/**
 * Feutre de table, généré.
 *
 * Deux trames de fibres croisées, du bruit fin, et un très léger relief :
 * de quoi casser l'aplat sans dessiner un motif que l'œil finit par repérer
 * quand la texture se répète.
 */
$S = 420;
$im = imagecreatetruecolor( $S, $S );
imagealphablending( $im, true );

// Fond.
$base = array( 0x11, 0x47, 0x36 );
imagefilledrectangle( $im, 0, 0, $S, $S, imagecolorallocate( $im, $base[0], $base[1], $base[2] ) );

mt_srand( 7 );

// Fibres : de courts traits dans deux directions, bouclés pour que les bords
// se raccordent — la texture est répétée en tuile.
for ( $i = 0; $i < 26000; $i++ ) {
	$x = mt_rand( 0, $S - 1 );
	$y = mt_rand( 0, $S - 1 );
	$dir = ( 0 === $i % 2 ) ? 1 : -1;
	$len = mt_rand( 2, 5 );
	$d   = mt_rand( -14, 16 );
	$r = max( 0, min( 255, $base[0] + $d ) );
	$g = max( 0, min( 255, $base[1] + $d ) );
	$b = max( 0, min( 255, $base[2] + $d ) );
	$col = imagecolorallocatealpha( $im, $r, $g, $b, 70 );
	for ( $k = 0; $k < $len; $k++ ) {
		imagesetpixel( $im, ( $x + $k ) % $S, ( $y + $dir * $k + $S ) % $S, $col );
	}
}

// Bruit fin.
for ( $i = 0; $i < 90000; $i++ ) {
	$x = mt_rand( 0, $S - 1 );
	$y = mt_rand( 0, $S - 1 );
	$d = mt_rand( -9, 9 );
	imagesetpixel( $im, $x, $y, imagecolorallocatealpha( $im,
		max( 0, min( 255, $base[0] + $d ) ),
		max( 0, min( 255, $base[1] + $d ) ),
		max( 0, min( 255, $base[2] + $d ) ), 88 ) );
}

imagejpeg( $im, $argv[1], 82 );
echo "felt : " . filesize( $argv[1] ) . " octets\n";
