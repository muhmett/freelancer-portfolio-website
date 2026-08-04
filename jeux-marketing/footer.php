<?php
/**
 * Pied de page.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main>

<footer class="site-footer">
	<div class="wrap">
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
		<?php if ( jmk_get( 'privacy_url' ) ) : ?>
			<p><a href="<?php echo esc_url( jmk_get( 'privacy_url' ) ); ?>"><?php jmk_e( 'privacy' ); ?></a></p>
		<?php endif; ?>
		<p class="small"><?php jmk_e( 'footer_note' ); ?></p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
