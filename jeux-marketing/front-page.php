<?php
/**
 * Page d'accueil.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<!-- HERO -->
<div class="wrap hero" id="roue">
	<div class="hero-grid">
		<div class="rv">
			<p class="eyebrow"><?php echo esc_html( jmk_get( 'hero_eyebrow' ) ); ?></p>
			<h1><?php echo esc_html( jmk_get( 'hero_title' ) ); ?><br>
				<span class="accent-text"><?php echo esc_html( jmk_get( 'hero_title_2' ) ); ?></span></h1>
			<p class="lede"><?php echo esc_html( jmk_get( 'hero_text' ) ); ?></p>

			<?php
			$chips = array_filter( array_map( 'trim', explode( "\n", (string) jmk_get( 'chips' ) ) ) );
			if ( $chips ) :
				?>
				<div class="metaline">
					<?php foreach ( $chips as $c ) : ?>
						<span class="chip"><?php echo esc_html( $c ); ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="hero-cta">
				<?php if ( jmk_get( 'sec_quote' ) ) : ?>
					<button class="btn" data-scroll="devis"><?php jmk_e( 'cta_configure' ); ?></button>
				<?php endif; ?>
				<?php if ( jmk_get( 'sec_roi' ) ) : ?>
					<button class="btn btn-ghost" data-scroll="roi"><?php jmk_e( 'cta_roi' ); ?></button>
				<?php endif; ?>
			</div>
		</div>

		<div class="rv">
			<?php
			if ( jmk_get( 'game_wheel' ) ) {
				get_template_part( 'template-parts/game', 'wheel' );
			}
			?>
		</div>
	</div>
</div>

<div class="wrap"><div class="divider"></div></div>

<?php
if ( jmk_get( 'sec_services' ) ) {
	get_template_part( 'template-parts/section', 'services' );
}
if ( jmk_get( 'sec_brand' ) ) {
	get_template_part( 'template-parts/section', 'brand' );
}
if ( jmk_get( 'sec_lab' ) ) {
	get_template_part( 'template-parts/section', 'lab' );
}
get_template_part( 'template-parts/section', 'games' );
if ( jmk_get( 'sec_leads' ) ) {
	get_template_part( 'template-parts/section', 'leads' );
}
if ( jmk_get( 'sec_work' ) ) {
	get_template_part( 'template-parts/section', 'work' );
}
if ( jmk_get( 'sec_skills' ) ) {
	get_template_part( 'template-parts/section', 'skills' );
}
if ( jmk_get( 'sec_banners' ) ) {
	get_template_part( 'template-parts/section', 'banners' );
}
if ( jmk_get( 'sec_reviews' ) ) {
	get_template_part( 'template-parts/section', 'reviews' );
}
if ( jmk_get( 'sec_roi' ) ) {
	get_template_part( 'template-parts/section', 'roi' );
}
if ( jmk_get( 'sec_quote' ) ) {
	get_template_part( 'template-parts/section', 'quote' );
}
if ( jmk_get( 'sec_specs' ) ) {
	get_template_part( 'template-parts/section', 'specs' );
}
if ( jmk_get( 'sec_process' ) ) {
	get_template_part( 'template-parts/section', 'process' );
}
if ( jmk_get( 'sec_about' ) ) {
	get_template_part( 'template-parts/section', 'about' );
}
if ( jmk_get( 'sec_faq' ) ) {
	get_template_part( 'template-parts/section', 'faq' );
}
get_template_part( 'template-parts/section', 'cta' );

get_footer();
