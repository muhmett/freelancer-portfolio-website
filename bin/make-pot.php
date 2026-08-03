<?php
/**
 * Génère languages/jeux-marketing.pot à partir des sources du thème.
 *
 * Lancer avec : php bin/make-pot.php
 *
 * Évite d'avoir à installer WP-CLI pour un thème de cette taille. Les
 * fonctions couvertes sont celles réellement utilisées ici : __, _e,
 * esc_html__, esc_html_e, esc_attr__, esc_attr_e.
 *
 * @package JeuxMarketing
 */

$theme  = dirname( __DIR__ ) . '/jeux-marketing';
$domain = 'jeux-marketing';

$files = array();
$it    = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $theme ) );
foreach ( $it as $f ) {
	if ( 'php' === strtolower( $f->getExtension() ) ) {
		$files[] = $f->getPathname();
	}
}
sort( $files );

// Fonction, guillemet ouvrant, contenu, guillemet fermant, domaine.
$pattern = '/\b(?:esc_html__|esc_html_e|esc_attr__|esc_attr_e|__|_e)\s*\(\s*'
	. '(?:\'((?:[^\'\\\\]|\\\\.)*)\'|"((?:[^"\\\\]|\\\\.)*)")'
	. '\s*,\s*\'' . preg_quote( $domain, '/' ) . '\'\s*\)/';

$strings = array();
foreach ( $files as $file ) {
	$src   = file_get_contents( $file );
	$rel   = 'jeux-marketing/' . ltrim( str_replace( $theme, '', $file ), '/' );
	$lines = explode( "\n", $src );

	foreach ( $lines as $no => $line ) {
		if ( ! preg_match_all( $pattern, $line, $m, PREG_SET_ORDER ) ) {
			continue;
		}
		foreach ( $m as $hit ) {
			$single = ( '' !== $hit[1] || ! isset( $hit[2] ) );
			$raw    = $single ? $hit[1] : $hit[2];

			// Repasse en texte brut, puis en littéral PO.
			$text = $single
				? str_replace( array( "\\'", '\\\\' ), array( "'", '\\' ), $raw )
				: stripcslashes( $raw );

			if ( '' === $text ) {
				continue;
			}
			if ( ! isset( $strings[ $text ] ) ) {
				$strings[ $text ] = array();
			}
			$strings[ $text ][] = $rel . ':' . ( $no + 1 );
		}
	}
}

ksort( $strings );

/**
 * Échappe une chaîne pour un fichier PO.
 *
 * @param string $s Chaîne.
 * @return string
 */
function po_escape( $s ) {
	return str_replace(
		array( '\\', '"', "\n", "\t" ),
		array( '\\\\', '\"', '\n', '\t' ),
		$s
	);
}

$out  = "# Traductions du thème Jeux Marketing.\n";
$out .= "# Ce fichier est généré : php bin/make-pot.php\n";
$out .= "#, fuzzy\n";
$out .= "msgid \"\"\n";
$out .= "msgstr \"\"\n";
$out .= "\"Project-Id-Version: Jeux Marketing 1.0.0\\n\"\n";
$out .= "\"MIME-Version: 1.0\\n\"\n";
$out .= "\"Content-Type: text/plain; charset=UTF-8\\n\"\n";
$out .= "\"Content-Transfer-Encoding: 8bit\\n\"\n";
$out .= "\"Plural-Forms: nplurals=2; plural=(n > 1);\\n\"\n";
$out .= "\"X-Domain: " . $domain . "\\n\"\n";

foreach ( $strings as $text => $refs ) {
	$out .= "\n";
	foreach ( array_chunk( array_unique( $refs ), 4 ) as $chunk ) {
		$out .= '#: ' . implode( ' ', $chunk ) . "\n";
	}
	$out .= 'msgid "' . po_escape( $text ) . "\"\n";
	$out .= "msgstr \"\"\n";
}

$dir = $theme . '/languages';
if ( ! is_dir( $dir ) ) {
	mkdir( $dir, 0755, true );
}
file_put_contents( $dir . '/' . $domain . '.pot', $out );

echo count( $strings ) . " chaînes écrites dans languages/$domain.pot\n";
