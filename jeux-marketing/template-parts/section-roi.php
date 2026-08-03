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
			<p class="eyebrow"><?php esc_html_e( 'Avant de commander', 'jeux-marketing' ); ?></p>
			<h2><?php esc_html_e( 'Combien ça rapporte, concrètement ?', 'jeux-marketing' ); ?></h2>
			<p class="lede"><?php esc_html_e( 'Ajustez les chiffres avec les vôtres. Les valeurs par défaut sont des moyennes observées sur ce type de campagne, pas des promesses.', 'jeux-marketing' ); ?></p>
		</div>
		<div class="panel rv">
			<div class="calc-grid">
				<div>
					<label for="cVisit"><?php esc_html_e( 'Visiteurs par mois', 'jeux-marketing' ); ?></label>
					<input type="number" id="cVisit" min="0" step="500" value="<?php echo esc_attr( jmk_get( 'roi_visitors' ) ); ?>">
				</div>
				<div>
					<label for="cPart"><?php esc_html_e( 'Taux de participation (%)', 'jeux-marketing' ); ?></label>
					<input type="number" id="cPart" min="0" max="100" step="1" value="<?php echo esc_attr( jmk_get( 'roi_part' ) ); ?>">
				</div>
				<div>
					<label for="cConv"><?php esc_html_e( 'Email vers client (%)', 'jeux-marketing' ); ?></label>
					<input type="number" id="cConv" min="0" max="100" step="0.5" value="<?php echo esc_attr( jmk_get( 'roi_conv' ) ); ?>">
				</div>
				<div>
					<label for="cCart"><?php esc_html_e( 'Panier moyen', 'jeux-marketing' ); ?></label>
					<input type="number" id="cCart" min="0" step="5" value="<?php echo esc_attr( jmk_get( 'roi_cart' ) ); ?>">
				</div>
			</div>
			<div class="kpis">
				<div class="kpi"><div class="n" id="kLeads">0</div><div class="l"><?php esc_html_e( 'emails collectés / mois', 'jeux-marketing' ); ?></div></div>
				<div class="kpi"><div class="n" id="kSales">0</div><div class="l"><?php esc_html_e( 'ventes attribuées / mois', 'jeux-marketing' ); ?></div></div>
				<div class="kpi"><div class="n" id="kRev">0</div><div class="l"><?php esc_html_e( 'chiffre d\'affaires / mois', 'jeux-marketing' ); ?></div></div>
				<div class="kpi"><div class="n" id="kPay">&mdash;</div><div class="l"><?php esc_html_e( 'amortissement du jeu', 'jeux-marketing' ); ?></div></div>
			</div>
			<p class="small mt-16"><?php esc_html_e( 'Amortissement calculé sur le montant du devis. Un jeu se paie une fois, la liste d\'emails reste à vous.', 'jeux-marketing' ); ?></p>
		</div>
	</div>
</section>
<div class="wrap"><div class="divider"></div></div>
