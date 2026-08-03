<?php
/**
 * Capture d'email et aperçu du tableau de participants.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section id="leads">
	<div class="wrap">
		<div class="sec-head rv">
			<p class="eyebrow"><?php esc_html_e( 'Ce que le jeu produit vraiment', 'jeux-marketing' ); ?></p>
			<h2><?php esc_html_e( 'Le lot est le prétexte.', 'jeux-marketing' ); ?>
				<span class="accent-text"><?php esc_html_e( 'L\'email est le produit.', 'jeux-marketing' ); ?></span></h2>
			<p class="lede"><?php esc_html_e( 'Remplissez le formulaire : la ligne apparaît immédiatement à droite et le participant est enregistré dans votre administration.', 'jeux-marketing' ); ?></p>
		</div>
		<div class="two rv">
			<?php get_template_part( 'template-parts/form', 'lead' ); ?>
			<div>
				<div class="sheet">
					<div class="sheet-bar"><span class="led"></span>
						<?php esc_html_e( 'participants — synchronisation active', 'jeux-marketing' ); ?></div>
					<div class="tbl-scroll">
						<table>
							<thead>
								<tr>
									<th><?php esc_html_e( 'Horodatage', 'jeux-marketing' ); ?></th>
									<th><?php esc_html_e( 'Prénom', 'jeux-marketing' ); ?></th>
									<th><?php esc_html_e( 'Email', 'jeux-marketing' ); ?></th>
									<th><?php esc_html_e( 'Lot', 'jeux-marketing' ); ?></th>
									<th><?php esc_html_e( 'Code', 'jeux-marketing' ); ?></th>
								</tr>
							</thead>
							<tbody id="sheetBody">
								<tr><td colspan="5" class="sheet-empty"><?php esc_html_e( 'Aucun participant pour l\'instant.', 'jeux-marketing' ); ?></td></tr>
							</tbody>
						</table>
					</div>
				</div>
				<p class="small mt-13"><?php esc_html_e( 'Chaque participation est enregistrée dans WordPress, exportable en CSV, et peut être transmise à Mailchimp, Brevo, Klaviyo ou votre CRM par webhook.', 'jeux-marketing' ); ?></p>
			</div>
		</div>
	</div>
</section>
<div class="wrap"><div class="divider"></div></div>
