<?php
/**
 * Portfolio de bannières.
 *
 * Quatre campagnes, quatre secteurs, quatre partis pris graphiques. Chacune
 * se montre dans plusieurs formats, animée, à sa taille réelle en pixels.
 *
 * Le mot « démonstration » n'est pas en petit en bas : il est dans la section,
 * lisible. Une marque inventée présentée comme un client est un faux
 * témoignage, et le prospect qui demande à parler à la référence met le
 * vendeur en difficulté.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$jmk_camps = jmk_banner_work();
$jmk_fmts  = jmk_banner_work_sizes();
if ( ! $jmk_camps || ! $jmk_fmts ) {
	return;
}
?>
<section id="bannieres-work">
	<div class="wrap">
		<div class="sec-head rv">
			<p class="eyebrow"><?php jmk_e( 'bwork_eyebrow' ); ?></p>
			<h2><?php jmk_e( 'bwork_h2' ); ?></h2>
			<p class="lede"><?php jmk_e( 'bwork_lede' ); ?></p>
		</div>

		<p class="bwork-flag rv"><?php jmk_e( 'bwork_demo' ); ?></p>

		<div class="bwork-tabs rv" role="tablist" id="bworkTabs">
			<?php foreach ( $jmk_camps as $jmk_i => $jmk_c ) : ?>
				<button type="button"
					class="bwork-tab<?php echo 0 === $jmk_i ? ' active' : ''; ?>"
					role="tab"
					aria-selected="<?php echo 0 === $jmk_i ? 'true' : 'false'; ?>"
					data-i="<?php echo (int) $jmk_i; ?>"
					style="--c:<?php echo esc_attr( $jmk_c['accent'] ); ?>">
					<span class="bwork-tab-brand"><?php echo esc_html( $jmk_c['brand'] ); ?></span>
					<span class="bwork-tab-niche"><?php echo esc_html( $jmk_c['niche'] ); ?></span>
				</button>
			<?php endforeach; ?>
		</div>

		<p class="bwork-note rv" id="bworkNote"><?php echo esc_html( $jmk_camps[0]['note'] ); ?></p>

		<div class="banner-wall rv" id="bworkWall">
			<?php foreach ( $jmk_fmts as $jmk_s ) : ?>
				<figure class="banner-slot is-<?php echo esc_attr( jmk_banner_shape( $jmk_s['w'], $jmk_s['h'] ) ); ?>"
					data-w="<?php echo (int) $jmk_s['w']; ?>" data-h="<?php echo (int) $jmk_s['h']; ?>">
					<figcaption>
						<?php /* Le « × » est neutre : en arabe il renverrait « 300×250 » à
						   « 250×300 ». Une dimension ne se retourne pas. */ ?>
						<span class="banner-dim" dir="ltr"><?php echo (int) $jmk_s['w']; ?>×<?php echo (int) $jmk_s['h']; ?></span>
						<span class="banner-name"><?php echo esc_html( $jmk_s['name'] ); ?></span>
					</figcaption>
					<div class="banner-stage" style="width:<?php echo (int) $jmk_s['w']; ?>px"><div class="banner-mount"></div></div>
				</figure>
			<?php endforeach; ?>
		</div>

		<p class="banner-foot rv"><?php jmk_e( 'bwork_foot' ); ?></p>
	</div>
</section>
<div class="wrap"><div class="divider"></div></div>
