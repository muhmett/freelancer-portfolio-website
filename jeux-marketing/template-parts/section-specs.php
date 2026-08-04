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
			<p class="eyebrow"><?php jmk_e( 'specs_eyebrow' ); ?></p>
			<h2><?php jmk_e( 'specs_h2' ); ?></h2>
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
