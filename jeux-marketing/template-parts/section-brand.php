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
			<p class="eyebrow"><?php jmk_e( 'brand_eyebrow' ); ?></p>
			<h2><?php jmk_e( 'brand_h2_a' ); ?>
				<span class="accent-text"><?php jmk_e( 'brand_h2_b' ); ?></span></h2>
			<p class="lede"><?php jmk_e( 'brand_lede' ); ?></p>
		</div>
		<div class="panel rv">
			<div class="custom-grid">
				<div>
					<label for="bName"><?php jmk_e( 'your_brand_name' ); ?></label>
					<input type="text" id="bName" maxlength="26" placeholder="<?php echo esc_attr( jmk_t( 'brand_name_ph' ) ); ?>">
				</div>
				<div>
					<label for="bColor"><?php jmk_e( 'main_color' ); ?></label>
					<div class="row-inline">
						<input type="color" id="bColor" value="<?php echo esc_attr( jmk_get( 'accent' ) ); ?>">
						<span class="mono small" id="bHex"><?php echo esc_html( strtoupper( jmk_get( 'accent' ) ) ); ?></span>
					</div>
				</div>
				<div>
					<label><?php jmk_e( 'ready_palettes' ); ?></label>
					<div class="swatches" id="swatches"></div>
				</div>
				<div>
					<label for="bLang"><?php jmk_e( 'game_language' ); ?></label>
					<select id="bLang">
						<option value="fr">Français</option>
						<option value="en">English</option>
						<option value="ar">العربية (RTL)</option>
					</select>
				</div>
			</div>
		</div>
	</div>
</section>
<div class="wrap"><div class="divider"></div></div>
