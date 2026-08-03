<?php
/**
 * Code court pour insérer un jeu dans n'importe quelle page.
 *
 * Exemples :
 *   [jeu]
 *   [jeu type="roue"]
 *   [jeu type="grattage"]
 *   [jeu type="tap"]
 *   [jeu type="quiz"]
 *   [jeu type="machine"]
 *   [jeu type="plinko"]
 *   [jeu type="roue" formulaire="oui"]
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rendu du code court.
 *
 * @param array $atts Attributs.
 * @return string
 */
function jmk_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'type'       => 'roue',
			'formulaire' => 'oui',
		),
		$atts,
		'jeu'
	);

	$type = sanitize_key( $atts['type'] );
	$form = in_array( strtolower( $atts['formulaire'] ), array( 'oui', 'yes', '1', 'true' ), true );

	ob_start();
	echo '<div class="jmk-embed jmk-embed-' . esc_attr( $type ) . '">';

	switch ( $type ) {
		case 'grattage':
			get_template_part( 'template-parts/game', 'scratch' );
			break;
		case 'tap':
			get_template_part( 'template-parts/game', 'tap' );
			break;
		case 'quiz':
			get_template_part( 'template-parts/game', 'quiz' );
			break;
		case 'machine':
			get_template_part( 'template-parts/game', 'slot' );
			break;
		case 'plinko':
			get_template_part( 'template-parts/game', 'plinko' );
			break;
		default:
			get_template_part( 'template-parts/game', 'wheel' );
			break;
	}

	if ( $form ) {
		get_template_part( 'template-parts/form', 'lead' );
	}

	echo '</div>';
	return ob_get_clean();
}
add_shortcode( 'jeu', 'jmk_shortcode' );
