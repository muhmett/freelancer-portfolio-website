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
	<?php
	/* La roue est un empilement, pas une image : la tranche, le plateau qui
	   tourne, le vernis, puis la monture en laiton. Seul le plateau tourne —
	   une monture qui tournerait avec lui ne serait plus une monture. Tout
	   l'empilement s'incline d'un même bloc, sinon les couches se
	   décolleraient les unes des autres. */
	?>
	<div class="wheel-holder">
		<div class="wheel-3d">
			<div class="wheel-edge" aria-hidden="true"></div>
			<div class="glowring" id="glow" aria-hidden="true"></div>
			<canvas id="wheel" width="840" height="840" role="img"
				aria-label="<?php echo esc_attr( jmk_t( 'wheel_aria' ) ); ?>"></canvas>
			<div class="wheel-gloss" aria-hidden="true"></div>
			<div class="wheel-rim" aria-hidden="true"></div>
			<div class="hub" aria-hidden="true" id="hubText"><?php jmk_e( 'js_spin' ); ?></div>
		</div>
		<div class="needle" id="needle" aria-hidden="true"></div>
	</div>
	<button class="btn" id="spinBtn"><?php jmk_e( 'spin_btn' ); ?></button>
	<div class="result" id="wheelResult" role="status" aria-live="polite"></div>
	<button class="btn btn-ghost" id="resetPlay" hidden><?php jmk_e( 'replay' ); ?></button>
</div>
