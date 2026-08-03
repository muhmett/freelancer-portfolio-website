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
	<h3><?php esc_html_e( 'Machine à sous', 'jeux-marketing' ); ?>
		<span class="tag"><?php esc_html_e( '3 rouleaux', 'jeux-marketing' ); ?></span></h3>
	<div class="reels" id="reels" role="img"
		aria-label="<?php esc_attr_e( 'Trois rouleaux de machine à sous', 'jeux-marketing' ); ?>">
		<div class="reel"><div class="strip"></div></div>
		<div class="reel"><div class="strip"></div></div>
		<div class="reel"><div class="strip"></div></div>
	</div>
	<div class="row-inline">
		<button class="btn btn-ghost" id="slotBtn"><?php esc_html_e( 'Lancer', 'jeux-marketing' ); ?></button>
		<span class="small" id="slotMsg" role="status" aria-live="polite"><?php
			esc_html_e( 'Trois symboles identiques et le lot est à vous.', 'jeux-marketing' );
		?></span>
	</div>
</div>
