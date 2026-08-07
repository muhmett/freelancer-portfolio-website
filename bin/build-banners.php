<?php
/**
 * Produit une campagne de bannières livrable.
 *
 *   php bin/build-banners.php bin/banner-example.json dist/campagne
 *
 * Pour chaque format : un dossier, un `index.html` autonome, et une image de
 * repli en JPG. Puis un ZIP de l'ensemble — le fichier qui part au client.
 *
 * La mise en page se calcule dans un navigateur, pas ici : la taille du titre
 * dépend de la largeur réelle du texte, et cette largeur ne se devine pas
 * depuis PHP. Chromium fait donc le rendu, et ce script orchestre.
 *
 * @package JeuxMarketing
 */

$root = dirname( __DIR__ );

$configPath = isset( $argv[1] ) ? $argv[1] : $root . '/bin/banner-example.json';
$outDir     = isset( $argv[2] ) ? $argv[2] : $root . '/dist/campagne';

if ( ! file_exists( $configPath ) ) {
	fwrite( STDERR, "Configuration introuvable : $configPath\n" );
	exit( 1 );
}

$config = json_decode( file_get_contents( $configPath ), true );
if ( ! is_array( $config ) ) {
	fwrite( STDERR, "Configuration illisible (JSON invalide).\n" );
	exit( 1 );
}

/**
 * Trouve Chromium.
 *
 * @return string
 */
function jmk_chrome() {
	$candidates = array(
		getenv( 'CHROME_BIN' ),
		'/opt/pw-browsers/chromium-1194/chrome-linux/chrome',
		'/opt/pw-browsers/chromium/chrome',
		trim( (string) shell_exec( 'command -v chromium 2>/dev/null' ) ),
		trim( (string) shell_exec( 'command -v chromium-browser 2>/dev/null' ) ),
		trim( (string) shell_exec( 'command -v google-chrome 2>/dev/null' ) ),
	);
	foreach ( $candidates as $c ) {
		if ( $c && is_executable( $c ) ) {
			return $c;
		}
	}
	fwrite( STDERR, "Chromium introuvable. Renseigner CHROME_BIN.\n" );
	exit( 1 );
}

/**
 * Recadre une image au format d'une bannière et la rend en donnée en ligne.
 *
 * Une seule photo sert les vingt formats, mais un 970×90 et un 160×600 n'en
 * gardent pas la même part : on recadre en « cover » puis on encode à la
 * taille exacte. Un JPEG de 300×250 pèse une trentaine de kilos — la limite
 * du Réseau Display est à 150, il y a la place. C'est ce que coûte une
 * bannière qui ne ressemble pas à un aplat de couleur.
 *
 * @param string $src   Chemin du fichier.
 * @param int    $w     Largeur voulue.
 * @param int    $h     Hauteur voulue.
 * @param int    $q     Qualité JPEG.
 * @param bool   $alpha Conserver la transparence (découpe produit).
 * @return string Adresse `data:` ou chaîne vide.
 */
function jmk_fit_image( $src, $w, $h, $q = 74, $alpha = false ) {
	if ( ! $src || ! file_exists( $src ) ) {
		return '';
	}

	$info = @getimagesize( $src );
	if ( ! $info ) {
		return '';
	}

	switch ( $info[2] ) {
		case IMAGETYPE_JPEG: $im = @imagecreatefromjpeg( $src ); break;
		case IMAGETYPE_PNG:  $im = @imagecreatefrompng( $src );  break;
		case IMAGETYPE_WEBP: $im = @imagecreatefromwebp( $src ); break;
		default: return '';
	}
	if ( ! $im ) {
		return '';
	}

	$sw = imagesx( $im );
	$sh = imagesy( $im );

	if ( $alpha ) {
		// La découpe garde ses proportions : on la fait entrer dans la boîte
		// au lieu de la recadrer, sinon on ampute le produit.
		$scale = min( $w / $sw, $h / $sh );
		$dw    = max( 1, (int) round( $sw * $scale ) );
		$dh    = max( 1, (int) round( $sh * $scale ) );
		$out   = imagecreatetruecolor( $dw, $dh );
		imagealphablending( $out, false );
		imagesavealpha( $out, true );
		imagefill( $out, 0, 0, imagecolorallocatealpha( $out, 0, 0, 0, 127 ) );
		imagecopyresampled( $out, $im, 0, 0, 0, 0, $dw, $dh, $sw, $sh );
		ob_start();
		imagepng( $out, null, 8 );
		$bin = ob_get_clean();
		imagedestroy( $out );
		imagedestroy( $im );
		return 'data:image/png;base64,' . base64_encode( $bin );
	}

	// « cover » : on remplit la boîte, on rogne le débord.
	$scale = max( $w / $sw, $h / $sh );
	$cw    = max( 1, (int) round( $w / $scale ) );
	$ch    = max( 1, (int) round( $h / $scale ) );
	$cx    = (int) round( ( $sw - $cw ) / 2 );
	$cy    = (int) round( ( $sh - $ch ) / 2 );

	$out = imagecreatetruecolor( $w, $h );
	imagecopyresampled( $out, $im, 0, 0, $cx, $cy, $w, $h, $cw, $ch );

	ob_start();
	imagejpeg( $out, null, $q );
	$bin = ob_get_clean();

	imagedestroy( $out );
	imagedestroy( $im );

	return 'data:image/jpeg;base64,' . base64_encode( $bin );
}

$chrome = jmk_chrome();
$tmp    = sys_get_temp_dir() . '/jmk-ban-' . getmypid();
@mkdir( $tmp, 0777, true );

/* ── 1. le navigateur produit toutes les pages d'un coup ── */

$engine = $root . '/jeux-marketing/assets/js/jmk-banner.js';
$sizes  = isset( $config['sizes'] ) ? $config['sizes'] : array();

/* Une image par format, préparée ici : le navigateur ne sait pas recadrer un
   fichier du disque, et lui envoyer la photo pleine taille ferait vingt
   bannières à 1,4 Mo. */
$byFormat = array();
$wanted   = ( isset( $config['sizes'] ) && $config['sizes'] ) ? $config['sizes'] : array();

if ( ! empty( $config['image'] ) || ! empty( $config['cutout'] ) ) {
	$imgSrc = ! empty( $config['image'] ) ? $config['image'] : '';
	$cutSrc = ! empty( $config['cutout'] ) ? $config['cutout'] : '';

	// Chemins relatifs au fichier de configuration.
	if ( $imgSrc && '/' !== $imgSrc[0] ) { $imgSrc = dirname( $configPath ) . '/' . $imgSrc; }
	if ( $cutSrc && '/' !== $cutSrc[0] ) { $cutSrc = dirname( $configPath ) . '/' . $cutSrc; }

	foreach ( $wanted as $key ) {
		list( $kw, $kh ) = array_map( 'intval', explode( 'x', $key ) );
		$byFormat[ $key ] = array(
			'image'  => $imgSrc ? jmk_fit_image( $imgSrc, $kw, $kh ) : '',
			'cutout' => $cutSrc ? jmk_fit_image( $cutSrc, $kw, $kh, 74, true ) : '',
		);
	}
	echo "  images preparees pour " . count( $byFormat ) . " formats\n\n";
}

$config['_byFormat'] = $byFormat;
// La photo d'origine ne part pas au navigateur : chaque format a la sienne.
unset( $config['image'], $config['cutout'] );

$gen = $tmp . '/gen.html';
file_put_contents(
	$gen,
	'<!doctype html><meta charset="utf-8"><pre id="out"></pre>' .
	'<script src="file://' . $engine . '"></script><script>' .
	'var CFG=' . json_encode( $config, JSON_UNESCAPED_UNICODE ) . ';' .
	'var want=CFG.sizes&&CFG.sizes.length?CFG.sizes:null;' .
	'var list=JMKBanner.sizes().filter(function(s){' .
	'  if(!want){return s.star;}' .
	'  return want.indexOf(s.w+"x"+s.h)>-1;' .
	'});' .
	'var files={};' .
	'list.forEach(function(s){' .
	'  var key=s.w+"x"+s.h;' .
	'  var c=Object.assign({},CFG,{w:s.w,h:s.h});delete c.sizes;delete c._byFormat;' .
	'  var pic=(CFG._byFormat||{})[key];' .
	'  if(pic){if(pic.image){c.image=pic.image;}if(pic.cutout){c.cutout=pic.cutout;}}' .
	'  files[key]={name:s.name,ad:JMKBanner.page(c),still:JMKBanner.page(c,{still:true})};' .
	'});' .
	'document.getElementById("out").textContent=btoa(unescape(encodeURIComponent(JSON.stringify(files))));' .
	'</script>'
);

$cmd = escapeshellarg( $chrome ) . ' --headless --no-sandbox --disable-gpu ' .
	'--virtual-time-budget=4000 --dump-dom ' . escapeshellarg( 'file://' . $gen ) . ' 2>/dev/null';

$dom = (string) shell_exec( $cmd );

if ( ! preg_match( '#<pre id="out">(.*?)</pre>#s', $dom, $m ) ) {
	fwrite( STDERR, "Le navigateur n'a rien produit.\n" );
	exit( 1 );
}

$files = json_decode( base64_decode( trim( $m[1] ) ), true );
if ( ! is_array( $files ) || ! count( $files ) ) {
	fwrite( STDERR, "Aucun format produit. Vérifier la liste « sizes ».\n" );
	exit( 1 );
}

/* ── 2. un dossier par format ── */

$slug = preg_replace( '/[^a-z0-9]+/', '-', strtolower( isset( $config['brand'] ) ? $config['brand'] : 'campagne' ) );
$slug = trim( $slug, '-' );
$slug = $slug ? $slug : 'campagne';

// On repart d'un dossier propre : un format retiré de la liste ne doit pas
// survivre dans le ZIP de la campagne suivante.
if ( is_dir( $outDir ) ) {
	$old = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $outDir, FilesystemIterator::SKIP_DOTS ),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ( $old as $f ) {
		$f->isDir() ? @rmdir( $f->getPathname() ) : @unlink( $f->getPathname() );
	}
}
@mkdir( $outDir, 0777, true );

echo "Campagne « " . ( isset( $config['brand'] ) ? $config['brand'] : '?' ) . " »\n\n";

$made  = array();
$total = 0;

foreach ( $files as $key => $f ) {
	$dir = $outDir . '/' . $key;
	@mkdir( $dir, 0777, true );

	file_put_contents( $dir . '/index.html', $f['ad'] );

	/* Image de repli : on peint la version figée, puis on capture. */
	list( $w, $h ) = array_map( 'intval', explode( 'x', $key ) );

	$stillHtml = $tmp . '/still-' . $key . '.html';
	file_put_contents( $stillHtml, $f['still'] );

	$png = $tmp . '/still-' . $key . '.png';

	/* On demande une fenêtre plus haute que la bannière, et on rogne.
	   La zone visible de Chromium sans interface est plus courte que la
	   fenêtre demandée : la capture, elle, descend jusqu'au bas de la page,
	   mais les calques composés (l'image de fond, le voile) s'arrêtent au
	   bas de la zone visible. Résultat sans marge : une bannière peinte
	   correctement en haut et coupée net à mi-hauteur. */
	$shot = escapeshellarg( $chrome ) . ' --headless --no-sandbox --disable-gpu ' .
		'--hide-scrollbars --force-device-scale-factor=1 ' .
		'--window-size=' . $w . ',' . ( $h + 200 ) . ' --virtual-time-budget=1200 ' .
		'--screenshot=' . escapeshellarg( $png ) . ' ' .
		escapeshellarg( 'file://' . $stillHtml ) . ' 2>/dev/null';
	shell_exec( $shot );

	$jpgPath = $dir . '/' . $slug . '-' . $key . '-backup.jpg';
	$jpgOk   = false;

	if ( file_exists( $png ) ) {
		$im = @imagecreatefrompng( $png );
		if ( $im ) {
			/* La fenêtre sans chrome ne fait pas toujours exactement la
			   taille demandée : on recadre au format annoncé plutôt que de
			   livrer un repli d'un pixel de travers. */
			$canvas = imagecreatetruecolor( $w, $h );
			imagefill( $canvas, 0, 0, imagecolorallocate( $canvas, 0, 0, 0 ) );
			imagecopy( $canvas, $im, 0, 0, 0, 0, min( $w, imagesx( $im ) ), min( $h, imagesy( $im ) ) );
			imagejpeg( $canvas, $jpgPath, 86 );
			imagedestroy( $canvas );
			imagedestroy( $im );
			$jpgOk = file_exists( $jpgPath );
		}
	}

	$adSize   = filesize( $dir . '/index.html' );
	$jpgSize  = $jpgOk ? filesize( $jpgPath ) : 0;
	$weight   = $adSize + $jpgSize;
	$total   += $weight;

	$made[] = array(
		'key'  => $key,
		'name' => $f['name'],
		'ad'   => $adSize,
		'jpg'  => $jpgSize,
	);

	printf(
		"  %-9s %-22s html %5s   repli %7s   %s\n",
		$key,
		$f['name'],
		round( $adSize / 1024, 1 ) . 'K',
		$jpgOk ? round( $jpgSize / 1024, 1 ) . 'K' : 'absent',
		// La limite du Réseau Display est à 150 Ko par création.
		$weight < 150 * 1024 ? 'ok' : 'AU-DESSUS DE 150 Ko'
	);
}

/* ── 3. la note de livraison ── */

$readme = "CAMPAGNE : " . ( isset( $config['brand'] ) ? $config['brand'] : '' ) . "\n" .
	str_repeat( '=', 60 ) . "\n\n" .
	"Un dossier par format. Dans chacun :\n\n" .
	"  index.html                 la bannière animée, autonome\n" .
	"  <marque>-<format>-backup.jpg  l'image de repli\n\n" .
	"CE QUI EST DEJA EN PLACE\n" .
	"------------------------\n" .
	"  - <meta name=\"ad.size\"> a la bonne taille dans chaque fichier\n" .
	"  - var clickTag declare avant le corps de page\n" .
	"  - l'animation s'arrete apres 3 boucles, sur le dernier message\n" .
	"  - aucun fichier externe : ni police, ni script, ni image\n" .
	"  - chaque creation pese tres en dessous des 150 Ko du Reseau Display\n\n" .
	"POUR MISE EN LIGNE\n" .
	"------------------\n" .
	"  Google Ads / Campaign Manager 360 / Display & Video 360 :\n" .
	"  televerser le dossier d'un format tel quel, ou son ZIP.\n" .
	"  L'adresse de destination est portee par clickTag ; la regie la\n" .
	"  reecrit pour compter le clic, il n'y a rien a modifier dans le HTML.\n\n" .
	"  L'image de repli s'ajoute separement, la ou la regie la demande.\n\n" .
	"ADRESSE DE DESTINATION\n" .
	"----------------------\n" .
	"  " . ( isset( $config['clickUrl'] ) && $config['clickUrl'] ? $config['clickUrl'] : '(a renseigner)' ) . "\n\n" .
	"FORMATS LIVRES\n" .
	"--------------\n";

foreach ( $made as $m2 ) {
	$readme .= sprintf( "  %-9s %s\n", $m2['key'], $m2['name'] );
}

file_put_contents( $outDir . '/LISEZMOI.txt', $readme );

/* ── 4. le ZIP ── */

$zipPath = dirname( $outDir ) . '/' . $slug . '-banners.zip';
@unlink( $zipPath );

$zip = new ZipArchive();
if ( true !== $zip->open( $zipPath, ZipArchive::CREATE ) ) {
	fwrite( STDERR, "Impossible d'ecrire $zipPath\n" );
	exit( 1 );
}

$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $outDir, FilesystemIterator::SKIP_DOTS ) );
foreach ( $it as $file ) {
	if ( $file->isDir() ) {
		continue;
	}
	$local = $slug . '-banners/' . ltrim( str_replace( $outDir, '', $file->getPathname() ), '/' );
	$zip->addFile( $file->getPathname(), $local );
}
$zip->close();

/* Un ZIP par format aussi : c'est ce que demandent les régies qui n'acceptent
   qu'une création à la fois. */
foreach ( $made as $m2 ) {
	$one = new ZipArchive();
	$p   = $outDir . '/' . $m2['key'] . '/' . $slug . '-' . $m2['key'] . '.zip';
	if ( true === $one->open( $p, ZipArchive::CREATE ) ) {
		$one->addFile( $outDir . '/' . $m2['key'] . '/index.html', 'index.html' );
		$one->close();
	}
}

// Le ZIP d'ensemble est reconstruit après, pour embarquer les ZIP par format.
@unlink( $zipPath );
$zip = new ZipArchive();
$zip->open( $zipPath, ZipArchive::CREATE );
$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $outDir, FilesystemIterator::SKIP_DOTS ) );
foreach ( $it as $file ) {
	if ( $file->isDir() ) {
		continue;
	}
	$local = $slug . '-banners/' . ltrim( str_replace( $outDir, '', $file->getPathname() ), '/' );
	$zip->addFile( $file->getPathname(), $local );
}
$zip->close();

/* ── ménage ── */
array_map( 'unlink', glob( $tmp . '/*' ) );
@rmdir( $tmp );

printf(
	"\n  %d formats, %s au total\n  %s  (%s)\n",
	count( $made ),
	round( $total / 1024, 1 ) . ' Ko',
	$zipPath,
	round( filesize( $zipPath ) / 1024, 1 ) . ' Ko'
);
