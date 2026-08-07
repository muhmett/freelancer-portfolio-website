<?php
/**
 * Accueil : le carrefour.
 *
 * Elle ne vend pas, elle oriente. Deux savoir-faire, deux pages. Tout ce qui
 * s'empilait ici — jeux, devis, calculateur, bannières — vit désormais sur la
 * page de son métier.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="wrap hub-hero">
	<p class="eyebrow rv"><?php echo esc_html( jmk_get( 'hero_eyebrow' ) ); ?></p>
	<h1 class="rv"><?php echo esc_html( jmk_get( 'hero_title' ) ); ?>
		<span class="accent-text"><?php echo esc_html( jmk_get( 'hero_title_2' ) ); ?></span></h1>
	<p class="lede rv"><?php echo esc_html( jmk_get( 'hero_text' ) ); ?></p>

	<?php
	$jmk_chips = array_filter( array_map( 'trim', explode( "\n", (string) jmk_get( 'chips' ) ) ) );
	if ( $jmk_chips ) :
		?>
		<div class="metaline rv">
			<?php foreach ( array_slice( $jmk_chips, 0, 4 ) as $jmk_c ) : ?>
				<span class="chip"><?php echo esc_html( $jmk_c ); ?></span>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>

<?php get_template_part( 'template-parts/section', 'hub' ); ?>

<?php
if ( jmk_get( 'sec_skills' ) ) {
	get_template_part( 'template-parts/section', 'skills' );
}
if ( jmk_get( 'sec_about' ) ) {
	get_template_part( 'template-parts/section', 'about' );
}
if ( jmk_get( 'sec_process' ) ) {
	get_template_part( 'template-parts/section', 'process' );
}
if ( jmk_get( 'sec_reviews' ) ) {
	get_template_part( 'template-parts/section', 'reviews' );
}
get_template_part( 'template-parts/section', 'cta' );

get_footer();
