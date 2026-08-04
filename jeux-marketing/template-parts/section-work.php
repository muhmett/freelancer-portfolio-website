<?php
/**
 * Réalisations.
 *
 * Rien n'est inventé ici : la section ne s'affiche que si de vraies
 * réalisations ont été saisies dans l'administration.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$works = get_posts(
	array(
		'post_type'      => 'jmk_work',
		'post_status'    => 'publish',
		'posts_per_page' => 6,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	)
);

if ( ! $works ) {
	return;
}
?>
<section id="realisations">
	<div class="wrap">
		<div class="sec-head rv">
			<p class="eyebrow"><?php esc_html_e( 'Réalisations', 'jeux-marketing' ); ?></p>
			<h2><?php echo esc_html( jmk_get( 'work_title' ) ); ?></h2>
			<p class="lede"><?php echo esc_html( jmk_get( 'work_text' ) ); ?></p>
		</div>

		<div class="works rv">
			<?php
			foreach ( $works as $w ) :
				$client = get_post_meta( $w->ID, 'jmk_client', true );
				$sector = get_post_meta( $w->ID, 'jmk_sector', true );
				$game   = get_post_meta( $w->ID, 'jmk_game', true );
				$result = get_post_meta( $w->ID, 'jmk_result', true );
				?>
				<article class="work">
					<a class="work-media" href="<?php echo esc_url( get_permalink( $w ) ); ?>">
						<?php if ( has_post_thumbnail( $w ) ) : ?>
							<?php echo get_the_post_thumbnail( $w, 'large', array( 'loading' => 'lazy', 'alt' => esc_attr( get_the_title( $w ) ) ) ); ?>
						<?php else : ?>
							<span class="work-void" aria-hidden="true"><?php echo esc_html( mb_substr( get_the_title( $w ), 0, 1 ) ); ?></span>
						<?php endif; ?>
					</a>
					<div class="work-body">
						<?php if ( $sector || $game ) : ?>
							<p class="work-meta">
								<?php echo esc_html( $sector ); ?><?php echo ( $sector && $game ) ? ' · ' : ''; ?><?php echo esc_html( $game ); ?>
							</p>
						<?php endif; ?>
						<h3><a href="<?php echo esc_url( get_permalink( $w ) ); ?>"><?php echo esc_html( get_the_title( $w ) ); ?></a></h3>
						<?php if ( $client ) : ?>
							<p class="work-client"><?php echo esc_html( $client ); ?></p>
						<?php endif; ?>
						<?php if ( $result ) : ?>
							<p class="work-result"><?php echo esc_html( $result ); ?></p>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<div class="wrap"><div class="divider"></div></div>
