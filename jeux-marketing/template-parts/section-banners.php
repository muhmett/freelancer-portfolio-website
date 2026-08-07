<?php
/**
 * Bannières d'affichage.
 *
 * Même parti pris que les jeux : ce n'est pas une capture d'écran, ce sont
 * les vraies bannières, animées, dans la page. Le visiteur voit qu'une seule
 * création remplit tous les formats — c'est l'argument, et il ne se raconte
 * pas, il se montre.
 *
 * Les formats gardent leur taille réelle en pixels : un 728×90 mesure 728×90.
 * Sur un écran étroit ils sont mis à l'échelle par transform, jamais
 * redimensionnés — une bannière déformée ne prouve plus rien.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$jmk_sizes = jmk_banner_sizes();
if ( ! $jmk_sizes ) {
	return;
}
?>
<section id="bannieres">
	<div class="wrap">
		<div class="sec-head rv">
			<p class="eyebrow"><?php jmk_e( 'banners_eyebrow' ); ?></p>
			<h2><?php jmk_e( 'banners_h2' ); ?></h2>
			<p class="lede"><?php jmk_e( 'banners_lede' ); ?></p>
		</div>

		<div class="banner-note rv">
			<span class="chip"><?php jmk_e( 'banners_chip_sizes' ); ?></span>
			<span class="chip"><?php jmk_e( 'banners_chip_weight' ); ?></span>
			<span class="chip"><?php jmk_e( 'banners_chip_specs' ); ?></span>
		</div>

		<div class="banner-wall rv" id="bannerWall">
			<?php foreach ( $jmk_sizes as $jmk_s ) : ?>
				<figure class="banner-slot" data-w="<?php echo (int) $jmk_s['w']; ?>" data-h="<?php echo (int) $jmk_s['h']; ?>">
					<figcaption>
						<?php /* En arabe, le « × » est un caractère neutre entre deux nombres :
						   l'algorithme bidirectionnel place le second à gauche et « 300×250 »
						   se lit « 250×300 ». Une dimension n'est pas du texte, elle ne se
						   retourne pas — on l'isole. */ ?>
						<span class="banner-dim" dir="ltr"><?php echo (int) $jmk_s['w']; ?>×<?php echo (int) $jmk_s['h']; ?></span>
						<span class="banner-name"><?php echo esc_html( $jmk_s['name'] ); ?></span>
					</figcaption>
					<div class="banner-stage" style="width:<?php echo (int) $jmk_s['w']; ?>px"><div class="banner-mount"></div></div>
				</figure>
			<?php endforeach; ?>
		</div>

		<p class="banner-foot rv"><?php jmk_e( 'banners_foot' ); ?></p>
	</div>
</section>
<div class="wrap"><div class="divider"></div></div>
