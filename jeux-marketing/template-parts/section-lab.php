<?php
/**
 * Réglage des probabilités et simulation.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section id="labo">
	<div class="wrap">
		<div class="sec-head rv">
			<p class="eyebrow"><?php esc_html_e( 'La question que tout le monde pose', 'jeux-marketing' ); ?></p>
			<h2><?php esc_html_e( '« Est-ce que je contrôle qui gagne ? »', 'jeux-marketing' ); ?></h2>
			<p class="lede"><?php esc_html_e( 'Oui. Déplacez les curseurs : la roue plus haut obéit immédiatement. Puis lancez la simulation — 1 000 tours joués d\'un coup — pour vérifier que le tirage respecte vraiment vos réglages.', 'jeux-marketing' ); ?></p>
		</div>
		<div class="two rv">
			<div class="panel">
				<div class="panel-title">
					<h3><?php esc_html_e( 'Réglage des lots', 'jeux-marketing' ); ?></h3>
					<span class="lot-sub">config.json</span>
				</div>
				<div id="lotList"></div>
				<p class="small mt-16"><?php esc_html_e( 'La taille visuelle d\'un segment n\'est pas sa probabilité. C\'est volontaire : un gros lot reste bien visible sur la roue tout en n\'étant tiré que rarement.', 'jeux-marketing' ); ?></p>
			</div>
			<div class="panel">
				<div class="panel-title">
					<h3><?php esc_html_e( 'Vérification', 'jeux-marketing' ); ?></h3>
					<button class="btn btn-ghost" id="simBtn"><?php esc_html_e( 'Simuler 1 000 tours', 'jeux-marketing' ); ?></button>
				</div>
				<div id="simResults">
					<p class="sheet-empty"><?php esc_html_e( 'Lancez la simulation pour comparer les probabilités configurées aux résultats réellement obtenus.', 'jeux-marketing' ); ?></p>
				</div>
				<div class="legend">
					<span><i></i> <?php esc_html_e( 'obtenu sur 1 000 tirages', 'jeux-marketing' ); ?></span>
					<span><i class="tk"></i> <?php esc_html_e( 'valeur configurée', 'jeux-marketing' ); ?></span>
				</div>
			</div>
		</div>
		<div class="panel rv mt-24" id="capPanel" hidden>
			<div class="panel-title">
				<h3><?php esc_html_e( 'Plafonnement d\'un lot', 'jeux-marketing' ); ?></h3>
				<span class="lot-sub"><?php esc_html_e( 'stock · max', 'jeux-marketing' ); ?></span>
			</div>
			<p class="small mb-16"><?php esc_html_e( 'Une fois le stock épuisé, le lot est retiré du tirage et les probabilités des autres se réajustent seules. Personne ne peut gagner un lot qui n\'existe plus.', 'jeux-marketing' ); ?></p>
			<div id="capRows"></div>
		</div>
	</div>
</section>
<div class="wrap"><div class="divider"></div></div>
