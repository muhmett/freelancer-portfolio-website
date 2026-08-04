<?php
/**
 * Compétences.
 *
 * Chaque groupe porte un titre et une liste d'outils, un par ligne.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$jmk_groups = (array) jmk_get( 'skills' );
if ( ! $jmk_groups ) {
	return;
}
?>
<section id="competences">
	<div class="wrap">
		<div class="sec-head rv">
			<p class="eyebrow"><?php jmk_e( 'skills_eyebrow' ); ?></p>
			<h2><?php jmk_e( 'skills_h2' ); ?></h2>
			<p class="lede"><?php jmk_e( 'skills_lede' ); ?></p>
		</div>
		<div class="skills rv">
			<?php foreach ( $jmk_groups as $jmk_g ) : ?>
				<?php $jmk_items = array_filter( array_map( 'trim', explode( "\n", (string) $jmk_g['a'] ) ) ); ?>
				<div class="skill">
					<h3><?php echo esc_html( $jmk_g['q'] ); ?></h3>
					<?php if ( $jmk_items ) : ?>
						<ul class="skill-tags">
							<?php foreach ( $jmk_items as $jmk_it ) : ?>
								<li><?php echo esc_html( $jmk_it ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<div class="wrap"><div class="divider"></div></div>
