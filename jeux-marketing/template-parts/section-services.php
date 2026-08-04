<?php
/**
 * Les packs et leur prix.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$packs = (array) jmk_get( 'packs' );
if ( ! $packs ) {
	return;
}
$currency = jmk_get( 'currency' );
?>
<section id="services">
	<div class="wrap">
		<div class="sec-head rv">
			<p class="eyebrow"><?php jmk_e( 'services_eyebrow' ); ?></p>
			<h2><?php jmk_e( 'services_h2' ); ?></h2>
			<p class="lede"><?php jmk_e( 'services_lede' ); ?></p>
		</div>

		<div class="packs rv">
			<?php foreach ( $packs as $p ) : ?>
				<?php
				$items = array_filter( array_map( 'trim', explode( "\n", (string) $p['items'] ) ) );
				?>
				<article class="pack<?php echo ! empty( $p['featured'] ) ? ' pack-star' : ''; ?>">
					<?php if ( ! empty( $p['featured'] ) ) : ?>
						<span class="pack-flag"><?php jmk_e( 'most_wanted' ); ?></span>
					<?php endif; ?>
					<h3><?php echo esc_html( $p['name'] ); ?></h3>
					<p class="pack-price"><?php echo esc_html( $p['price'] ); ?><span><?php echo esc_html( $currency ); ?></span></p>
					<p class="pack-desc"><?php echo esc_html( $p['desc'] ); ?></p>
					<?php if ( $items ) : ?>
						<ul class="pack-list">
							<?php foreach ( $items as $it ) : ?>
								<li><?php echo esc_html( $it ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<?php if ( ! empty( $p['days'] ) ) : ?>
						<p class="pack-days"><?php
							/* translators: %s: nombre de jours. */
							printf( jmk_t( 'delivered_in' ), esc_html( $p['days'] ) );
						?></p>
					<?php endif; ?>
					<button class="btn<?php echo empty( $p['featured'] ) ? ' btn-ghost' : ''; ?>" data-scroll="devis">
						<?php jmk_e( 'compose_pack' ); ?>
					</button>
				</article>
			<?php endforeach; ?>
		</div>

		<p class="small mt-16"><?php jmk_e( 'services_note' ); ?></p>
	</div>
</section>
<div class="wrap"><div class="divider"></div></div>
