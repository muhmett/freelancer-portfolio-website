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
	<h3><?php jmk_e( 'plinko_title' ); ?>
		<span class="tag"><?php jmk_e( 'plinko_tag' ); ?></span></h3>
	<div class="plinko-holder">
		<canvas id="plinkoCv" role="img"
			aria-label="<?php echo esc_attr( jmk_t( 'plinko_aria' ) ); ?>"></canvas>
	</div>
	<div class="row-inline">
		<button class="btn btn-ghost" id="plinkoBtn"><?php jmk_e( 'plinko_btn' ); ?></button>
		<span class="small" id="plinkoMsg" role="status" aria-live="polite"><?php
			jmk_e( 'plinko_msg' );
		?></span>
	</div>
</div>
