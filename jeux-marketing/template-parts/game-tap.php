<?php
/**
 * Tap-to-win.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Boîte cadeau.
 *
 * Dessinée en SVG plutôt qu'en image : quelques centaines d'octets, nette à
 * toutes les tailles, et `currentColor` la fait suivre la couleur de la marque
 * sans qu'on ait à produire un fichier par teinte.
 */
$jmk_gift = '<svg class="gift" viewBox="0 0 24 24" aria-hidden="true" focusable="false">'
	. '<path d="M8.4 2.1c-1.5-.5-2.9.9-2.3 2.3.4 1 1.6 1.6 3 2 .3.1.6.1.9.2-.3-2-.9-3.8-1.6-4.5z"/>'
	. '<path d="M15.6 2.1c1.5-.5 2.9.9 2.3 2.3-.4 1-1.6 1.6-3 2-.3.1-.6.1-.9.2.3-2 .9-3.8 1.6-4.5z"/>'
	. '<path d="M2.6 7h18.8c.6 0 1 .4 1 1v2.2c0 .6-.4 1-1 1H2.6c-.6 0-1-.4-1-1V8c0-.6.4-1 1-1z"/>'
	. '<path d="M3.6 12.6h16.8V21c0 .6-.4 1-1 1H4.6c-.6 0-1-.4-1-1v-8.4z"/>'
	. '<path d="M10.7 7h2.6v15h-2.6z" opacity=".4"/>'
	. '</svg>';
?>
<div class="game-card rv" id="jmk-tap">
	<h3><?php jmk_e( 'tap_title' ); ?>
		<span class="tag"><?php jmk_e( 'tap_tag' ); ?></span></h3>
	<div class="boxes" id="boxes">
		<?php for ( $jmk_i = 0; $jmk_i < 3; $jmk_i++ ) : ?>
			<button class="box" data-i="<?php echo (int) $jmk_i; ?>"
				aria-label="<?php
					/* translators: %d: numéro de la boîte. */
					printf( jmk_t( 'open_box' ), (int) $jmk_i + 1 );
				?>"><?php
					/* Le lot attend sous le couvercle, vide, dès le premier
					   affichage : le couvercle bascule pour le découvrir.
					   Il occupe son propre élément — le script y écrit le
					   résultat sans effacer le couvercle au passage. */
				?><span class="box-prize"></span><span class="box-lid"><?php
					echo $jmk_gift; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- balisage SVG fixe, sans donnée utilisateur.
				?></span></button>
		<?php endfor; ?>
	</div>
	<div class="row-inline">
		<button class="btn btn-ghost" id="tapReset"><?php jmk_e( 'replay' ); ?></button>
		<span class="small" id="tapMsg"><?php jmk_e( 'tap_msg' ); ?></span>
	</div>
</div>
