<?php
/**
 * Gabarit générique (articles, pages, archives).
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="wrap jmk-content">
	<?php if ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class( 'jmk-article' ); ?>>
				<?php if ( is_singular() ) : ?>
					<h1 class="jmk-article-title"><?php the_title(); ?></h1>
				<?php else : ?>
					<h2 class="jmk-article-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<?php endif; ?>

				<div class="jmk-prose">
					<?php
					if ( is_singular() ) {
						the_content();
					} else {
						the_excerpt();
					}
					?>
				</div>
			</article>
			<?php
		endwhile;

		the_posts_pagination(
			array(
				'prev_text' => jmk_t( 'prev' ),
				'next_text' => jmk_t( 'next' ),
			)
		);
		?>
	<?php else : ?>
		<p><?php jmk_e( 'nothing' ); ?></p>
	<?php endif; ?>
</div>

<?php
get_footer();
