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
			<p class="eyebrow"><?php esc_html_e( 'Ce que je livre', 'jeux-marketing' ); ?></p>
			<h2><?php esc_html_e( 'Trois façons de travailler ensemble', 'jeux-marketing' ); ?></h2>
			<p class="lede"><?php esc_html_e( 'Prix fermes, périmètre écrit. Ce qui n\'est pas dans la liste n\'est pas dans le prix — vous savez exactement ce que vous achetez.', 'jeux-marketing' ); ?></p>
		</div>

		<div class="packs rv">
			<?php foreach ( $packs as $p ) : ?>
				<?php
				$items = array_filter( array_map( 'trim', explode( "\n", (string) $p['items'] ) ) );
				?>
				<article class="pack<?php echo ! empty( $p['featured'] ) ? ' pack-star' : ''; ?>">
					<?php if ( ! empty( $p['featured'] ) ) : ?>
						<span class="pack-flag"><?php esc_html_e( 'le plus demandé', 'jeux-marketing' ); ?></span>
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
							printf( esc_html__( 'Livré en %s jours', 'jeux-marketing' ), esc_html( $p['days'] ) );
						?></p>
					<?php endif; ?>
					<button class="btn<?php echo empty( $p['featured'] ) ? ' btn-ghost' : ''; ?>" data-scroll="devis">
						<?php esc_html_e( 'Composer ce pack', 'jeux-marketing' ); ?>
					</button>
				</article>
			<?php endforeach; ?>
		</div>

		<p class="small mt-16"><?php esc_html_e( 'Un besoin qui ne rentre dans aucune case ? Décrivez-le, je réponds si c\'est faisable et en combien de temps — y compris quand la réponse est non.', 'jeux-marketing' ); ?></p>
	</div>
</section>
<div class="wrap"><div class="divider"></div></div>
