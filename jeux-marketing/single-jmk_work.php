<?php
/**
 * Une réalisation en détail.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="wrap jmk-content">
	<?php
	while ( have_posts() ) :
		the_post();
		$client = get_post_meta( get_the_ID(), 'jmk_client', true );
		$sector = get_post_meta( get_the_ID(), 'jmk_sector', true );
		$game   = get_post_meta( get_the_ID(), 'jmk_game', true );
		$result = get_post_meta( get_the_ID(), 'jmk_result', true );
		$link   = get_post_meta( get_the_ID(), 'jmk_link', true );
		?>
		<article <?php post_class( 'jmk-article work-single' ); ?>>
			<p class="eyebrow"><?php esc_html_e( 'Réalisation', 'jeux-marketing' ); ?></p>
			<h1 class="jmk-article-title"><?php the_title(); ?></h1>

			<?php if ( $client || $sector || $game ) : ?>
				<p class="work-meta">
					<?php
					echo esc_html( implode( ' · ', array_filter( array( $client, $sector, $game ) ) ) );
					?>
				</p>
			<?php endif; ?>

			<?php if ( $result ) : ?>
				<p class="work-result work-result-big"><?php echo esc_html( $result ); ?></p>
			<?php endif; ?>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="work-single-media"><?php the_post_thumbnail( 'large' ); ?></div>
			<?php endif; ?>

			<div class="jmk-prose"><?php the_content(); ?></div>

			<?php if ( $link ) : ?>
				<p class="mt-24">
					<a class="btn btn-ghost" href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener">
						<?php esc_html_e( 'Voir la campagne', 'jeux-marketing' ); ?>
					</a>
				</p>
			<?php endif; ?>

			<p class="mt-24">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>#realisations">
					<?php esc_html_e( '← Toutes les réalisations', 'jeux-marketing' ); ?>
				</a>
			</p>
		</article>
		<?php
	endwhile;
	?>
</div>

<?php
get_footer();
