<?php
/**
 * Créateur de jeu.
 *
 * L'écran où l'on compose un jeu — mécanique, marque, lots, formulaire — et
 * d'où l'on repart avec quelque chose de livrable : un fichier HTML autonome,
 * ou le code d'intégration à coller sur le site du client.
 *
 * Le jeu produit tourne sur `assets/js/jmk-embed.js`, qui ne dépend ni de
 * WordPress ni d'aucune bibliothèque. L'aperçu affiché ici est *le* fichier
 * exporté, pas une imitation : les deux passent par le même squelette.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Entrée de menu.
 */
function jmk_builder_menu() {
	add_submenu_page(
		'jmk-settings',
		__( 'Créateur de jeu', 'jeux-marketing' ),
		__( 'Créateur de jeu', 'jeux-marketing' ),
		'manage_options',
		'jmk-builder',
		'jmk_builder_page'
	);
}
add_action( 'admin_menu', 'jmk_builder_menu', 11 );

/**
 * Scripts et styles du créateur.
 *
 * @param string $hook Page courante.
 */
function jmk_builder_assets( $hook ) {
	if ( false === strpos( $hook, 'jmk-builder' ) ) {
		return;
	}
	wp_enqueue_style( 'jmk-admin', JMK_URI . '/assets/css/admin.css', array(), jmk_asset_version( 'assets/css/admin.css' ) );
	wp_enqueue_script( 'jmk-builder', JMK_URI . '/assets/js/builder.js', array(), jmk_asset_version( 'assets/js/builder.js' ), true );

	wp_localize_script(
		'jmk-builder',
		'JMKB',
		array(
			'engine'  => JMK_URI . '/assets/js/jmk-embed.js?v=' . jmk_asset_version( 'assets/js/jmk-embed.js' ),
			'ajax'    => admin_url( 'admin-ajax.php' ),
			'config'  => jmk_builder_seed(),
			'games'   => jmk_builder_games(),
			// Les trois dictionnaires partent d'un coup : changer la langue du
			// jeu dans le créateur ne doit pas demander un aller-retour au
			// serveur, sinon l'aperçu accuse un temps de retard.
			'strings' => jmk_builder_all_strings(),
			'dirs'    => wp_list_pluck( jmk_langs(), 'dir' ),
			'i18n'    => array(
				'copied'   => __( 'Copié', 'jeux-marketing' ),
				'copy'     => __( 'Copier le code', 'jeux-marketing' ),
				'remove'   => __( 'Retirer', 'jeux-marketing' ),
				'unlimited' => __( 'illimité', 'jeux-marketing' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'jmk_builder_assets' );

/**
 * Les mécaniques proposées.
 *
 * @return array
 */
function jmk_builder_games() {
	return array(
		'wheel'   => array( 'label' => __( 'La roue', 'jeux-marketing' ),        'note' => __( 'Le classique. Lisible tout de suite.', 'jeux-marketing' ) ),
		'scratch' => array( 'label' => __( 'Carte à gratter', 'jeux-marketing' ), 'note' => __( 'Le geste retient le mieux sur mobile.', 'jeux-marketing' ) ),
		'tap'     => array( 'label' => __( 'Tap to win', 'jeux-marketing' ),      'note' => __( 'Trois boîtes, un choix. Le plus rapide.', 'jeux-marketing' ) ),
		'slot'    => array( 'label' => __( 'Machine à sous', 'jeux-marketing' ),  'note' => __( 'Pour une promotion qui assume le côté jeu.', 'jeux-marketing' ) ),
		'plinko'  => array( 'label' => __( 'Pluie de lots', 'jeux-marketing' ),   'note' => __( 'Le suspense dure quelques secondes de plus.', 'jeux-marketing' ) ),
		'quiz'    => array( 'label' => __( 'Quiz de marque', 'jeux-marketing' ),  'note' => __( 'Fait passer un message avant le lot.', 'jeux-marketing' ) ),
	);
}

/**
 * Les textes du jeu, dans une langue.
 *
 * Le moteur porte ses propres valeurs de repli, en français. Un jeu livré à
 * un client anglophone avec un bouton « Je joue » se remarque tout de suite :
 * le créateur envoie donc toujours le dictionnaire, pris dans les paquets de
 * langue du thème.
 *
 * @param string $lang Code de langue.
 * @return array
 */
function jmk_builder_strings( $lang ) {
	$pack = jmk_pack( $lang );
	$ui   = isset( $pack['ui'] ) ? $pack['ui'] : array();

	$pick = function ( $key, $fallback ) use ( $ui ) {
		return ( isset( $ui[ $key ] ) && '' !== $ui[ $key ] ) ? $ui[ $key ] : $fallback;
	};

	return array(
		'play'      => $pick( 'js_spin', 'Je joue' ),
		'again'     => $pick( 'game_again', 'Rejouer' ),
		'scratch'   => $pick( 'js_scratch', 'Grattez ici' ),
		'tapPick'   => $pick( 'game_pick', 'Choisissez une boîte' ),
		'win'       => $pick( 'js_win', 'Gagné :' ),
		'lose'      => $pick( 'js_lose', 'Perdu cette fois' ),
		'loseSub'   => $pick( 'js_loseSub', '' ),
		'revealed'  => $pick( 'js_revealed', 'Révélé' ),
		'name'      => $pick( 'first_name', 'Prénom' ),
		'email'     => $pick( 'email', 'Email' ),
		'phone'     => $pick( 'phone', 'Téléphone' ),
		'consent'   => $pick( 'consent_text', 'J’accepte de recevoir mon code par email.' ),
		'privacy'   => $pick( 'privacy', 'Politique de confidentialité' ),
		'send'      => $pick( 'game_send', 'Recevoir mon code' ),
		'sent'      => $pick( 'js_saved', 'C’est envoyé.' ),
		'errName'   => $pick( 'js_errName', 'Indiquez votre prénom.' ),
		'errMail'   => $pick( 'js_errMail', 'Cette adresse email semble incomplète.' ),
		'errPhone'  => $pick( 'game_err_phone', 'Indiquez un numéro de téléphone.' ),
		'errCons'   => $pick( 'js_errCons', 'Merci de cocher la case.' ),
		'errNet'    => $pick( 'game_err_net', 'Envoi impossible pour le moment.' ),
		'exhausted' => $pick( 'js_exhausted', 'épuisé' ),
		'score'     => $pick( 'js_score', 'Score : %1$s / %2$s' ),
	);
}

/**
 * Les dictionnaires des trois langues.
 *
 * @return array
 */
function jmk_builder_all_strings() {
	$all = array();
	foreach ( array_keys( jmk_langs() ) as $code ) {
		$all[ $code ] = jmk_builder_strings( $code );
	}
	return $all;
}

/**
 * Réglages de départ du créateur : ceux du site.
 *
 * Ouvrir le créateur sur les vrais lots du site évite de tout ressaisir pour
 * produire une variante, et c'est le cas le plus fréquent.
 *
 * @return array
 */
function jmk_builder_seed() {
	$lots = array();
	foreach ( (array) jmk_get( 'lots' ) as $lot ) {
		$lots[] = array(
			'label'  => (string) $lot['label'],
			'weight' => (float) $lot['weight'],
			'cap'    => ( (int) $lot['cap'] > 0 ) ? (int) $lot['cap'] : null,
			'code'   => empty( $lot['losing'] ) ? (string) $lot['code'] : '',
			'hue'    => empty( $lot['losing'] ) ? (float) $lot['hue'] : null,
			'losing' => ! empty( $lot['losing'] ),
		);
	}

	$quiz = array();
	foreach ( (array) jmk_get( 'quiz' ) as $q ) {
		$answers = array_values( array_filter( array_map( 'trim', explode( "\n", (string) $q['a'] ) ) ) );
		if ( empty( $q['q'] ) || count( $answers ) < 2 ) {
			continue;
		}
		$quiz[] = array(
			'q' => (string) $q['q'],
			'o' => $answers,
			'a' => max( 0, min( count( $answers ) - 1, (int) $q['c'] - 1 ) ),
		);
	}

	$lang = jmk_lang();

	return array(
		'game'     => 'wheel',
		'accent'   => (string) jmk_get( 'accent' ),
		'skin'     => (string) jmk_get( 'skin' ),
		'lang'     => $lang,
		'dir'      => jmk_dir(),
		'brand'    => (string) jmk_get( 'brand_name' ),
		'title'    => jmk_t( 'builder_title' ),
		'subtitle' => jmk_t( 'builder_sub' ),
		't'        => jmk_builder_strings( $lang ),
		'onePlay'  => (bool) jmk_get( 'one_play' ),
		'drawUrl'  => '',
		'leadUrl'  => '',
		'lots'     => $lots,
		'quiz'     => $quiz,
		'form'     => array(
			'on'      => true,
			'name'    => true,
			'phone'   => false,
			'consent' => true,
			'privacy' => (string) jmk_get( 'privacy_url' ),
		),
	);
}

/**
 * Nettoie une configuration reçue du navigateur.
 *
 * Tout ce qui arrive ici finit dans un fichier téléchargé : rien n'est repris
 * tel quel, pas même les libellés.
 *
 * @param array $raw Données brutes.
 * @return array
 */
function jmk_builder_sanitize( $raw ) {
	$raw   = is_array( $raw ) ? $raw : array();
	$games = jmk_builder_games();

	$game = isset( $raw['game'] ) ? sanitize_key( $raw['game'] ) : 'wheel';
	if ( ! isset( $games[ $game ] ) ) {
		$game = 'wheel';
	}

	$accent = isset( $raw['accent'] ) ? sanitize_hex_color( $raw['accent'] ) : '';

	$lots = array();
	foreach ( (array) ( isset( $raw['lots'] ) ? $raw['lots'] : array() ) as $lot ) {
		$label = isset( $lot['label'] ) ? sanitize_text_field( $lot['label'] ) : '';
		if ( '' === $label ) {
			continue;
		}
		$losing = ! empty( $lot['losing'] );
		$cap    = isset( $lot['cap'] ) ? (int) $lot['cap'] : 0;
		$lots[] = array(
			'label'  => $label,
			'weight' => max( 0, min( 1000, (float) ( isset( $lot['weight'] ) ? $lot['weight'] : 0 ) ) ),
			'cap'    => $cap > 0 ? $cap : null,
			'code'   => $losing ? '' : strtoupper( preg_replace( '/[^A-Za-z0-9\-]/', '', (string) ( isset( $lot['code'] ) ? $lot['code'] : '' ) ) ),
			'hue'    => $losing ? null : max( -180, min( 180, (float) ( isset( $lot['hue'] ) ? $lot['hue'] : 0 ) ) ),
			'losing' => $losing,
		);
	}

	$quiz = array();
	foreach ( (array) ( isset( $raw['quiz'] ) ? $raw['quiz'] : array() ) as $q ) {
		$question = isset( $q['q'] ) ? sanitize_text_field( $q['q'] ) : '';
		$options  = array();
		foreach ( (array) ( isset( $q['o'] ) ? $q['o'] : array() ) as $o ) {
			$o = sanitize_text_field( $o );
			if ( '' !== $o ) {
				$options[] = $o;
			}
		}
		if ( '' === $question || count( $options ) < 2 ) {
			continue;
		}
		$quiz[] = array(
			'q' => $question,
			'o' => $options,
			'a' => max( 0, min( count( $options ) - 1, (int) ( isset( $q['a'] ) ? $q['a'] : 0 ) ) ),
		);
	}

	$form = isset( $raw['form'] ) && is_array( $raw['form'] ) ? $raw['form'] : array();

	// La langue commande le dictionnaire et le sens de lecture. Les textes ne
	// sont pas repris du navigateur mais relus dans le paquet de langue : il
	// n'y a rien à gagner à laisser réécrire les libellés de l'interface, et
	// une traduction partielle passerait inaperçue jusque chez le client.
	$lang = isset( $raw['lang'] ) ? sanitize_key( $raw['lang'] ) : jmk_default_lang();
	$langs = jmk_langs();
	if ( ! isset( $langs[ $lang ] ) ) {
		$lang = jmk_default_lang();
	}

	return array(
		'game'     => $game,
		'accent'   => $accent ? $accent : '#D9A441',
		'skin'     => ( isset( $raw['skin'] ) && in_array( $raw['skin'], jmk_skins(), true ) ) ? $raw['skin'] : 'elegant',
		'lang'     => $lang,
		'dir'      => $langs[ $lang ]['dir'],
		't'        => jmk_builder_strings( $lang ),
		'brand'    => isset( $raw['brand'] ) ? sanitize_text_field( $raw['brand'] ) : '',
		'title'    => isset( $raw['title'] ) ? sanitize_text_field( $raw['title'] ) : '',
		'subtitle' => isset( $raw['subtitle'] ) ? sanitize_text_field( $raw['subtitle'] ) : '',
		'onePlay'  => ! empty( $raw['onePlay'] ),
		'drawUrl'  => isset( $raw['drawUrl'] ) ? esc_url_raw( $raw['drawUrl'] ) : '',
		'leadUrl'  => isset( $raw['leadUrl'] ) ? esc_url_raw( $raw['leadUrl'] ) : '',
		'lots'     => $lots,
		'quiz'     => $quiz,
		'form'     => array(
			'on'      => ! empty( $form['on'] ),
			'name'    => ! empty( $form['name'] ),
			'phone'   => ! empty( $form['phone'] ),
			'consent' => ! empty( $form['consent'] ),
			'privacy' => isset( $form['privacy'] ) ? esc_url_raw( $form['privacy'] ) : '',
		),
	);
}

/**
 * Le fichier livrable : une page qui se suffit à elle-même.
 *
 * Le moteur y est recopié plutôt que lié. C'est ce qui permet d'ouvrir le
 * fichier depuis une clé USB, une borne hors ligne, ou de le joindre à une
 * livraison Fiverr sans expliquer où poser un second fichier.
 *
 * @param array $cfg Configuration nettoyée.
 * @return string
 */
function jmk_builder_html( $cfg ) {
	$engine = file_get_contents( JMK_DIR . '/assets/js/jmk-embed.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$title  = $cfg['title'] ? $cfg['title'] : __( 'Votre jeu', 'jeux-marketing' );
	$json   = wp_json_encode( $cfg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

	// Le moteur est recopié dans la page : une balise fermante à l'intérieur
	// d'une chaîne JavaScript fermerait le <script> qui le contient.
	$engine = str_replace( '</script', '<\/script', (string) $engine );

	return '<!doctype html>' . "\n"
		. '<html lang="' . esc_attr( jmk_lang() ) . '" dir="' . esc_attr( $cfg['dir'] ) . '">' . "\n"
		. '<head>' . "\n"
		. '<meta charset="utf-8">' . "\n"
		. '<meta name="viewport" content="width=device-width,initial-scale=1">' . "\n"
		. '<title>' . esc_html( $title ) . '</title>' . "\n"
		. '<style>body{margin:0;padding:28px 16px;background:#0E0916;'
		. 'font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}</style>' . "\n"
		. '</head>' . "\n"
		. '<body>' . "\n"
		. '<div id="jeu"></div>' . "\n"
		. '<script>' . $engine . '</script>' . "\n"
		. '<script>JMKGame.mount(' . wp_json_encode( '#jeu' ) . ',' . $json . ');</script>' . "\n"
		. '</body>' . "\n"
		. '</html>' . "\n";
}

/**
 * Téléchargement du jeu composé.
 */
function jmk_builder_export() {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'jmk_build' ) ) {
		wp_die( esc_html__( 'Action non autorisée.', 'jeux-marketing' ) );
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON décodé puis nettoyé par jmk_builder_sanitize().
	$raw = isset( $_POST['config'] ) ? json_decode( wp_unslash( $_POST['config'] ), true ) : array();
	$cfg = jmk_builder_sanitize( $raw );

	$slug = sanitize_title( $cfg['brand'] ? $cfg['brand'] : 'jeu' );
	$name = $slug . '-' . $cfg['game'] . '.html';

	nocache_headers();
	header( 'Content-Type: text/html; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . $name );
	echo jmk_builder_html( $cfg ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- page complète, échappée à la construction.
	exit;
}
add_action( 'admin_post_jmk_build_export', 'jmk_builder_export' );

/**
 * Reprend la configuration du créateur dans les réglages du site.
 *
 * Seuls les réglages qui existent des deux côtés sont repris : lots, couleur,
 * habillage, jeu mis en avant. Les textes du créateur restent au créateur —
 * ceux du site sont traduits par langue, et les écraser depuis ici effacerait
 * les deux autres.
 */
function jmk_builder_apply() {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'jmk_build' ) ) {
		wp_die( esc_html__( 'Action non autorisée.', 'jeux-marketing' ) );
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON décodé puis nettoyé par jmk_builder_sanitize().
	$raw = isset( $_POST['config'] ) ? json_decode( wp_unslash( $_POST['config'] ), true ) : array();
	$cfg = jmk_builder_sanitize( $raw );

	$saved = get_option( 'jmk_settings', array() );
	$saved = is_array( $saved ) ? $saved : array();

	$lots = array();
	foreach ( $cfg['lots'] as $lot ) {
		$lots[] = array(
			'label'  => $lot['label'],
			'weight' => $lot['weight'],
			'cap'    => null === $lot['cap'] ? 0 : $lot['cap'],
			'code'   => $lot['code'],
			'hue'    => null === $lot['hue'] ? 0 : $lot['hue'],
			'losing' => $lot['losing'] ? 1 : 0,
		);
	}

	$lang = jmk_admin_lang();
	if ( ! isset( $saved[ $lang ] ) || ! is_array( $saved[ $lang ] ) ) {
		$saved[ $lang ] = array();
	}
	$saved[ $lang ]['lots'] = $lots;
	$saved['accent']        = $cfg['accent'];
	$saved['skin']          = $cfg['skin'];
	$saved['one_play']      = $cfg['onePlay'] ? 1 : 0;

	update_option( 'jmk_settings', $saved );

	wp_safe_redirect( add_query_arg( 'jmk_applied', '1', admin_url( 'admin.php?page=jmk-builder' ) ) );
	exit;
}
add_action( 'admin_post_jmk_build_apply', 'jmk_builder_apply' );

/**
 * L'écran du créateur.
 */
function jmk_builder_page() {
	$games = jmk_builder_games();
	?>
	<div class="wrap jmk-wrap jmk-builder">
		<h1><?php esc_html_e( 'Créateur de jeu', 'jeux-marketing' ); ?></h1>

		<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- simple accusé de réception. ?>
		<?php if ( isset( $_GET['jmk_applied'] ) ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Les lots et la marque du créateur sont maintenant ceux du site.', 'jeux-marketing' ); ?></p>
			</div>
		<?php endif; ?>

		<p class="jmk-lead">
			<?php esc_html_e( 'Composez le jeu à gauche, il se met à jour à droite. Ce que vous voyez dans l’aperçu est exactement le fichier que vous téléchargez : même moteur, même réglages.', 'jeux-marketing' ); ?>
		</p>

		<div class="jmk-build">

			<div class="jmk-build-form">

				<section class="jmk-panel">
					<h2><?php esc_html_e( '1. La mécanique', 'jeux-marketing' ); ?></h2>
					<div class="jmk-picks" id="jmkGames">
						<?php foreach ( $games as $key => $g ) : ?>
							<label class="jmk-pick">
								<input type="radio" name="jmk_game" value="<?php echo esc_attr( $key ); ?>">
								<span class="jmk-pick-in">
									<strong><?php echo esc_html( $g['label'] ); ?></strong>
									<em><?php echo esc_html( $g['note'] ); ?></em>
								</span>
							</label>
						<?php endforeach; ?>
					</div>
				</section>

				<section class="jmk-panel">
					<h2><?php esc_html_e( '2. La marque', 'jeux-marketing' ); ?></h2>
					<p class="jmk-row">
						<label for="jmkBrand"><?php esc_html_e( 'Nom du client', 'jeux-marketing' ); ?></label>
						<input type="text" id="jmkBrand" class="regular-text">
					</p>
					<p class="jmk-row">
						<label for="jmkTitle"><?php esc_html_e( 'Titre affiché', 'jeux-marketing' ); ?></label>
						<input type="text" id="jmkTitle" class="regular-text">
					</p>
					<p class="jmk-row">
						<label for="jmkSub"><?php esc_html_e( 'Sous-titre', 'jeux-marketing' ); ?></label>
						<input type="text" id="jmkSub" class="regular-text">
					</p>
					<p class="jmk-row">
						<label for="jmkAccent"><?php esc_html_e( 'Couleur', 'jeux-marketing' ); ?></label>
						<span class="jmk-inline">
							<input type="color" id="jmkAccent">
							<span class="jmk-swatches" id="jmkSwatches"></span>
						</span>
					</p>
					<p class="jmk-row">
						<label for="jmkSkin"><?php esc_html_e( 'Style visuel', 'jeux-marketing' ); ?></label>
						<select id="jmkSkin">
							<option value="elegant"><?php esc_html_e( 'Sobre', 'jeux-marketing' ); ?></option>
							<option value="arcade"><?php esc_html_e( 'Fête foraine', 'jeux-marketing' ); ?></option>
							<option value="casino"><?php esc_html_e( 'Casino', 'jeux-marketing' ); ?></option>
						</select>
					</p>
					<p class="jmk-row">
						<label for="jmkLang"><?php esc_html_e( 'Langue du jeu', 'jeux-marketing' ); ?></label>
						<select id="jmkLang">
							<?php foreach ( jmk_langs() as $code => $l ) : ?>
								<option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $l['name'] ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<p class="description">
						<?php esc_html_e( 'Elle commande les libellés du jeu — bouton, formulaire, messages — et le sens de lecture. L’arabe bascule le jeu de droite à gauche.', 'jeux-marketing' ); ?>
					</p>
				</section>

				<section class="jmk-panel">
					<h2><?php esc_html_e( '3. Les lots', 'jeux-marketing' ); ?></h2>
					<p class="description">
						<?php esc_html_e( 'Le poids est une part relative, pas un pourcentage : 40 face à 10 sort quatre fois plus souvent. Un plafond à 0 veut dire illimité.', 'jeux-marketing' ); ?>
					</p>
					<table class="widefat jmk-lots" id="jmkLots">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Libellé', 'jeux-marketing' ); ?></th>
								<th class="num"><?php esc_html_e( 'Poids', 'jeux-marketing' ); ?></th>
								<th class="num"><?php esc_html_e( 'Part', 'jeux-marketing' ); ?></th>
								<th class="num"><?php esc_html_e( 'Plafond', 'jeux-marketing' ); ?></th>
								<th><?php esc_html_e( 'Code', 'jeux-marketing' ); ?></th>
								<th class="num"><?php esc_html_e( 'Teinte', 'jeux-marketing' ); ?></th>
								<th class="num"><?php esc_html_e( 'Perdant', 'jeux-marketing' ); ?></th>
								<th></th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
					<p>
						<button type="button" class="button" id="jmkAddLot"><?php esc_html_e( 'Ajouter un lot', 'jeux-marketing' ); ?></button>
					</p>
				</section>

				<section class="jmk-panel" id="jmkQuizPanel" hidden>
					<h2><?php esc_html_e( '4. Les questions', 'jeux-marketing' ); ?></h2>
					<p class="description">
						<?php esc_html_e( 'Une ligne par réponse. Cochez la bonne.', 'jeux-marketing' ); ?>
					</p>
					<div id="jmkQuiz"></div>
					<p>
						<button type="button" class="button" id="jmkAddQ"><?php esc_html_e( 'Ajouter une question', 'jeux-marketing' ); ?></button>
					</p>
				</section>

				<section class="jmk-panel">
					<h2><?php esc_html_e( '5. Le formulaire', 'jeux-marketing' ); ?></h2>
					<p class="description">
						<?php esc_html_e( 'Il apparaît une fois la partie jouée : le lot est déjà gagné, il ne reste plus qu’à savoir où l’envoyer. C’est ce qui fait la différence de rendement avec un pop-up.', 'jeux-marketing' ); ?>
					</p>
					<p><label><input type="checkbox" id="jmkFormOn"> <?php esc_html_e( 'Demander un contact', 'jeux-marketing' ); ?></label></p>
					<p><label><input type="checkbox" id="jmkFormName"> <?php esc_html_e( 'Champ prénom', 'jeux-marketing' ); ?></label></p>
					<p><label><input type="checkbox" id="jmkFormPhone"> <?php esc_html_e( 'Champ téléphone', 'jeux-marketing' ); ?></label></p>
					<p><label><input type="checkbox" id="jmkFormConsent"> <?php esc_html_e( 'Case de consentement', 'jeux-marketing' ); ?></label></p>
					<p class="jmk-row">
						<label for="jmkPrivacy"><?php esc_html_e( 'Lien politique de confidentialité', 'jeux-marketing' ); ?></label>
						<input type="url" id="jmkPrivacy" class="regular-text" placeholder="https://">
					</p>
					<p><label><input type="checkbox" id="jmkOnePlay"> <?php esc_html_e( 'Une seule partie par visiteur', 'jeux-marketing' ); ?></label></p>
				</section>

				<section class="jmk-panel">
					<h2><?php esc_html_e( '6. Où vont les participants', 'jeux-marketing' ); ?></h2>
					<p class="jmk-row">
						<label for="jmkLead"><?php esc_html_e( 'Adresse de réception', 'jeux-marketing' ); ?></label>
						<input type="url" id="jmkLead" class="regular-text" placeholder="https://">
					</p>
					<p>
						<button type="button" class="button" id="jmkUseSite">
							<?php esc_html_e( 'Utiliser ce site WordPress', 'jeux-marketing' ); ?>
						</button>
					</p>
					<p class="description">
						<?php esc_html_e( 'Laissée vide, l’adresse ne fait rien partir : le jeu reste une démonstration, et rien n’est enregistré. Renseignée avec ce site, chaque participant arrive dans Jeux Marketing → Participants, et le tirage passe côté serveur — c’est la seule façon de faire respecter un plafond entre plusieurs visiteurs.', 'jeux-marketing' ); ?>
					</p>
					<p><label><input type="checkbox" id="jmkServerDraw"> <?php esc_html_e( 'Tirage côté serveur (plafonds partagés)', 'jeux-marketing' ); ?></label></p>
					<p class="description jmk-warn" id="jmkDrawWarn" hidden>
						<?php esc_html_e( 'Sans tirage serveur, les plafonds sont comptés dans le navigateur du visiteur : chacun repart avec son propre compteur. Suffisant pour une démonstration, pas pour un stock réellement limité.', 'jeux-marketing' ); ?>
					</p>
				</section>

			</div>

			<div class="jmk-build-side">
				<div class="jmk-sticky">

					<h2><?php esc_html_e( 'Aperçu', 'jeux-marketing' ); ?></h2>
					<iframe id="jmkPreview" title="<?php esc_attr_e( 'Aperçu du jeu', 'jeux-marketing' ); ?>"></iframe>
					<p>
						<button type="button" class="button" id="jmkReplay"><?php esc_html_e( 'Rejouer l’aperçu', 'jeux-marketing' ); ?></button>
					</p>

					<h2><?php esc_html_e( 'Emporter le jeu', 'jeux-marketing' ); ?></h2>

					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="jmk-take">
						<?php wp_nonce_field( 'jmk_build' ); ?>
						<input type="hidden" name="action" value="jmk_build_export">
						<input type="hidden" name="config" class="jmk-cfg" value="">
						<button type="submit" class="button button-primary">
							<?php esc_html_e( 'Télécharger le fichier du jeu', 'jeux-marketing' ); ?>
						</button>
					</form>
					<p class="description">
						<?php esc_html_e( 'Un seul fichier HTML, moteur compris. Il s’ouvre par double-clic, sans serveur — c’est le fichier à joindre à une livraison.', 'jeux-marketing' ); ?>
					</p>

					<h3><?php esc_html_e( 'Ou le poser sur un site existant', 'jeux-marketing' ); ?></h3>
					<pre class="jmk-embed" id="jmkEmbed"></pre>
					<p>
						<button type="button" class="button" id="jmkCopy"><?php esc_html_e( 'Copier le code', 'jeux-marketing' ); ?></button>
					</p>

					<h3><?php esc_html_e( 'Ou l’appliquer à ce site', 'jeux-marketing' ); ?></h3>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="jmk-take">
						<?php wp_nonce_field( 'jmk_build' ); ?>
						<input type="hidden" name="action" value="jmk_build_apply">
						<input type="hidden" name="config" class="jmk-cfg" value="">
						<button type="submit" class="button">
							<?php esc_html_e( 'Reprendre ces lots sur le site', 'jeux-marketing' ); ?>
						</button>
					</form>
					<p class="description">
						<?php esc_html_e( 'Reprend les lots, la couleur et le style visuel dans les réglages du site. Les textes ne sont pas touchés : ils sont traduits par langue.', 'jeux-marketing' ); ?>
					</p>

				</div>
			</div>

		</div>
	</div>
	<?php
}
