<?php
/**
 * Points techniques.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$specs = (array) jmk_get( 'specs' );
if ( ! $specs ) {
	return;
}
?>
<section id="specs">
	<div class="wrap">
		<div class="sec-head rv">
			<p class="eyebrow"><?php esc_html_e( 'Sous le capot', 'jeux-marketing' ); ?></p>
			<h2><?php esc_html_e( 'Ce qui compte une fois le jeu en ligne', 'jeux-marketing' ); ?></h2>
		</div>
		<div class="spec rv">
			<?php foreach ( $specs as $s ) : ?>
				<div class="spec-item">
					<h3><?php echo esc_html( $s['q'] ); ?></h3>
					<p><?php echo esc_html( $s['a'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<div class="wrap"><div class="divider"></div></div>
