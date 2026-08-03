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
			<p class="eyebrow"><?php esc_html_e( 'Devis instantané', 'jeux-marketing' ); ?></p>
			<h2><?php esc_html_e( 'Composez votre jeu, obtenez le prix', 'jeux-marketing' ); ?></h2>
			<p class="lede"><?php esc_html_e( 'Cochez ce dont vous avez besoin. Le récapitulatif complet part avec vous — plus besoin de tout réécrire.', 'jeux-marketing' ); ?></p>
		</div>
		<div class="two rv">
			<div class="panel">
				<div class="panel-title">
					<h3><?php esc_html_e( 'Votre configuration', 'jeux-marketing' ); ?></h3>
					<span class="lot-sub"><?php esc_html_e( 'devis', 'jeux-marketing' ); ?></span>
				</div>
				<div class="opts" id="opts"></div>
				<div class="total">
					<span class="small"><?php esc_html_e( 'Total estimé', 'jeux-marketing' ); ?></span>
					<span class="n" id="qTotal">0</span>
				</div>
				<p class="small" id="qDelay"></p>
				<div class="cta-row" id="quoteCta"></div>
				<p class="small mt-14"><?php esc_html_e( 'Prix indicatif hors taxes. Le devis final est confirmé après un échange de deux minutes sur vos lots et votre plateforme.', 'jeux-marketing' ); ?></p>
			</div>
			<div>
				<div class="panel">
					<div class="panel-title">
						<h3><?php esc_html_e( 'Code d\'intégration généré', 'jeux-marketing' ); ?></h3>
						<button class="btn btn-ghost" id="copyCode"><?php esc_html_e( 'Copier', 'jeux-marketing' ); ?></button>
					</div>
					<div class="codebox" id="embedCode"></div>
					<p class="small mt-13"><?php esc_html_e( 'Voilà tout ce que vous aurez à coller sur votre page. Le reste vit dans un fichier de configuration lisible, modifiable après la livraison.', 'jeux-marketing' ); ?></p>
				</div>
			</div>
		</div>
	</div>
</section>
<div class="wrap"><div class="divider"></div></div>
