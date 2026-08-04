<?php
/**
 * Calculateur de rentabilité.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section id="roi">
	<div class="wrap">
		<div class="sec-head rv">
			<p class="eyebrow"><?php jmk_e( 'roi_eyebrow' ); ?></p>
			<h2><?php jmk_e( 'roi_h2' ); ?></h2>
			<p class="lede"><?php jmk_e( 'roi_lede' ); ?></p>
		</div>
		<div class="panel rv">
			<div class="calc-grid">
				<div>
					<label for="cVisit"><?php jmk_e( 'visitors_month' ); ?></label>
					<input type="number" id="cVisit" min="0" step="500" value="<?php echo esc_attr( jmk_get( 'roi_visitors' ) ); ?>">
				</div>
				<div>
					<label for="cPart"><?php jmk_e( 'part_rate' ); ?></label>
					<input type="number" id="cPart" min="0" max="100" step="1" value="<?php echo esc_attr( jmk_get( 'roi_part' ) ); ?>">
				</div>
				<div>
					<label for="cConv"><?php jmk_e( 'email_to_client' ); ?></label>
					<input type="number" id="cConv" min="0" max="100" step="0.5" value="<?php echo esc_attr( jmk_get( 'roi_conv' ) ); ?>">
				</div>
				<div>
					<label for="cCart"><?php jmk_e( 'avg_cart' ); ?></label>
					<input type="number" id="cCart" min="0" step="5" value="<?php echo esc_attr( jmk_get( 'roi_cart' ) ); ?>">
				</div>
			</div>
			<div class="kpis">
				<div class="kpi"><div class="n" id="kLeads">0</div><div class="l"><?php jmk_e( 'k_leads' ); ?></div></div>
				<div class="kpi"><div class="n" id="kSales">0</div><div class="l"><?php jmk_e( 'k_sales' ); ?></div></div>
				<div class="kpi"><div class="n" id="kRev">0</div><div class="l"><?php jmk_e( 'k_rev' ); ?></div></div>
				<div class="kpi"><div class="n" id="kPay">&mdash;</div><div class="l"><?php jmk_e( 'k_pay' ); ?></div></div>
			</div>
			<p class="small mt-16"><?php jmk_e( 'roi_note' ); ?></p>
		</div>
	</div>
</section>
<div class="wrap"><div class="divider"></div></div>
