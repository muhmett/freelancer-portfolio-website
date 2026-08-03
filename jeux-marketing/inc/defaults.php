<?php
/**
 * Valeurs par défaut des réglages.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Réglages par défaut.
 *
 * @return array
 */
function jmk_defaults() {
	return array(

		// Contact.
		'mode'          => 'direct',
		'whatsapp'      => '212665827222',
		'fiverr_url'    => '',
		'email'         => '',

		// Marque.
		'brand_name'    => '',
		'accent'        => '#D9A441',
		'lang'          => 'fr',

		// Textes.
		'hero_eyebrow'  => 'Démonstration en direct',
		'hero_title'    => 'Tournez d\'abord.',
		'hero_title_2'  => 'On parlera ensuite.',
		'hero_text'     => 'Cette roue n\'est pas une image. Elle tourne, elle respecte des probabilités que vous pouvez modifier vous-même plus bas, et elle envoie l\'email du joueur là où vous travaillez déjà.',
		'chips'         => "44 Ko au total\nSans dépendance externe\nTactile & clavier\nFR · EN · AR (RTL)\nLivraison 48 h",

		// Jeux actifs.
		'game_wheel'    => 1,
		'game_scratch'  => 1,
		'game_tap'      => 1,
		'game_quiz'     => 1,

		// Sections affichées.
		'sec_brand'     => 1,
		'sec_lab'       => 1,
		'sec_leads'     => 1,
		'sec_roi'       => 1,
		'sec_quote'     => 1,
		'sec_specs'     => 1,
		'sec_faq'       => 1,

		// Règles de jeu.
		'one_play'      => 1,
		'webhook'       => '',
		'privacy_url'   => '',

		// Lots.
		'lots'          => array(
			array(
				'label'  => '-10 %',
				'weight' => 38,
				'cap'    => 0,
				'code'   => 'PROMO10',
				'hue'    => 0,
				'losing' => 0,
			),
			array(
				'label'  => 'Livraison offerte',
				'weight' => 24,
				'cap'    => 0,
				'code'   => 'LIVRAISON',
				'hue'    => 38,
				'losing' => 0,
			),
			array(
				'label'  => '-25 %',
				'weight' => 11,
				'cap'    => 0,
				'code'   => 'PROMO25',
				'hue'    => -42,
				'losing' => 0,
			),
			array(
				'label'  => 'Produit offert',
				'weight' => 2,
				'cap'    => 5,
				'code'   => 'CADEAU',
				'hue'    => 150,
				'losing' => 0,
			),
			array(
				'label'  => 'Réessayez',
				'weight' => 25,
				'cap'    => 0,
				'code'   => '',
				'hue'    => 0,
				'losing' => 1,
			),
		),

		// Devis.
		'options'       => array(
			array(
				'label' => 'Jeu principal à votre marque',
				'price' => 45,
				'days'  => 2,
				'on'    => 1,
				'fixed' => 1,
			),
			array(
				'label' => 'Formulaire de capture d\'email',
				'price' => 25,
				'days'  => 0,
				'on'    => 1,
				'fixed' => 0,
			),
			array(
				'label' => 'Probabilités et plafonds réglables',
				'price' => 20,
				'days'  => 0,
				'on'    => 1,
				'fixed' => 0,
			),
			array(
				'label' => 'Connexion Mailchimp / Brevo / Sheets',
				'price' => 30,
				'days'  => 1,
				'on'    => 0,
				'fixed' => 0,
			),
			array(
				'label' => 'Jeu supplémentaire (grattage, quiz…)',
				'price' => 45,
				'days'  => 1,
				'on'    => 0,
				'fixed' => 0,
			),
			array(
				'label' => 'Deuxième langue (dont arabe RTL)',
				'price' => 15,
				'days'  => 0,
				'on'    => 0,
				'fixed' => 0,
			),
			array(
				'label' => 'Mode borne tactile / salon',
				'price' => 35,
				'days'  => 1,
				'on'    => 0,
				'fixed' => 0,
			),
			array(
				'label' => 'Code source et droits complets',
				'price' => 40,
				'days'  => 0,
				'on'    => 0,
				'fixed' => 0,
			),
			array(
				'label' => 'Installation sur votre site',
				'price' => 25,
				'days'  => 1,
				'on'    => 0,
				'fixed' => 0,
			),
			array(
				'label' => 'Livraison express en 24 h',
				'price' => 25,
				'days'  => 0,
				'on'    => 0,
				'fixed' => 0,
			),
		),
		'currency'      => '€',

		// Calculateur.
		'roi_visitors'  => 8000,
		'roi_part'      => 24,
		'roi_conv'      => 4,
		'roi_cart'      => 65,

		// Quiz.
		'quiz'          => array(
			array(
				'q' => 'Quel format capte le plus d\'emails ?',
				'a' => "Un pop-up classique\nUn jeu avec un lot à la clé\nUne bannière latérale",
				'c' => 2,
			),
			array(
				'q' => 'Quand faut-il demander l\'email ?',
				'a' => "Avant de jouer\nJuste après le résultat\nÀ la fin de la semaine",
				'c' => 2,
			),
			array(
				'q' => 'Un consentement RGPD valide est…',
				'a' => "Pré-coché pour simplifier\nExplicite et jamais pré-coché\nImplicite si on joue",
				'c' => 2,
			),
		),

		// FAQ.
		'faq'           => array(
			array(
				'q' => 'Puis-je décider qui gagne le gros lot ?',
				'a' => 'Oui. Chaque lot a sa probabilité et son plafond. Vous pouvez fixer 3 % pour le gros lot, 40 % pour une remise, et limiter à 20 gagnants sur toute la campagne. Tout se règle dans un fichier de configuration, sans toucher au code.',
			),
			array(
				'q' => 'Pourquoi ne pas prendre un plugin à 20 € ?',
				'a' => 'Un plugin impose WordPress, un design générique et son propre système de lots. Ici le jeu suit votre charte, s\'intègre partout — Shopify, Wix, page HTML, borne tactile — et vous en gardez le contrôle complet, code source inclus.',
			),
			array(
				'q' => 'Le jeu va-t-il ralentir mon site ?',
				'a' => 'Non. Environ 44 Ko, sans framework de jeu. Il se charge après le contenu principal et n\'affecte ni votre score PageSpeed ni votre référencement.',
			),
			array(
				'q' => 'Où vont les emails collectés ?',
				'a' => 'Directement dans l\'outil que vous utilisez déjà : Mailchimp, Brevo, Klaviyo, Google Sheets, ou votre CRM via webhook. Une copie reste téléchargeable en CSV. Vous restez propriétaire des données.',
			),
			array(
				'q' => 'Ça fonctionne sur un écran tactile en salon ?',
				'a' => 'Oui. Mode plein écran, sans connexion internet requise, avec export des participants en fin de journée. Précisez-le avant de commander pour recevoir la bonne version.',
			),
			array(
				'q' => 'Je peux modifier les lots après la livraison ?',
				'a' => 'Oui. Lots, couleurs et probabilités sont dans un fichier lisible, accompagné d\'une notice d\'une page. Aucune compétence technique nécessaire.',
			),
		),

		// Points techniques.
		'specs'         => array(
			array(
				'q' => '44 Ko, un seul fichier',
				'a' => 'Aucun moteur de jeu, aucune bibliothèque externe. Le jeu se charge après votre contenu principal : votre score PageSpeed et votre référencement ne bougent pas.',
			),
			array(
				'q' => 'Une seule participation',
				'a' => 'Vérification côté navigateur et déduplication par email côté serveur. Dix parties depuis la même personne ne produisent qu\'un seul participant.',
			),
			array(
				'q' => 'Conforme au RGPD',
				'a' => 'Case de consentement explicite jamais pré-cochée, lien vers votre politique de confidentialité, export ou suppression des données sur simple demande.',
			),
			array(
				'q' => 'S\'intègre partout',
				'a' => 'WordPress, Shopify, Wix, Webflow, Systeme.io ou une simple page HTML. Une ligne de code à coller, ou le dossier complet si vous préférez l\'héberger vous-même.',
			),
			array(
				'q' => 'Mode borne tactile',
				'a' => 'Plein écran, sans connexion internet requise, pensé pour un stand de salon ou un point de vente. Export des participants en fin de journée.',
			),
			array(
				'q' => 'Multilingue et RTL',
				'a' => 'Français, anglais et arabe, avec inversion complète de la mise en page pour la lecture de droite à gauche. Une langue de plus se déclare dans la configuration.',
			),
		),
	);
}

/**
 * Réglages pour lesquels « vide » est une valeur volontaire.
 *
 * Pour les textes, un champ laissé vide reprend la valeur d'exemple : c'est
 * pratique. Pour un moyen de contact, ce serait dangereux — effacer le
 * numéro WhatsApp doit vraiment l'effacer, sinon le numéro d'exemple du
 * thème reste publié à l'insu du propriétaire du site.
 *
 * @return array
 */
function jmk_blankable() {
	return array( 'whatsapp', 'fiverr_url', 'email', 'webhook', 'privacy_url', 'brand_name' );
}

/**
 * Lit un réglage.
 *
 * @param string $key     Clé.
 * @param mixed  $fallback Valeur de repli.
 * @return mixed
 */
function jmk_get( $key, $fallback = null ) {
	$saved    = get_option( 'jmk_settings', array() );
	$defaults = jmk_defaults();

	if ( isset( $saved[ $key ] ) ) {
		if ( '' !== $saved[ $key ] || in_array( $key, jmk_blankable(), true ) ) {
			return $saved[ $key ];
		}
	}
	if ( isset( $defaults[ $key ] ) ) {
		return $defaults[ $key ];
	}
	return $fallback;
}
