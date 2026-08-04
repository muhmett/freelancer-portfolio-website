<?php
/**
 * Machine à sous.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="game-card rv" id="jmk-slot">
	<h3><?php jmk_e( 'slot_title' ); ?>
		<span class="tag"><?php jmk_e( 'slot_tag' ); ?></span></h3>
	<div class="reels" id="reels" role="img"
		aria-label="<?php echo esc_attr( jmk_t( 'slot_aria' ) ); ?>">
		<div class="reel"><div class="strip"></div></div>
		<div class="reel"><div class="strip"></div></div>
		<div class="reel"><div class="strip"></div></div>
	</div>
	<div class="row-inline">
		<button class="btn btn-ghost" id="slotBtn"><?php jmk_e( 'launch' ); ?></button>
		<span class="small" id="slotMsg" role="status" aria-live="polite"><?php
			jmk_e( 'slot_msg' );
		?></span>
	</div>
</div>
