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
	<h3><?php jmk_e( 'quiz_title' ); ?>
		<span class="tag" id="quizCount"></span></h3>
	<div id="quizBox"></div>
	<button class="btn btn-ghost" id="quizReset"><?php jmk_e( 'restart' ); ?></button>
</div>
