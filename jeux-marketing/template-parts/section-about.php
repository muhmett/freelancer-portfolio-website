<?php
/**
 * Qui fait le travail.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$stats = (array) jmk_get( 'about_stats' );
?>
<section id="apropos">
	<div class="wrap">
		<div class="about rv">
			<div>
				<p class="eyebrow"><?php esc_html_e( 'Qui fait le travail', 'jeux-marketing' ); ?></p>
				<h2><?php echo esc_html( jmk_get( 'about_title' ) ); ?></h2>
				<div class="about-text">
					<?php
					foreach ( array_filter( array_map( 'trim', explode( "\n\n", (string) jmk_get( 'about_text' ) ) ) ) as $para ) {
						echo '<p>' . esc_html( $para ) . '</p>';
					}
					?>
				</div>
			</div>

			<?php if ( $stats ) : ?>
				<ul class="about-stats">
					<?php foreach ( $stats as $s ) : ?>
						<li>
							<span class="n"><?php echo esc_html( $s['n'] ); ?></span>
							<span class="l"><?php echo esc_html( $s['l'] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
</section>
<div class="wrap"><div class="divider"></div></div>
