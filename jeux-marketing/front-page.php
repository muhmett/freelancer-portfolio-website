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

// Les fonds tournent derrière le texte, pas dedans : quatre humeurs
// générées par bin/make-hero-bg.php (voir ce fichier), sans banque
// d'images ni licence à suivre. L'ordre ici règle l'ordre de rotation.
$jmk_hero_bgs = array( 'aurore', 'confetti', 'rayons', 'grille' );
?>

<section class="hero-stage">
	<div class="hero-carousel" id="heroBg" aria-hidden="true">
		<?php foreach ( $jmk_hero_bgs as $jmk_i => $jmk_bg ) :
			$jmk_webp = 'assets/img/hero/' . $jmk_bg . '.webp';
			$jmk_jpg  = 'assets/img/hero/' . $jmk_bg . '.jpg';
			?>
			<div class="hero-bg-slide<?php echo ( 0 === $jmk_i ) ? ' is-active' : ''; ?>"
				style="background-image:image-set(
					url(<?php echo esc_url( JMK_URI . '/' . $jmk_webp . '?v=' . jmk_asset_version( $jmk_webp ) ); ?>) type('image/webp'),
					url(<?php echo esc_url( JMK_URI . '/' . $jmk_jpg . '?v=' . jmk_asset_version( $jmk_jpg ) ); ?>) type('image/jpeg')
				)"></div>
		<?php endforeach; ?>
		<div class="hero-carousel-veil"></div>
	</div>

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

		<?php if ( count( $jmk_hero_bgs ) > 1 ) : ?>
			<div class="hero-dots rv" id="heroDots" role="group" aria-label="<?php echo esc_attr( jmk_t( 'hero_bg_group' ) ); ?>">
				<?php foreach ( $jmk_hero_bgs as $jmk_i => $jmk_bg ) : ?>
					<button type="button" class="carousel-dot<?php echo ( 0 === $jmk_i ) ? ' active' : ''; ?>"
						data-i="<?php echo (int) $jmk_i; ?>"
						aria-label="<?php echo esc_attr( sprintf( jmk_t( 'hero_bg_dot' ), $jmk_i + 1 ) ); ?>"></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

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
