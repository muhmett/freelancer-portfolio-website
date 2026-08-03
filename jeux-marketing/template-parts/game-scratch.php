<?php
/**
 * Carte à gratter.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="game-card rv" id="jmk-scratch">
	<h3><?php esc_html_e( 'Carte à gratter', 'jeux-marketing' ); ?>
		<span class="tag"><?php esc_html_e( 'grattez', 'jeux-marketing' ); ?></span></h3>
	<div class="scratch-holder">
		<div class="scratch-under">
			<div>
				<span class="lot-sub"><?php esc_html_e( 'votre lot', 'jeux-marketing' ); ?></span>
				<strong id="scratchPrize">&mdash;</strong>
			</div>
		</div>
		<canvas id="scratchCv"></canvas>
	</div>
	<div class="row-inline">
		<button class="btn btn-ghost" id="scratchReset"><?php esc_html_e( 'Nouvelle carte', 'jeux-marketing' ); ?></button>
		<span class="small mono" id="scratchPct">0 %</span>
	</div>
</div>
