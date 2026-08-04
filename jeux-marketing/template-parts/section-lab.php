<?php
/**
 * Réglage des probabilités et simulation.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section id="labo">
	<div class="wrap">
		<div class="sec-head rv">
			<p class="eyebrow"><?php jmk_e( 'lab_eyebrow' ); ?></p>
			<h2><?php jmk_e( 'lab_h2' ); ?></h2>
			<p class="lede"><?php jmk_e( 'lab_lede' ); ?></p>
		</div>
		<div class="two rv">
			<div class="panel">
				<div class="panel-title">
					<h3><?php jmk_e( 'lots_setting' ); ?></h3>
					<span class="lot-sub">config.json</span>
				</div>
				<div id="lotList"></div>
				<p class="small mt-16"><?php jmk_e( 'lab_note' ); ?></p>
			</div>
			<div class="panel">
				<div class="panel-title">
					<h3><?php jmk_e( 'verification' ); ?></h3>
					<button class="btn btn-ghost" id="simBtn"><?php jmk_e( 'simulate' ); ?></button>
				</div>
				<div id="simResults">
					<p class="sheet-empty"><?php jmk_e( 'sim_empty' ); ?></p>
				</div>
				<div class="legend">
					<span><i></i> <?php jmk_e( 'legend_obtained' ); ?></span>
					<span><i class="tk"></i> <?php jmk_e( 'legend_config' ); ?></span>
				</div>
			</div>
		</div>
		<div class="panel rv mt-24" id="capPanel" hidden>
			<div class="panel-title">
				<h3><?php jmk_e( 'cap_title' ); ?></h3>
				<span class="lot-sub"><?php jmk_e( 'cap_sub' ); ?></span>
			</div>
			<p class="small mb-16"><?php jmk_e( 'cap_text' ); ?></p>
			<div id="capRows"></div>
		</div>
	</div>
</section>
<div class="wrap"><div class="divider"></div></div>
