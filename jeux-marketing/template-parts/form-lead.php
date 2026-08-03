<?php
/**
 * Formulaire de capture d'email.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$privacy = jmk_get( 'privacy_url' );
?>
<div class="panel jmk-leadform">
	<div class="field">
		<label for="fname"><?php esc_html_e( 'Prénom', 'jeux-marketing' ); ?></label>
		<input type="text" id="fname" autocomplete="given-name" placeholder="<?php esc_attr_e( 'Camille', 'jeux-marketing' ); ?>">
	</div>
	<div class="field">
		<label for="femail"><?php esc_html_e( 'Email', 'jeux-marketing' ); ?></label>
		<input type="email" id="femail" autocomplete="email" placeholder="camille@exemple.fr">
	</div>
	<div class="consent">
		<input type="checkbox" id="fconsent">
		<label for="fconsent" class="consent-label">
			<?php esc_html_e( 'J\'accepte de recevoir les offres de la marque.', 'jeux-marketing' ); ?>
			<?php if ( $privacy ) : ?>
				<a href="<?php echo esc_url( $privacy ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Politique de confidentialité', 'jeux-marketing' ); ?></a>.
			<?php endif; ?>
			<?php esc_html_e( 'Consentement obligatoire, jamais pré-coché.', 'jeux-marketing' ); ?>
		</label>
	</div>
	<button class="btn" id="sendLead"><?php esc_html_e( 'Recevoir mon code', 'jeux-marketing' ); ?></button>
	<p class="err" id="leadErr" role="alert"></p>
</div>
