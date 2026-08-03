<?php
/**
 * Pluie de lots — la bille tombe entre les clous et se range dans une case.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="game-card rv" id="jmk-plinko">
	<h3><?php esc_html_e( 'Pluie de lots', 'jeux-marketing' ); ?>
		<span class="tag"><?php esc_html_e( 'la bille décide', 'jeux-marketing' ); ?></span></h3>
	<div class="plinko-holder">
		<canvas id="plinkoCv" role="img"
			aria-label="<?php esc_attr_e( 'Plateau de billes : la bille tombe dans la case du lot gagné.', 'jeux-marketing' ); ?>"></canvas>
	</div>
	<div class="row-inline">
		<button class="btn btn-ghost" id="plinkoBtn"><?php esc_html_e( 'Lâcher la bille', 'jeux-marketing' ); ?></button>
		<span class="small" id="plinkoMsg" role="status" aria-live="polite"><?php
			esc_html_e( 'Chaque case a sa probabilité, comme la roue.', 'jeux-marketing' );
		?></span>
	</div>
</div>
