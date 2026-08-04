<?php
/**
 * Questions fréquentes.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$faq = (array) jmk_get( 'faq' );
if ( ! $faq ) {
	return;
}
?>
<section id="faq">
	<div class="wrap">
		<div class="sec-head rv">
			<p class="eyebrow"><?php jmk_e( 'faq_eyebrow' ); ?></p>
			<h2><?php jmk_e( 'faq_h2' ); ?></h2>
		</div>
		<div class="faq rv">
			<?php foreach ( $faq as $i => $f ) : ?>
				<details <?php echo ( 0 === $i ) ? 'open' : ''; ?>>
					<summary><?php echo esc_html( $f['q'] ); ?></summary>
					<p><?php echo esc_html( $f['a'] ); ?></p>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
