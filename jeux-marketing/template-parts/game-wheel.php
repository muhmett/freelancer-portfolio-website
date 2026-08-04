<?php
/**
 * Roue de la fortune.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="stage" id="jmk-wheel-stage">
	<div class="wheel-holder">
		<div class="glowring" id="glow" aria-hidden="true"></div>
		<div class="needle" id="needle" aria-hidden="true"></div>
		<canvas id="wheel" width="840" height="840" role="img"
			aria-label="<?php echo esc_attr( jmk_t( 'wheel_aria' ) ); ?>"></canvas>
		<div class="hub" aria-hidden="true" id="hubText"><?php jmk_e( 'js_spin' ); ?></div>
	</div>
	<button class="btn" id="spinBtn"><?php jmk_e( 'spin_btn' ); ?></button>
	<div class="result" id="wheelResult" role="status" aria-live="polite"></div>
	<button class="btn btn-ghost" id="resetPlay" hidden><?php jmk_e( 'replay' ); ?></button>
</div>
