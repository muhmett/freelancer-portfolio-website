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
			aria-label="<?php esc_attr_e( 'Roue de la fortune', 'jeux-marketing' ); ?>"></canvas>
		<div class="hub" aria-hidden="true" id="hubText"><?php esc_html_e( 'TOURNEZ', 'jeux-marketing' ); ?></div>
	</div>
	<button class="btn" id="spinBtn"><?php esc_html_e( 'Lancer la roue', 'jeux-marketing' ); ?></button>
	<div class="result" id="wheelResult" role="status" aria-live="polite"></div>
	<button class="btn btn-ghost" id="resetPlay" hidden><?php esc_html_e( 'Rejouer', 'jeux-marketing' ); ?></button>
</div>
