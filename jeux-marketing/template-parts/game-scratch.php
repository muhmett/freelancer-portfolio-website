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
	<h3><?php jmk_e( 'scratch_title' ); ?>
		<span class="tag"><?php jmk_e( 'scratch_tag' ); ?></span></h3>
	<div class="scratch-3d">
	<div class="scratch-holder">
		<div class="scratch-under">
			<div>
				<span class="lot-sub"><?php jmk_e( 'your_prize' ); ?></span>
				<strong id="scratchPrize">&mdash;</strong>
			</div>
		</div>
		<canvas id="scratchCv"></canvas>
	</div>
	</div>
	<div class="row-inline">
		<button class="btn btn-ghost" id="scratchReset"><?php jmk_e( 'new_card' ); ?></button>
		<span class="small mono" id="scratchPct">0 %</span>
	</div>
</div>
