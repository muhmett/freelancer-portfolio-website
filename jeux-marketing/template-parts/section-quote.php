<?php
/**
 * Devis instantané et code d'intégration.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section id="devis">
	<div class="wrap">
		<div class="sec-head rv">
			<p class="eyebrow"><?php jmk_e( 'quote_eyebrow' ); ?></p>
			<h2><?php jmk_e( 'quote_h2' ); ?></h2>
			<p class="lede"><?php jmk_e( 'quote_lede' ); ?></p>
		</div>
		<div class="two rv">
			<div class="panel">
				<div class="panel-title">
					<h3><?php jmk_e( 'your_config' ); ?></h3>
					<span class="lot-sub"><?php jmk_e( 'quote_word' ); ?></span>
				</div>
				<div class="opts" id="opts"></div>
				<div class="total">
					<span class="small"><?php jmk_e( 'total_est' ); ?></span>
					<span class="n" id="qTotal">0</span>
				</div>
				<p class="small" id="qDelay"></p>
				<div class="cta-row" id="quoteCta"></div>
				<p class="small mt-14"><?php jmk_e( 'quote_note1' ); ?></p>
			</div>
			<div>
				<div class="panel">
					<div class="panel-title">
						<h3><?php jmk_e( 'embed_title' ); ?></h3>
						<button class="btn btn-ghost" id="copyCode"><?php jmk_e( 'copy_short' ); ?></button>
					</div>
					<div class="codebox" id="embedCode"></div>
					<p class="small mt-13"><?php jmk_e( 'quote_note2' ); ?></p>
				</div>
			</div>
		</div>
	</div>
</section>
<div class="wrap"><div class="divider"></div></div>
