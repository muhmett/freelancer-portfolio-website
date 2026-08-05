<?php
/**
 * Panneau d'administration.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menu d'administration.
 */
function jmk_admin_menu() {
	add_menu_page(
		__( 'Jeux Marketing', 'jeux-marketing' ),
		__( 'Jeux Marketing', 'jeux-marketing' ),
		'manage_options',
		'jmk-settings',
		'jmk_settings_page',
		'dashicons-forms',
		58
	);
	add_submenu_page(
		'jmk-settings',
		__( 'Réglages du jeu', 'jeux-marketing' ),
		__( 'Réglages du jeu', 'jeux-marketing' ),
		'manage_options',
		'jmk-settings',
		'jmk_settings_page'
	);
}
add_action( 'admin_menu', 'jmk_admin_menu' );

/**
 * Déclaration du réglage.
 */
function jmk_register_settings() {
	register_setting(
		'jmk_group',
		'jmk_settings',
		array(
			'sanitize_callback' => 'jmk_sanitize',
			'default'           => jmk_defaults(),
		)
	);
}
add_action( 'admin_init', 'jmk_register_settings' );

/**
 * Nettoyage des données envoyées.
 *
 * @param array $in Données brutes.
 * @return array
 */
function jmk_sanitize( $in ) {
	$old = get_option( 'jmk_settings', array() );
	$out = is_array( $old ) ? $old : array();
	$in  = is_array( $in ) ? $in : array();

	// Le contenu traduisible arrive sous la langue en cours d'édition. On le
	// remonte à plat le temps du nettoyage, puis on le redescend dans son
	// compartiment : les deux autres langues ne sont jamais touchées.
	$lang = isset( $in['__edit_lang'] ) ? sanitize_key( $in['__edit_lang'] ) : jmk_default_lang();
	if ( ! isset( jmk_langs()[ $lang ] ) ) {
		$lang = 'en';
	}
	unset( $in['__edit_lang'] );

	if ( isset( $in[ $lang ] ) && is_array( $in[ $lang ] ) ) {
		$in = array_merge( $in, $in[ $lang ] );
		unset( $in[ $lang ] );
	}

	$trans = jmk_translatable();
	$bucket = isset( $out[ $lang ] ) && is_array( $out[ $lang ] ) ? $out[ $lang ] : array();

	// Champs texte simples.
	$text = array( 'brand_name', 'hero_eyebrow', 'hero_title', 'hero_title_2', 'currency', 'about_title', 'work_title' );
	foreach ( $text as $k ) {
		if ( isset( $in[ $k ] ) ) {
			$out[ $k ] = sanitize_text_field( wp_unslash( $in[ $k ] ) );
		}
	}

	// Zones de texte.
	foreach ( array( 'hero_text', 'chips', 'about_text', 'work_text' ) as $k ) {
		if ( isset( $in[ $k ] ) ) {
			$out[ $k ] = sanitize_textarea_field( wp_unslash( $in[ $k ] ) );
		}
	}

	// Numéro WhatsApp : chiffres uniquement.
	if ( isset( $in['whatsapp'] ) ) {
		$out['whatsapp'] = preg_replace( '/\D/', '', wp_unslash( $in['whatsapp'] ) );
	}

	// URLs.
	foreach ( array( 'fiverr_url', 'webhook', 'privacy_url' ) as $k ) {
		if ( isset( $in[ $k ] ) ) {
			$out[ $k ] = esc_url_raw( wp_unslash( $in[ $k ] ) );
		}
	}

	// Email.
	if ( isset( $in['email'] ) ) {
		$out['email'] = sanitize_email( wp_unslash( $in['email'] ) );
	}

	// Listes fermées.
	if ( isset( $in['mode'] ) ) {
		$out['mode'] = in_array( $in['mode'], array( 'direct', 'fiverr' ), true ) ? $in['mode'] : 'direct';
	}
	if ( isset( $in['skin'] ) ) {
		$out['skin'] = in_array( $in['skin'], array( 'elegant', 'arcade' ), true ) ? $in['skin'] : 'elegant';
	}
	if ( isset( $in['lang'] ) ) {
		$out['lang'] = in_array( $in['lang'], array( 'fr', 'en', 'ar' ), true ) ? $in['lang'] : 'fr';
	}

	// Couleur.
	if ( isset( $in['accent'] ) ) {
		$hex           = sanitize_hex_color( wp_unslash( $in['accent'] ) );
		$out['accent'] = $hex ? $hex : '#D9A441';
	}

	// Nombres.
	foreach ( array( 'roi_visitors', 'roi_part', 'roi_conv', 'roi_cart' ) as $k ) {
		if ( isset( $in[ $k ] ) ) {
			$out[ $k ] = max( 0, (float) $in[ $k ] );
		}
	}

	// Cases à cocher : absentes = 0.
	$flags = array(
		'game_wheel', 'game_scratch', 'game_tap', 'game_quiz', 'game_slot', 'game_plinko',
		'sec_brand', 'sec_lab', 'sec_leads', 'sec_roi', 'sec_quote', 'sec_specs', 'sec_faq',
		'sec_about', 'sec_services', 'sec_work', 'sec_process', 'sec_reviews', 'sec_skills',
		'one_play',
	);
	foreach ( $flags as $k ) {
		$out[ $k ] = empty( $in[ $k ] ) ? 0 : 1;
	}

	// Lots.
	if ( isset( $in['lots'] ) && is_array( $in['lots'] ) ) {
		$lots = array();
		foreach ( $in['lots'] as $lot ) {
			$label = sanitize_text_field( wp_unslash( isset( $lot['label'] ) ? $lot['label'] : '' ) );
			if ( '' === $label ) {
				continue;
			}
			$lots[] = array(
				'label'  => $label,
				'weight' => max( 0, (float) ( isset( $lot['weight'] ) ? $lot['weight'] : 0 ) ),
				'cap'    => max( 0, (int) ( isset( $lot['cap'] ) ? $lot['cap'] : 0 ) ),
				'code'   => sanitize_text_field( wp_unslash( isset( $lot['code'] ) ? $lot['code'] : '' ) ),
				'hue'    => (float) ( isset( $lot['hue'] ) ? $lot['hue'] : 0 ),
				'losing' => empty( $lot['losing'] ) ? 0 : 1,
			);
		}
		if ( count( $lots ) >= 2 ) {
			$out['lots'] = $lots;
		} else {
			add_settings_error(
				'jmk_settings',
				'jmk_lots',
				__( 'Il faut au moins deux lots. Les lots précédents ont été conservés.', 'jeux-marketing' )
			);
		}
	}

	// Options du devis.
	if ( isset( $in['options'] ) && is_array( $in['options'] ) ) {
		$opts = array();
		foreach ( $in['options'] as $o ) {
			$label = sanitize_text_field( wp_unslash( isset( $o['label'] ) ? $o['label'] : '' ) );
			if ( '' === $label ) {
				continue;
			}
			$opts[] = array(
				'label' => $label,
				'price' => max( 0, (float) ( isset( $o['price'] ) ? $o['price'] : 0 ) ),
				'days'  => max( 0, (int) ( isset( $o['days'] ) ? $o['days'] : 0 ) ),
				'on'    => empty( $o['on'] ) ? 0 : 1,
				'fixed' => empty( $o['fixed'] ) ? 0 : 1,
			);
		}
		$out['options'] = $opts;
	}

	// Packs.
	if ( isset( $in['packs'] ) && is_array( $in['packs'] ) ) {
		$packs = array();
		foreach ( $in['packs'] as $p ) {
			$name = sanitize_text_field( wp_unslash( isset( $p['name'] ) ? $p['name'] : '' ) );
			if ( '' === $name ) {
				continue;
			}
			$packs[] = array(
				'name'     => $name,
				'price'    => max( 0, (float) ( isset( $p['price'] ) ? $p['price'] : 0 ) ),
				'days'     => max( 0, (int) ( isset( $p['days'] ) ? $p['days'] : 0 ) ),
				'desc'     => sanitize_text_field( wp_unslash( isset( $p['desc'] ) ? $p['desc'] : '' ) ),
				'items'    => sanitize_textarea_field( wp_unslash( isset( $p['items'] ) ? $p['items'] : '' ) ),
				'featured' => empty( $p['featured'] ) ? 0 : 1,
			);
		}
		$out['packs'] = $packs;
	}

	// Chiffres clés de la section « à propos ».
	if ( isset( $in['about_stats'] ) && is_array( $in['about_stats'] ) ) {
		$stats = array();
		foreach ( $in['about_stats'] as $st ) {
			$n = sanitize_text_field( wp_unslash( isset( $st['n'] ) ? $st['n'] : '' ) );
			$l = sanitize_text_field( wp_unslash( isset( $st['l'] ) ? $st['l'] : '' ) );
			if ( '' === $n && '' === $l ) {
				continue;
			}
			$stats[] = array( 'n' => $n, 'l' => $l );
		}
		$out['about_stats'] = $stats;
	}

	// Blocs question / réponse : quiz, faq, specs, déroulé, avis.
	foreach ( array( 'quiz', 'faq', 'specs', 'process', 'reviews', 'skills' ) as $key ) {
		if ( ! isset( $in[ $key ] ) || ! is_array( $in[ $key ] ) ) {
			continue;
		}
		$rows = array();
		foreach ( $in[ $key ] as $r ) {
			$q = sanitize_text_field( wp_unslash( isset( $r['q'] ) ? $r['q'] : '' ) );
			$a = sanitize_textarea_field( wp_unslash( isset( $r['a'] ) ? $r['a'] : '' ) );
			if ( '' === $q ) {
				continue;
			}
			$row = array( 'q' => $q, 'a' => $a );
			if ( 'quiz' === $key ) {
				$row['c'] = max( 1, (int) ( isset( $r['c'] ) ? $r['c'] : 1 ) );
			}
			$rows[] = $row;
		}
		$out[ $key ] = $rows;
	}

	// Redescendre le contenu traduisible dans sa langue.
	foreach ( $trans as $k ) {
		if ( array_key_exists( $k, $out ) ) {
			$bucket[ $k ] = $out[ $k ];
			unset( $out[ $k ] );
		}
	}
	$out[ $lang ] = $bucket;

	return $out;
}

/**
 * Scripts et styles de l'administration.
 *
 * @param string $hook Page courante.
 */
function jmk_admin_assets( $hook ) {
	if ( false === strpos( $hook, 'jmk-settings' ) ) {
		return;
	}
	wp_enqueue_style( 'jmk-admin', JMK_URI . '/assets/css/admin.css', array(), jmk_asset_version( 'assets/css/admin.css' ) );
	wp_enqueue_script( 'jmk-admin', JMK_URI . '/assets/js/admin.js', array(), jmk_asset_version( 'assets/js/admin.js' ), true );
}
add_action( 'admin_enqueue_scripts', 'jmk_admin_assets' );

/**
 * Langue dont on modifie le contenu dans l'administration.
 *
 * @return string
 */
function jmk_admin_lang() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- simple choix d'affichage.
	$asked = isset( $_GET['jmk_lang'] ) ? sanitize_key( wp_unslash( $_GET['jmk_lang'] ) ) : '';
	return isset( jmk_langs()[ $asked ] ) ? $asked : jmk_default_lang();
}

/**
 * Champ texte.
 *
 * @param string $name  Nom.
 * @param string $label Libellé.
 * @param string $help  Aide.
 * @param string $type  Type HTML.
 */
function jmk_field( $name, $label, $help = '', $type = 'text', $lang = null ) {
	$val = jmk_get( $name, null, $lang );
	echo '<div class="jmk-field">';
	echo '<label for="jmk-' . esc_attr( $name ) . '">' . esc_html( $label ) . '</label>';
	echo '<input type="' . esc_attr( $type ) . '" id="jmk-' . esc_attr( $name ) . '" name="jmk_settings[' . esc_attr( $name ) . ']" value="' . esc_attr( $val ) . '">';
	if ( $help ) {
		echo '<p class="jmk-help">' . esc_html( $help ) . '</p>';
	}
	echo '</div>';
}

/**
 * Champ texte traduisible : le nom porte la langue en cours d'édition.
 *
 * @param string $name  Nom.
 * @param string $label Libellé.
 * @param string $help  Aide.
 * @param string $type  Type HTML.
 */
function jmk_field_l( $name, $label, $help = '', $type = 'text' ) {
	$lang = jmk_admin_lang();
	$val  = jmk_get( $name, null, $lang );
	echo '<div class="jmk-field">';
	echo '<label for="jmk-' . esc_attr( $name ) . '">' . esc_html( $label ) . '</label>';
	echo '<input type="' . esc_attr( $type ) . '" id="jmk-' . esc_attr( $name ) . '" name="jmk_settings[' . esc_attr( $lang ) . '][' . esc_attr( $name ) . ']" value="' . esc_attr( $val ) . '">';
	if ( $help ) {
		echo '<p class="jmk-help">' . esc_html( $help ) . '</p>';
	}
	echo '</div>';
}

/**
 * Case à cocher.
 *
 * @param string $name  Nom.
 * @param string $label Libellé.
 * @param string $help  Aide.
 */
function jmk_check( $name, $label, $help = '' ) {
	$val = jmk_get( $name );
	echo '<label class="jmk-check"><input type="checkbox" name="jmk_settings[' . esc_attr( $name ) . ']" value="1" ' . checked( 1, (int) $val, false ) . '> <span>' . esc_html( $label ) . '</span>';
	if ( $help ) {
		echo '<em>' . esc_html( $help ) . '</em>';
	}
	echo '</label>';
}

/**
 * Page de réglages.
 */
function jmk_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$jmk_el = jmk_admin_lang();
	$tabs   = array(
		'general' => __( 'Général', 'jeux-marketing' ),
		'marque'  => __( 'Marque', 'jeux-marketing' ),
		'jeux'    => __( 'Jeux', 'jeux-marketing' ),
		'lots'    => __( 'Lots et probabilités', 'jeux-marketing' ),
		'devis'   => __( 'Devis', 'jeux-marketing' ),
		'textes'  => __( 'Textes', 'jeux-marketing' ),
		'porte'   => __( 'Portfolio', 'jeux-marketing' ),
	);
	?>
	<div class="wrap jmk-wrap">
		<h1><?php esc_html_e( 'Réglages du jeu', 'jeux-marketing' ); ?></h1>
		<p class="jmk-intro"><?php esc_html_e( 'Tout ce qui apparaît sur la page d\'accueil se règle ici. Enregistrez, puis rechargez la page publique pour voir le résultat.', 'jeux-marketing' ); ?></p>
		<?php settings_errors( 'jmk_settings' ); ?>

		<div class="jmk-langbar">
			<span><?php esc_html_e( 'Contenu affiché en :', 'jeux-marketing' ); ?></span>
			<?php foreach ( jmk_langs() as $jmk_code => $jmk_info ) : ?>
				<a class="jmk-langbtn<?php echo ( $jmk_code === $jmk_el ) ? ' active' : ''; ?>"
					href="<?php echo esc_url( add_query_arg( 'jmk_lang', $jmk_code, admin_url( 'admin.php?page=jmk-settings' ) ) ); ?>">
					<?php echo esc_html( $jmk_info['name'] ); ?></a>
			<?php endforeach; ?>
			<em><?php esc_html_e( 'Les textes, lots, packs et questions ci-dessous appartiennent à cette langue. Les réglages techniques (contact, couleur, sections, plafonds) sont communs aux trois.', 'jeux-marketing' ); ?></em>
		</div>

		<nav class="jmk-tabs">
			<?php foreach ( $tabs as $id => $label ) : ?>
				<button type="button" class="jmk-tab" data-tab="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></button>
			<?php endforeach; ?>
		</nav>

		<form method="post" action="options.php">
			<?php settings_fields( 'jmk_group' ); ?>
			<input type="hidden" name="jmk_settings[__edit_lang]" value="<?php echo esc_attr( $jmk_el ); ?>">

			<!-- GÉNÉRAL -->
			<div class="jmk-pane" data-pane="general">
				<div class="jmk-card">
					<h2><?php esc_html_e( 'Comment vous contacte-t-on ?', 'jeux-marketing' ); ?></h2>
					<p class="jmk-help"><?php esc_html_e( 'Fiverr interdit le partage de coordonnées. Publiez deux versions du site : l\'une en mode WhatsApp pour vos réseaux sociaux, l\'autre en mode Fiverr pour le lien de votre annonce.', 'jeux-marketing' ); ?></p>
					<?php $mode = jmk_get( 'mode' ); ?>
					<div class="jmk-radio">
						<label><input type="radio" name="jmk_settings[mode]" value="direct" <?php checked( 'direct', $mode ); ?>>
							<strong><?php esc_html_e( 'Mode direct', 'jeux-marketing' ); ?></strong>
							<em><?php esc_html_e( 'Bouton WhatsApp visible partout. Pour TikTok, Facebook, LinkedIn, prospection.', 'jeux-marketing' ); ?></em></label>
						<label><input type="radio" name="jmk_settings[mode]" value="fiverr" <?php checked( 'fiverr', $mode ); ?>>
							<strong><?php esc_html_e( 'Mode Fiverr', 'jeux-marketing' ); ?></strong>
							<em><?php esc_html_e( 'WhatsApp entièrement masqué, bouton « Commander sur Fiverr ». Pour le lien de votre annonce.', 'jeux-marketing' ); ?></em></label>
					</div>
					<div class="jmk-grid">
						<?php
						jmk_field( 'whatsapp', __( 'Numéro WhatsApp', 'jeux-marketing' ), __( 'Format international, chiffres uniquement. Exemple : 212665827222', 'jeux-marketing' ) );
						jmk_field( 'fiverr_url', __( 'Lien de votre annonce Fiverr', 'jeux-marketing' ), __( 'Utilisé uniquement en mode Fiverr.', 'jeux-marketing' ), 'url' );
						jmk_field( 'email', __( 'Email de notification', 'jeux-marketing' ), __( 'Vous recevez un message à chaque nouveau participant. Laissez vide pour désactiver.', 'jeux-marketing' ), 'email' );
						?>
					</div>
				</div>

				<div class="jmk-card">
					<h2><?php esc_html_e( 'Sections affichées', 'jeux-marketing' ); ?></h2>
					<div class="jmk-checks">
						<?php
						jmk_check( 'sec_services', __( 'Tarifs et packs', 'jeux-marketing' ) );
						jmk_check( 'sec_skills', __( 'Compétences', 'jeux-marketing' ) );
						jmk_check( 'sec_work', __( 'Réalisations', 'jeux-marketing' ), __( 'Ne s\'affiche que si vous avez saisi au moins une réalisation.', 'jeux-marketing' ) );
						jmk_check( 'sec_reviews', __( 'Avis clients', 'jeux-marketing' ), __( 'Ne s\'affiche que si vous avez saisi au moins un avis.', 'jeux-marketing' ) );
						jmk_check( 'sec_process', __( 'Déroulé d\'un projet', 'jeux-marketing' ) );
						jmk_check( 'sec_about', __( 'À propos', 'jeux-marketing' ) );
						jmk_check( 'sec_brand', __( 'Aperçu aux couleurs du visiteur', 'jeux-marketing' ) );
						jmk_check( 'sec_lab', __( 'Réglage des probabilités et simulation', 'jeux-marketing' ) );
						jmk_check( 'sec_leads', __( 'Formulaire de capture d\'email', 'jeux-marketing' ) );
						jmk_check( 'sec_roi', __( 'Calculateur de rentabilité', 'jeux-marketing' ) );
						jmk_check( 'sec_quote', __( 'Devis instantané', 'jeux-marketing' ) );
						jmk_check( 'sec_specs', __( 'Points techniques', 'jeux-marketing' ) );
						jmk_check( 'sec_faq', __( 'Questions fréquentes', 'jeux-marketing' ) );
						?>
					</div>
				</div>

				<div class="jmk-card">
					<h2><?php esc_html_e( 'Règles et destination des emails', 'jeux-marketing' ); ?></h2>
					<div class="jmk-checks">
						<?php jmk_check( 'one_play', __( 'Une seule participation par personne', 'jeux-marketing' ), __( 'Vérification navigateur, plus déduplication par email côté serveur.', 'jeux-marketing' ) ); ?>
					</div>
					<div class="jmk-grid">
						<?php
						jmk_field( 'webhook', __( 'Webhook (Zapier, Make, CRM…)', 'jeux-marketing' ), __( 'Chaque participant y est envoyé en JSON. Laissez vide pour ne rien envoyer.', 'jeux-marketing' ), 'url' );
						jmk_field( 'privacy_url', __( 'Lien vers la politique de confidentialité', 'jeux-marketing' ), __( 'Affiché à côté de la case de consentement. Obligatoire pour le RGPD.', 'jeux-marketing' ), 'url' );
						?>
					</div>
				</div>
			</div>

			<!-- MARQUE -->
			<div class="jmk-pane" data-pane="marque">
				<div class="jmk-card">
					<h2><?php esc_html_e( 'Identité affichée', 'jeux-marketing' ); ?></h2>
					<div class="jmk-grid">
						<?php jmk_field( 'brand_name', __( 'Nom affiché en haut du site', 'jeux-marketing' ), __( 'Laissez vide pour afficher « Jeux marketing HTML5 ».', 'jeux-marketing' ) ); ?>
						<div class="jmk-field">
							<label for="jmk-accent"><?php esc_html_e( 'Couleur principale', 'jeux-marketing' ); ?></label>
							<input type="color" id="jmk-accent" name="jmk_settings[accent]" value="<?php echo esc_attr( jmk_get( 'accent' ) ); ?>">
							<p class="jmk-help"><?php esc_html_e( 'Utilisée par la roue, les boutons et tous les accents de la page.', 'jeux-marketing' ); ?></p>
						</div>
						<div class="jmk-field">
							<label for="jmk-lang"><?php esc_html_e( 'Langue du jeu', 'jeux-marketing' ); ?></label>
							<select id="jmk-lang" name="jmk_settings[lang]">
								<?php
								$lang = jmk_get( 'lang' );
								foreach ( array( 'fr' => 'Français', 'en' => 'English', 'ar' => 'العربية (RTL)' ) as $k => $v ) {
									echo '<option value="' . esc_attr( $k ) . '" ' . selected( $k, $lang, false ) . '>' . esc_html( $v ) . '</option>';
								}
								?>
							</select>
							<p class="jmk-help"><?php esc_html_e( 'L\'arabe bascule automatiquement la page en lecture de droite à gauche.', 'jeux-marketing' ); ?></p>
						</div>
						<?php jmk_field( 'currency', __( 'Symbole monétaire', 'jeux-marketing' ), __( 'Exemple : € — MAD — $', 'jeux-marketing' ) ); ?>
					</div>
					<p class="jmk-help"><?php esc_html_e( 'Le logo se règle dans Apparence puis Personnaliser.', 'jeux-marketing' ); ?></p>
				</div>

				<div class="jmk-card">
					<h2><?php esc_html_e( 'Style visuel', 'jeux-marketing' ); ?></h2>
					<p class="jmk-help"><?php esc_html_e( 'Deux habillages du même jeu. Le style ne change ni les lots, ni les probabilités, ni la capture d\'email : uniquement l\'apparence.', 'jeux-marketing' ); ?></p>
					<?php $jmk_skin = jmk_get( 'skin' ); ?>
					<div class="jmk-radio">
						<label><input type="radio" name="jmk_settings[skin]" value="elegant" <?php checked( 'elegant', $jmk_skin ); ?>>
							<strong><?php esc_html_e( 'Sobre', 'jeux-marketing' ); ?></strong>
							<em><?php esc_html_e( 'Fond sombre, or discret, formes nettes. Pour une marque haut de gamme, un cabinet, une boutique de créateur.', 'jeux-marketing' ); ?></em></label>
						<label><input type="radio" name="jmk_settings[skin]" value="arcade" <?php checked( 'arcade', $jmk_skin ); ?>>
							<strong><?php esc_html_e( 'Fête foraine', 'jeux-marketing' ); ?></strong>
							<em><?php esc_html_e( 'Violet saturé, or épais, ampoules autour de la roue, boutons bombés. Pour une promotion grand public, un jeu-concours de marque, un stand de salon.', 'jeux-marketing' ); ?></em></label>
					</div>
				</div>
			</div>

			<!-- JEUX -->
			<div class="jmk-pane" data-pane="jeux">
				<div class="jmk-card">
					<h2><?php esc_html_e( 'Jeux visibles sur la page', 'jeux-marketing' ); ?></h2>
					<div class="jmk-checks">
						<?php
						jmk_check( 'game_wheel', __( 'Roue de la fortune', 'jeux-marketing' ), __( 'Affichée en haut de page. Décochez pour la retirer entièrement.', 'jeux-marketing' ) );
						jmk_check( 'game_scratch', __( 'Carte à gratter', 'jeux-marketing' ) );
						jmk_check( 'game_tap', __( 'Tap-to-win', 'jeux-marketing' ) );
						jmk_check( 'game_quiz', __( 'Quiz de marque', 'jeux-marketing' ) );
						jmk_check( 'game_slot', __( 'Machine à sous', 'jeux-marketing' ) );
						jmk_check( 'game_plinko', __( 'Pluie de lots', 'jeux-marketing' ), __( 'La bille tombe entre les clous et se range dans la case du lot.', 'jeux-marketing' ) );
						?>
					</div>
				</div>

				<div class="jmk-card">
					<h2><?php esc_html_e( 'Questions du quiz', 'jeux-marketing' ); ?></h2>
					<p class="jmk-help"><?php esc_html_e( 'Une réponse par ligne. Indiquez le numéro de la bonne réponse : 1 pour la première ligne, 2 pour la deuxième, etc.', 'jeux-marketing' ); ?></p>
					<div class="jmk-rep" data-rep="quiz">
						<?php foreach ( (array) jmk_get( 'quiz', null, $jmk_el ) as $i => $q ) : ?>
							<div class="jmk-row">
								<span class="jmk-handle">≡</span>
								<div class="jmk-row-body">
									<input type="text" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][quiz][<?php echo (int) $i; ?>][q]" value="<?php echo esc_attr( $q['q'] ); ?>" placeholder="<?php esc_attr_e( 'Question', 'jeux-marketing' ); ?>">
									<textarea rows="3" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][quiz][<?php echo (int) $i; ?>][a]" placeholder="<?php esc_attr_e( 'Une réponse par ligne', 'jeux-marketing' ); ?>"><?php echo esc_textarea( $q['a'] ); ?></textarea>
									<label class="jmk-inline"><?php esc_html_e( 'Bonne réponse n°', 'jeux-marketing' ); ?>
										<input type="number" min="1" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][quiz][<?php echo (int) $i; ?>][c]" value="<?php echo (int) $q['c']; ?>"></label>
								</div>
								<button type="button" class="jmk-del" aria-label="<?php esc_attr_e( 'Supprimer', 'jeux-marketing' ); ?>">×</button>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button jmk-add" data-add="quiz"><?php esc_html_e( 'Ajouter une question', 'jeux-marketing' ); ?></button>
				</div>
			</div>

			<!-- LOTS -->
			<div class="jmk-pane" data-pane="lots">
				<div class="jmk-card">
					<h2><?php esc_html_e( 'Lots et probabilités', 'jeux-marketing' ); ?></h2>
					<p class="jmk-help"><?php esc_html_e( 'Le poids n\'est pas un pourcentage : c\'est une part relative. Un lot à 40 face à un lot à 10 sort quatre fois plus souvent. Le pourcentage réel est calculé et affiché en bas.', 'jeux-marketing' ); ?></p>

					<div class="jmk-head">
						<span><?php esc_html_e( 'Libellé', 'jeux-marketing' ); ?></span>
						<span><?php esc_html_e( 'Poids', 'jeux-marketing' ); ?></span>
						<span><?php esc_html_e( 'Plafond', 'jeux-marketing' ); ?></span>
						<span><?php esc_html_e( 'Code promo', 'jeux-marketing' ); ?></span>
						<span><?php esc_html_e( 'Teinte', 'jeux-marketing' ); ?></span>
						<span><?php esc_html_e( 'Perdant', 'jeux-marketing' ); ?></span>
						<span></span>
					</div>

					<div class="jmk-rep jmk-lots" data-rep="lots">
						<?php foreach ( (array) jmk_get( 'lots', null, $jmk_el ) as $i => $lot ) : ?>
							<div class="jmk-lot">
								<input type="text" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][lots][<?php echo (int) $i; ?>][label]" value="<?php echo esc_attr( $lot['label'] ); ?>" placeholder="<?php esc_attr_e( 'Nom du lot', 'jeux-marketing' ); ?>">
								<input type="number" step="0.1" min="0" class="jmk-weight" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][lots][<?php echo (int) $i; ?>][weight]" value="<?php echo esc_attr( $lot['weight'] ); ?>">
								<input type="number" min="0" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][lots][<?php echo (int) $i; ?>][cap]" value="<?php echo (int) $lot['cap']; ?>" title="<?php esc_attr_e( '0 = illimité', 'jeux-marketing' ); ?>">
								<input type="text" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][lots][<?php echo (int) $i; ?>][code]" value="<?php echo esc_attr( $lot['code'] ); ?>" placeholder="PROMO10">
								<input type="number" step="1" min="-180" max="180" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][lots][<?php echo (int) $i; ?>][hue]" value="<?php echo esc_attr( $lot['hue'] ); ?>" title="<?php esc_attr_e( 'Décalage de teinte par rapport à la couleur principale', 'jeux-marketing' ); ?>">
								<label class="jmk-mini"><input type="checkbox" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][lots][<?php echo (int) $i; ?>][losing]" value="1" <?php checked( 1, (int) $lot['losing'] ); ?>></label>
								<button type="button" class="jmk-del" aria-label="<?php esc_attr_e( 'Supprimer', 'jeux-marketing' ); ?>">×</button>
							</div>
						<?php endforeach; ?>
					</div>

					<button type="button" class="button jmk-add" data-add="lots"><?php esc_html_e( 'Ajouter un lot', 'jeux-marketing' ); ?></button>

					<div class="jmk-preview">
						<h3><?php esc_html_e( 'Probabilités réelles', 'jeux-marketing' ); ?></h3>
						<div id="jmk-shares"></div>
					</div>

					<div class="jmk-note">
						<p><strong><?php esc_html_e( 'Plafond', 'jeux-marketing' ); ?></strong> — <?php esc_html_e( 'nombre maximum de fois où ce lot peut être gagné sur toute la campagne. 0 signifie illimité. Une fois atteint, le lot est retiré du tirage et les autres probabilités se réajustent automatiquement.', 'jeux-marketing' ); ?></p>
						<p><strong><?php esc_html_e( 'Perdant', 'jeux-marketing' ); ?></strong> — <?php esc_html_e( 'ce lot n\'attribue aucun code. C\'est la case « Réessayez ».', 'jeux-marketing' ); ?></p>
						<p><strong><?php esc_html_e( 'Teinte', 'jeux-marketing' ); ?></strong> — <?php esc_html_e( 'décalage de couleur par rapport à la couleur principale, entre -180 et 180. Laissez 0 pour utiliser la couleur principale telle quelle.', 'jeux-marketing' ); ?></p>
					</div>

					<p>
						<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=jmk_reset_caps' ), 'jmk_reset_caps' ) ); ?>" class="button"><?php esc_html_e( 'Remettre les compteurs de plafond à zéro', 'jeux-marketing' ); ?></a>
						<span class="jmk-help"><?php esc_html_e( 'À faire au lancement de chaque nouvelle campagne.', 'jeux-marketing' ); ?></span>
					</p>
				</div>
			</div>

			<!-- DEVIS -->
			<div class="jmk-pane" data-pane="devis">
				<div class="jmk-card">
					<h2><?php esc_html_e( 'Options et tarifs', 'jeux-marketing' ); ?></h2>
					<p class="jmk-help"><?php esc_html_e( '« Cochée » signifie sélectionnée d\'avance pour le visiteur. « Verrouillée » signifie qu\'il ne peut pas la décocher : à réserver à votre prestation de base.', 'jeux-marketing' ); ?></p>
					<div class="jmk-head jmk-head-opt">
						<span><?php esc_html_e( 'Intitulé', 'jeux-marketing' ); ?></span>
						<span><?php esc_html_e( 'Prix', 'jeux-marketing' ); ?></span>
						<span><?php esc_html_e( 'Jours', 'jeux-marketing' ); ?></span>
						<span><?php esc_html_e( 'Cochée', 'jeux-marketing' ); ?></span>
						<span><?php esc_html_e( 'Verrouillée', 'jeux-marketing' ); ?></span>
						<span></span>
					</div>
					<div class="jmk-rep jmk-opts" data-rep="options">
						<?php foreach ( (array) jmk_get( 'options', null, $jmk_el ) as $i => $o ) : ?>
							<div class="jmk-opt">
								<input type="text" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][options][<?php echo (int) $i; ?>][label]" value="<?php echo esc_attr( $o['label'] ); ?>">
								<input type="number" step="1" min="0" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][options][<?php echo (int) $i; ?>][price]" value="<?php echo esc_attr( $o['price'] ); ?>">
								<input type="number" step="1" min="0" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][options][<?php echo (int) $i; ?>][days]" value="<?php echo (int) $o['days']; ?>">
								<label class="jmk-mini"><input type="checkbox" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][options][<?php echo (int) $i; ?>][on]" value="1" <?php checked( 1, (int) $o['on'] ); ?>></label>
								<label class="jmk-mini"><input type="checkbox" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][options][<?php echo (int) $i; ?>][fixed]" value="1" <?php checked( 1, (int) $o['fixed'] ); ?>></label>
								<button type="button" class="jmk-del" aria-label="<?php esc_attr_e( 'Supprimer', 'jeux-marketing' ); ?>">×</button>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button jmk-add" data-add="options"><?php esc_html_e( 'Ajouter une option', 'jeux-marketing' ); ?></button>
				</div>

				<div class="jmk-card">
					<h2><?php esc_html_e( 'Valeurs de départ du calculateur', 'jeux-marketing' ); ?></h2>
					<p class="jmk-help"><?php esc_html_e( 'Le visiteur peut les modifier. Ce sont seulement les chiffres affichés à l\'arrivée.', 'jeux-marketing' ); ?></p>
					<div class="jmk-grid">
						<?php
						jmk_field( 'roi_visitors', __( 'Visiteurs par mois', 'jeux-marketing' ), '', 'number' );
						jmk_field( 'roi_part', __( 'Taux de participation (%)', 'jeux-marketing' ), '', 'number' );
						jmk_field( 'roi_conv', __( 'Email vers client (%)', 'jeux-marketing' ), '', 'number' );
						jmk_field( 'roi_cart', __( 'Panier moyen', 'jeux-marketing' ), '', 'number' );
						?>
					</div>
				</div>
			</div>

			<!-- TEXTES -->
			<div class="jmk-pane" data-pane="textes">
				<div class="jmk-card">
					<h2><?php esc_html_e( 'Haut de page', 'jeux-marketing' ); ?></h2>
					<div class="jmk-grid">
						<?php
						jmk_field_l( 'hero_eyebrow', __( 'Petite ligne au-dessus du titre', 'jeux-marketing' ) );
						jmk_field_l( 'hero_title', __( 'Titre, première ligne', 'jeux-marketing' ) );
						jmk_field_l( 'hero_title_2', __( 'Titre, deuxième ligne (en couleur)', 'jeux-marketing' ) );
						?>
					</div>
					<div class="jmk-field">
						<label for="jmk-hero_text"><?php esc_html_e( 'Paragraphe d\'introduction', 'jeux-marketing' ); ?></label>
						<textarea id="jmk-hero_text" rows="3" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][hero_text]"><?php echo esc_textarea( jmk_get( 'hero_text', null, $jmk_el ) ); ?></textarea>
					</div>
					<div class="jmk-field">
						<label for="jmk-chips"><?php esc_html_e( 'Étiquettes sous le paragraphe', 'jeux-marketing' ); ?></label>
						<textarea id="jmk-chips" rows="5" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][chips]"><?php echo esc_textarea( jmk_get( 'chips', null, $jmk_el ) ); ?></textarea>
						<p class="jmk-help"><?php esc_html_e( 'Une étiquette par ligne.', 'jeux-marketing' ); ?></p>
					</div>
				</div>

				<div class="jmk-card">
					<h2><?php esc_html_e( 'Points techniques', 'jeux-marketing' ); ?></h2>
					<div class="jmk-rep" data-rep="specs">
						<?php foreach ( (array) jmk_get( 'specs', null, $jmk_el ) as $i => $s ) : ?>
							<div class="jmk-row">
								<span class="jmk-handle">≡</span>
								<div class="jmk-row-body">
									<input type="text" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][specs][<?php echo (int) $i; ?>][q]" value="<?php echo esc_attr( $s['q'] ); ?>" placeholder="<?php esc_attr_e( 'Titre', 'jeux-marketing' ); ?>">
									<textarea rows="3" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][specs][<?php echo (int) $i; ?>][a]"><?php echo esc_textarea( $s['a'] ); ?></textarea>
								</div>
								<button type="button" class="jmk-del" aria-label="<?php esc_attr_e( 'Supprimer', 'jeux-marketing' ); ?>">×</button>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button jmk-add" data-add="specs"><?php esc_html_e( 'Ajouter un point', 'jeux-marketing' ); ?></button>
				</div>

				<div class="jmk-card">
					<h2><?php esc_html_e( 'Questions fréquentes', 'jeux-marketing' ); ?></h2>
					<div class="jmk-rep" data-rep="faq">
						<?php foreach ( (array) jmk_get( 'faq', null, $jmk_el ) as $i => $f ) : ?>
							<div class="jmk-row">
								<span class="jmk-handle">≡</span>
								<div class="jmk-row-body">
									<input type="text" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][faq][<?php echo (int) $i; ?>][q]" value="<?php echo esc_attr( $f['q'] ); ?>" placeholder="<?php esc_attr_e( 'Question', 'jeux-marketing' ); ?>">
									<textarea rows="3" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][faq][<?php echo (int) $i; ?>][a]"><?php echo esc_textarea( $f['a'] ); ?></textarea>
								</div>
								<button type="button" class="jmk-del" aria-label="<?php esc_attr_e( 'Supprimer', 'jeux-marketing' ); ?>">×</button>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button jmk-add" data-add="faq"><?php esc_html_e( 'Ajouter une question', 'jeux-marketing' ); ?></button>
				</div>
			</div>

			<!-- PORTFOLIO -->
			<div class="jmk-pane" data-pane="porte">
				<div class="jmk-card">
					<h2><?php esc_html_e( 'Tarifs et packs', 'jeux-marketing' ); ?></h2>
					<p class="jmk-help"><?php esc_html_e( 'Trois packs suffisent : un prix d\'entrée, un pack complet mis en avant, un pack haut de gamme. Une ligne par élément inclus.', 'jeux-marketing' ); ?></p>
					<div class="jmk-rep" data-rep="packs">
						<?php foreach ( (array) jmk_get( 'packs', null, $jmk_el ) as $i => $pk ) : ?>
							<div class="jmk-row">
								<span class="jmk-handle">≡</span>
								<div class="jmk-row-body">
									<div class="jmk-grid-3">
										<input type="text" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][packs][<?php echo (int) $i; ?>][name]" value="<?php echo esc_attr( $pk['name'] ); ?>" placeholder="<?php esc_attr_e( 'Nom du pack', 'jeux-marketing' ); ?>">
										<input type="number" min="0" step="1" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][packs][<?php echo (int) $i; ?>][price]" value="<?php echo esc_attr( $pk['price'] ); ?>" placeholder="<?php esc_attr_e( 'Prix', 'jeux-marketing' ); ?>">
										<input type="number" min="0" step="1" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][packs][<?php echo (int) $i; ?>][days]" value="<?php echo (int) $pk['days']; ?>" placeholder="<?php esc_attr_e( 'Jours', 'jeux-marketing' ); ?>">
									</div>
									<input type="text" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][packs][<?php echo (int) $i; ?>][desc]" value="<?php echo esc_attr( $pk['desc'] ); ?>" placeholder="<?php esc_attr_e( 'Une phrase de résumé', 'jeux-marketing' ); ?>">
									<textarea rows="5" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][packs][<?php echo (int) $i; ?>][items]" placeholder="<?php esc_attr_e( 'Un élément inclus par ligne', 'jeux-marketing' ); ?>"><?php echo esc_textarea( $pk['items'] ); ?></textarea>
									<label class="jmk-inline"><input type="checkbox" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][packs][<?php echo (int) $i; ?>][featured]" value="1" <?php checked( 1, (int) $pk['featured'] ); ?>> <?php esc_html_e( 'Mettre ce pack en avant', 'jeux-marketing' ); ?></label>
								</div>
								<button type="button" class="jmk-del" aria-label="<?php esc_attr_e( 'Supprimer', 'jeux-marketing' ); ?>">×</button>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button jmk-add" data-add="packs"><?php esc_html_e( 'Ajouter un pack', 'jeux-marketing' ); ?></button>
				</div>

				<div class="jmk-card">
					<h2><?php esc_html_e( 'Déroulé d\'un projet', 'jeux-marketing' ); ?></h2>
					<p class="jmk-help"><?php esc_html_e( 'Les étapes sont numérotées automatiquement. Quatre suffisent.', 'jeux-marketing' ); ?></p>
					<div class="jmk-rep" data-rep="process">
						<?php foreach ( (array) jmk_get( 'process', null, $jmk_el ) as $i => $st ) : ?>
							<div class="jmk-row">
								<span class="jmk-handle">≡</span>
								<div class="jmk-row-body">
									<input type="text" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][process][<?php echo (int) $i; ?>][q]" value="<?php echo esc_attr( $st['q'] ); ?>" placeholder="<?php esc_attr_e( 'Titre de l\'étape', 'jeux-marketing' ); ?>">
									<textarea rows="3" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][process][<?php echo (int) $i; ?>][a]"><?php echo esc_textarea( $st['a'] ); ?></textarea>
								</div>
								<button type="button" class="jmk-del" aria-label="<?php esc_attr_e( 'Supprimer', 'jeux-marketing' ); ?>">×</button>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button jmk-add" data-add="process"><?php esc_html_e( 'Ajouter une étape', 'jeux-marketing' ); ?></button>
				</div>

				<div class="jmk-card">
					<h2><?php esc_html_e( 'À propos', 'jeux-marketing' ); ?></h2>
					<div class="jmk-grid">
						<?php jmk_field_l( 'about_title', __( 'Titre de la section', 'jeux-marketing' ) ); ?>
					</div>
					<div class="jmk-field">
						<label for="jmk-about_text"><?php esc_html_e( 'Texte', 'jeux-marketing' ); ?></label>
						<textarea id="jmk-about_text" rows="8" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][about_text]"><?php echo esc_textarea( jmk_get( 'about_text', null, $jmk_el ) ); ?></textarea>
						<p class="jmk-help"><?php esc_html_e( 'Séparez les paragraphes par une ligne vide.', 'jeux-marketing' ); ?></p>
					</div>

					<h3><?php esc_html_e( 'Chiffres clés', 'jeux-marketing' ); ?></h3>
					<p class="jmk-help"><?php esc_html_e( 'N\'affichez que des chiffres que vous pouvez tenir. « 48 h » engage sur un délai réel.', 'jeux-marketing' ); ?></p>
					<div class="jmk-rep" data-rep="about_stats">
						<?php foreach ( (array) jmk_get( 'about_stats', null, $jmk_el ) as $i => $stt ) : ?>
							<div class="jmk-row">
								<span class="jmk-handle">≡</span>
								<div class="jmk-row-body jmk-grid-2">
									<input type="text" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][about_stats][<?php echo (int) $i; ?>][n]" value="<?php echo esc_attr( $stt['n'] ); ?>" placeholder="<?php esc_attr_e( 'Chiffre', 'jeux-marketing' ); ?>">
									<input type="text" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][about_stats][<?php echo (int) $i; ?>][l]" value="<?php echo esc_attr( $stt['l'] ); ?>" placeholder="<?php esc_attr_e( 'Ce qu\'il désigne', 'jeux-marketing' ); ?>">
								</div>
								<button type="button" class="jmk-del" aria-label="<?php esc_attr_e( 'Supprimer', 'jeux-marketing' ); ?>">×</button>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button jmk-add" data-add="about_stats"><?php esc_html_e( 'Ajouter un chiffre', 'jeux-marketing' ); ?></button>
				</div>

				<div class="jmk-card">
					<h2><?php esc_html_e( 'Compétences', 'jeux-marketing' ); ?></h2>
					<p class="jmk-help"><?php esc_html_e( 'Un groupe par ligne de la grille : un titre, puis un outil par ligne. N\'y mettez que ce que vous savez maintenir après la livraison.', 'jeux-marketing' ); ?></p>
					<div class="jmk-rep" data-rep="skills">
						<?php foreach ( (array) jmk_get( 'skills', null, $jmk_el ) as $i => $sk ) : ?>
							<div class="jmk-row">
								<span class="jmk-handle">≡</span>
								<div class="jmk-row-body">
									<input type="text" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][skills][<?php echo (int) $i; ?>][q]" value="<?php echo esc_attr( $sk['q'] ); ?>" placeholder="<?php esc_attr_e( 'Titre du groupe', 'jeux-marketing' ); ?>">
									<textarea rows="5" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][skills][<?php echo (int) $i; ?>][a]" placeholder="<?php esc_attr_e( 'Un outil par ligne', 'jeux-marketing' ); ?>"><?php echo esc_textarea( $sk['a'] ); ?></textarea>
								</div>
								<button type="button" class="jmk-del" aria-label="<?php esc_attr_e( 'Supprimer', 'jeux-marketing' ); ?>">×</button>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button jmk-add" data-add="skills"><?php esc_html_e( 'Ajouter un groupe', 'jeux-marketing' ); ?></button>
				</div>

				<div class="jmk-card">
					<h2><?php esc_html_e( 'Réalisations', 'jeux-marketing' ); ?></h2>
					<p class="jmk-help"><?php esc_html_e( 'Les réalisations elles-mêmes s\'ajoutent dans le menu « Réalisations », avec une image et un texte. Ici, seulement l\'intitulé de la section.', 'jeux-marketing' ); ?></p>
					<div class="jmk-grid">
						<?php jmk_field_l( 'work_title', __( 'Titre de la section', 'jeux-marketing' ) ); ?>
					</div>
					<div class="jmk-field">
						<label for="jmk-work_text"><?php esc_html_e( 'Phrase d\'introduction', 'jeux-marketing' ); ?></label>
						<textarea id="jmk-work_text" rows="3" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][work_text]"><?php echo esc_textarea( jmk_get( 'work_text', null, $jmk_el ) ); ?></textarea>
					</div>
					<p>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=jmk_work' ) ); ?>" class="button"><?php esc_html_e( 'Ajouter une réalisation', 'jeux-marketing' ); ?></a>
					</p>
				</div>

				<div class="jmk-card">
					<h2><?php esc_html_e( 'Avis clients', 'jeux-marketing' ); ?></h2>
					<div class="jmk-note">
						<p><strong><?php esc_html_e( 'Vide au départ, et c\'est voulu.', 'jeux-marketing' ); ?></strong>
						<?php esc_html_e( 'Un avis inventé est un faux témoignage : sur Fiverr comme sur Upwork c\'est un motif de suspension, et un prospect qui demande à parler à la référence vous met en difficulté. N\'ajoutez ici que des phrases réellement écrites par un client. La section reste masquée tant qu\'il n\'y en a aucune.', 'jeux-marketing' ); ?></p>
					</div>
					<div class="jmk-rep" data-rep="reviews">
						<?php foreach ( (array) jmk_get( 'reviews', null, $jmk_el ) as $i => $rv ) : ?>
							<div class="jmk-row">
								<span class="jmk-handle">≡</span>
								<div class="jmk-row-body">
									<textarea rows="3" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][reviews][<?php echo (int) $i; ?>][a]" placeholder="<?php esc_attr_e( 'L\'avis, mot pour mot', 'jeux-marketing' ); ?>"><?php echo esc_textarea( $rv['a'] ); ?></textarea>
									<input type="text" name="jmk_settings[<?php echo esc_attr( $jmk_el ); ?>][reviews][<?php echo (int) $i; ?>][q]" value="<?php echo esc_attr( $rv['q'] ); ?>" placeholder="<?php esc_attr_e( 'Qui l\'a dit — prénom, marque, plateforme', 'jeux-marketing' ); ?>">
								</div>
								<button type="button" class="jmk-del" aria-label="<?php esc_attr_e( 'Supprimer', 'jeux-marketing' ); ?>">×</button>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button jmk-add" data-add="reviews"><?php esc_html_e( 'Ajouter un avis', 'jeux-marketing' ); ?></button>
				</div>
			</div>

			<?php submit_button( __( 'Enregistrer les réglages', 'jeux-marketing' ) ); ?>
		</form>
	</div>
	<?php
}

/**
 * Remise à zéro des compteurs de plafond.
 */
function jmk_reset_caps() {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'jmk_reset_caps' ) ) {
		wp_die( esc_html__( 'Action non autorisée.', 'jeux-marketing' ) );
	}
	delete_option( 'jmk_awarded' );
	wp_safe_redirect( admin_url( 'admin.php?page=jmk-settings&jmk_reset=1' ) );
	exit;
}
add_action( 'admin_post_jmk_reset_caps', 'jmk_reset_caps' );

/**
 * Message de confirmation.
 */
function jmk_reset_notice() {
	if ( isset( $_GET['jmk_reset'] ) && isset( $_GET['page'] ) && 'jmk-settings' === $_GET['page'] ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Compteurs de plafond remis à zéro.', 'jeux-marketing' ) . '</p></div>';
	}
}
add_action( 'admin_notices', 'jmk_reset_notice' );
