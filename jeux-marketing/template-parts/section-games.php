<?php
/**
 * Les jeux, en carrousel.
 *
 * Un jeu à la fois, en grand : la grille les serrait tous sur une ligne et
 * aucun n'avait la place de respirer. Le défilement natif fait le travail
 * (scroll-snap) ; le script n'ajoute que les flèches, les puces et le
 * clavier. Sans JavaScript, le carrousel reste défilable au doigt.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$jmk_games = array();
if ( jmk_get( 'game_scratch' ) ) {
	$jmk_games[] = array( 'scratch', jmk_t( 'scratch_title' ) );
}
if ( jmk_get( 'game_slot' ) ) {
	$jmk_games[] = array( 'slot', jmk_t( 'slot_title' ) );
}
if ( jmk_get( 'game_plinko' ) ) {
	$jmk_games[] = array( 'plinko', jmk_t( 'plinko_title' ) );
}
if ( jmk_get( 'game_tap' ) ) {
	$jmk_games[] = array( 'tap', jmk_t( 'tap_title' ) );
}
if ( jmk_get( 'game_quiz' ) ) {
	$jmk_games[] = array( 'quiz', jmk_t( 'quiz_title' ) );
}

if ( ! $jmk_games ) {
	return;
}
$jmk_total = count( $jmk_games );
?>
<section id="autres">
	<div class="wrap">
		<div class="sec-head rv">
			<p class="eyebrow"><?php jmk_e( 'games_eyebrow' ); ?></p>
			<h2><?php jmk_e( 'games_h2' ); ?></h2>
			<p class="lede"><?php jmk_e( 'games_lede' ); ?></p>
		</div>

		<div class="carousel rv" id="gamesCarousel">
			<div class="carousel-viewport">
				<div class="carousel-track" id="gamesTrack"
					role="group" aria-roledescription="carousel"
					aria-label="<?php echo esc_attr( jmk_t( 'games_h2' ) ); ?>">
					<?php foreach ( $jmk_games as $jmk_i => $jmk_g ) : ?>
						<div class="carousel-slide" role="group" aria-roledescription="slide"
							aria-label="<?php
								printf(
									/* translators: 1: position, 2: total, 3: nom du jeu. */
									esc_attr( jmk_t( 'slide_of' ) ),
									(int) $jmk_i + 1,
									(int) $jmk_total,
									esc_attr( $jmk_g[1] )
								);
							?>">
							<?php get_template_part( 'template-parts/game', $jmk_g[0] ); ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<button class="carousel-arrow prev" id="gamesPrev" type="button"
				aria-label="<?php echo esc_attr( jmk_t( 'prev' ) ); ?>">
				<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 4 7 12l8 8" fill="none"
					stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>
			<button class="carousel-arrow next" id="gamesNext" type="button"
				aria-label="<?php echo esc_attr( jmk_t( 'next' ) ); ?>">
				<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 4l8 8-8 8" fill="none"
					stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>

			<div class="carousel-dots" id="gamesDots">
				<?php foreach ( $jmk_games as $jmk_i => $jmk_g ) : ?>
					<button type="button" class="carousel-dot<?php echo 0 === $jmk_i ? ' active' : ''; ?>"
						data-go="<?php echo (int) $jmk_i; ?>"
						aria-current="<?php echo 0 === $jmk_i ? 'true' : 'false'; ?>">
						<span><?php echo esc_html( $jmk_g[1] ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
<div class="wrap"><div class="divider"></div></div>
