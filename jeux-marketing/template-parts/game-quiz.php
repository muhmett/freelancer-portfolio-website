<?php
/**
 * Quiz de marque.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="game-card rv" id="jmk-quiz">
	<h3><?php esc_html_e( 'Quiz de marque', 'jeux-marketing' ); ?>
		<span class="tag" id="quizCount"></span></h3>
	<div id="quizBox"></div>
	<button class="btn btn-ghost" id="quizReset"><?php esc_html_e( 'Recommencer', 'jeux-marketing' ); ?></button>
</div>
