<?php
/**
 * Avis clients.
 *
 * Vide par défaut, et c'est voulu : un avis inventé est un faux
 * témoignage. Sur Fiverr comme sur Upwork, c'est un motif de suspension,
 * et un client qui demande à parler à la référence vous met en difficulté.
 * La section n'apparaît qu'une fois de vrais avis saisis.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$reviews = array_filter(
	(array) jmk_get( 'reviews' ),
	function ( $r ) {
		return ! empty( $r['a'] );
	}
);

if ( ! $reviews ) {
	return;
}
?>
<section id="avis">
	<div class="wrap">
		<div class="sec-head rv">
			<p class="eyebrow"><?php esc_html_e( 'Ce qu\'ils en disent', 'jeux-marketing' ); ?></p>
			<h2><?php esc_html_e( 'Les retours, sans retouche', 'jeux-marketing' ); ?></h2>
		</div>
		<div class="reviews rv">
			<?php foreach ( $reviews as $r ) : ?>
				<figure class="review">
					<blockquote><?php echo esc_html( $r['a'] ); ?></blockquote>
					<figcaption><?php echo esc_html( $r['q'] ); ?></figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<div class="wrap"><div class="divider"></div></div>
