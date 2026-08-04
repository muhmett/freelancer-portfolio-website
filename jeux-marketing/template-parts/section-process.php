<?php
/**
 * Comment ça se passe, étape par étape.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$steps = (array) jmk_get( 'process' );
if ( ! $steps ) {
	return;
}
?>
<section id="process">
	<div class="wrap">
		<div class="sec-head rv">
			<p class="eyebrow"><?php esc_html_e( 'Comment ça se passe', 'jeux-marketing' ); ?></p>
			<h2><?php esc_html_e( 'De votre message au jeu en ligne', 'jeux-marketing' ); ?></h2>
		</div>
		<ol class="steps rv">
			<?php foreach ( $steps as $i => $s ) : ?>
				<li class="step">
					<span class="step-n"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
					<div>
						<h3><?php echo esc_html( $s['q'] ); ?></h3>
						<p><?php echo esc_html( $s['a'] ); ?></p>
					</div>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
<div class="wrap"><div class="divider"></div></div>
