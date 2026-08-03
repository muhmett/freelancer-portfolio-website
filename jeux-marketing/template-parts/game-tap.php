<?php
/**
 * Tap-to-win.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="game-card rv" id="jmk-tap">
	<h3><?php esc_html_e( 'Tap-to-win', 'jeux-marketing' ); ?>
		<span class="tag"><?php esc_html_e( 'une boîte', 'jeux-marketing' ); ?></span></h3>
	<div class="boxes" id="boxes">
		<button class="box" data-i="0">?</button>
		<button class="box" data-i="1">?</button>
		<button class="box" data-i="2">?</button>
	</div>
	<div class="row-inline">
		<button class="btn btn-ghost" id="tapReset"><?php esc_html_e( 'Rejouer', 'jeux-marketing' ); ?></button>
		<span class="small" id="tapMsg"><?php esc_html_e( 'Une seule tentative par joueur.', 'jeux-marketing' ); ?></span>
	</div>
</div>
