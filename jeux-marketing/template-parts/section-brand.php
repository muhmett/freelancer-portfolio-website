<?php
/**
 * Aperçu aux couleurs du visiteur.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section id="marque">
	<div class="wrap">
		<div class="sec-head rv">
			<p class="eyebrow"><?php esc_html_e( 'Essayez tout de suite', 'jeux-marketing' ); ?></p>
			<h2><?php esc_html_e( 'Voyez la roue à', 'jeux-marketing' ); ?>
				<span class="accent-text"><?php esc_html_e( 'vos couleurs', 'jeux-marketing' ); ?></span></h2>
			<p class="lede"><?php esc_html_e( 'Entrez le nom de votre marque et choisissez votre couleur. Toute la page se réaccorde en direct — c\'est exactement ce que vous recevrez.', 'jeux-marketing' ); ?></p>
		</div>
		<div class="panel rv">
			<div class="custom-grid">
				<div>
					<label for="bName"><?php esc_html_e( 'Nom de votre marque', 'jeux-marketing' ); ?></label>
					<input type="text" id="bName" maxlength="26" placeholder="<?php esc_attr_e( 'Maison Solène', 'jeux-marketing' ); ?>">
				</div>
				<div>
					<label for="bColor"><?php esc_html_e( 'Couleur principale', 'jeux-marketing' ); ?></label>
					<div class="row-inline">
						<input type="color" id="bColor" value="<?php echo esc_attr( jmk_get( 'accent' ) ); ?>">
						<span class="mono small" id="bHex"><?php echo esc_html( strtoupper( jmk_get( 'accent' ) ) ); ?></span>
					</div>
				</div>
				<div>
					<label><?php esc_html_e( 'Palettes prêtes', 'jeux-marketing' ); ?></label>
					<div class="swatches" id="swatches"></div>
				</div>
				<div>
					<label for="bLang"><?php esc_html_e( 'Langue du jeu', 'jeux-marketing' ); ?></label>
					<select id="bLang">
						<option value="fr"><?php esc_html_e( 'Français', 'jeux-marketing' ); ?></option>
						<option value="en">English</option>
						<option value="ar">العربية (RTL)</option>
					</select>
				</div>
			</div>
		</div>
	</div>
</section>
<div class="wrap"><div class="divider"></div></div>
