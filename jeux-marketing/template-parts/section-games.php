<?php
/**
 * Grille des autres jeux.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$has_any = jmk_get( 'game_scratch' ) || jmk_get( 'game_tap' ) || jmk_get( 'game_quiz' )
	|| jmk_get( 'game_slot' ) || jmk_get( 'game_plinko' );
if ( ! $has_any ) {
	return;
}
?>
<section id="autres">
	<div class="wrap">
		<div class="sec-head rv">
			<p class="eyebrow"><?php esc_html_e( 'Le même moteur, d\'autres formats', 'jeux-marketing' ); ?></p>
			<h2><?php esc_html_e( 'Plusieurs mécaniques, une seule configuration', 'jeux-marketing' ); ?></h2>
			<p class="lede"><?php esc_html_e( 'Les lots, les probabilités et la capture d\'email sont partagés. Changer de jeu ne change pas votre paramétrage.', 'jeux-marketing' ); ?></p>
		</div>
		<div class="games">
			<?php
			if ( jmk_get( 'game_scratch' ) ) {
				get_template_part( 'template-parts/game', 'scratch' );
			}
			if ( jmk_get( 'game_tap' ) ) {
				get_template_part( 'template-parts/game', 'tap' );
			}
			if ( jmk_get( 'game_quiz' ) ) {
				get_template_part( 'template-parts/game', 'quiz' );
			}
			if ( jmk_get( 'game_slot' ) ) {
				get_template_part( 'template-parts/game', 'slot' );
			}
			if ( jmk_get( 'game_plinko' ) ) {
				get_template_part( 'template-parts/game', 'plinko' );
			}
			?>
		</div>
	</div>
</section>
<div class="wrap"><div class="divider"></div></div>
