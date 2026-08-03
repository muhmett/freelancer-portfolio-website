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
			<p><a href="<?php echo esc_url( jmk_get( 'privacy_url' ) ); ?>"><?php esc_html_e( 'Politique de confidentialité', 'jeux-marketing' ); ?></a></p>
		<?php endif; ?>
		<p class="small"><?php esc_html_e( 'Démonstration technique — les lots et la marque affichés sont donnés à titre d\'exemple.', 'jeux-marketing' ); ?></p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
