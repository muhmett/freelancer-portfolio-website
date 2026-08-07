<?php
/**
 * Template Name: Jeux marketing
 *
 * La page du premier savoir-faire : les six jeux, les lots, la capture, le
 * devis. Ce qui tenait la moitié de l'ancienne page d'accueil.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="wrap page-head">
	<p class="eyebrow rv"><?php jmk_e( 'page_games_eyebrow' ); ?></p>
	<h1 class="rv"><?php jmk_e( 'nav_games' ); ?></h1>
	<p class="lede rv"><?php jmk_e( 'page_games_lede' ); ?></p>
</div>

<div class="wrap" id="roue">
	<div class="hero-grid">
		<div class="rv"><?php get_template_part( 'template-parts/section', 'brand' ); ?></div>
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
get_template_part( 'template-parts/section', 'games' );
if ( jmk_get( 'sec_lab' ) ) {
	get_template_part( 'template-parts/section', 'lab' );
}
if ( jmk_get( 'sec_leads' ) ) {
	get_template_part( 'template-parts/section', 'leads' );
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
if ( jmk_get( 'sec_faq' ) ) {
	get_template_part( 'template-parts/section', 'faq' );
}
get_template_part( 'template-parts/section', 'cta' );

get_footer();
