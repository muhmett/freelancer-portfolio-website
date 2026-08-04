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
		<label for="fname"><?php jmk_e( 'first_name' ); ?></label>
		<input type="text" id="fname" autocomplete="given-name" placeholder="<?php echo esc_attr( jmk_t( 'name_ph' ) ); ?>">
	</div>
	<div class="field">
		<label for="femail"><?php jmk_e( 'email' ); ?></label>
		<input type="email" id="femail" autocomplete="email" placeholder="camille@exemple.fr">
	</div>
	<div class="consent">
		<input type="checkbox" id="fconsent">
		<label for="fconsent" class="consent-label">
			<?php jmk_e( 'consent_text' ); ?>
			<?php if ( $privacy ) : ?>
				<a href="<?php echo esc_url( $privacy ); ?>" target="_blank" rel="noopener"><?php jmk_e( 'privacy' ); ?></a>.
			<?php endif; ?>
			<?php jmk_e( 'consent_never' ); ?>
		</label>
	</div>
	<button class="btn" id="sendLead"><?php jmk_e( 'get_code' ); ?></button>
	<p class="err" id="leadErr" role="alert"></p>
</div>
