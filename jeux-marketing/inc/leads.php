<?php
/**
 * Participants, tirage côté serveur et export.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Type de contenu « participant ».
 */
function jmk_register_lead_cpt() {
	register_post_type(
		'jmk_lead',
		array(
			'labels'          => array(
				'name'          => __( 'Participants', 'jeux-marketing' ),
				'singular_name' => __( 'Participant', 'jeux-marketing' ),
				'menu_name'     => __( 'Participants', 'jeux-marketing' ),
				'all_items'     => __( 'Participants', 'jeux-marketing' ),
				'search_items'  => __( 'Rechercher un participant', 'jeux-marketing' ),
				'not_found'     => __( 'Aucun participant pour l\'instant.', 'jeux-marketing' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'jmk-settings',
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
		)
	);
}
add_action( 'init', 'jmk_register_lead_cpt' );

/**
 * Nombre de fois qu'un lot a déjà été attribué.
 *
 * @param int $index Index du lot.
 * @return int
 */
function jmk_awarded( $index ) {
	$c = get_option( 'jmk_awarded', array() );
	return isset( $c[ $index ] ) ? (int) $c[ $index ] : 0;
}

/**
 * Incrémente le compteur d'un lot.
 *
 * @param int $index Index du lot.
 */
function jmk_award( $index ) {
	$c = get_option( 'jmk_awarded', array() );
	$c[ $index ] = isset( $c[ $index ] ) ? (int) $c[ $index ] + 1 : 1;
	update_option( 'jmk_awarded', $c, false );
}

/**
 * Tirage pondéré côté serveur, plafonds respectés.
 *
 * @return array Index et données du lot.
 */
function jmk_draw() {
	$lots = array_values( (array) jmk_get( 'lots' ) );
	$pool = array();
	$sum  = 0;

	if ( empty( $lots ) ) {
		return array( 0, array( 'label' => '', 'code' => '', 'losing' => 1, 'cap' => 0, 'weight' => 0, 'hue' => 0 ) );
	}

	foreach ( $lots as $i => $lot ) {
		$cap = (int) $lot['cap'];
		if ( $cap > 0 && jmk_awarded( $i ) >= $cap ) {
			continue;
		}
		$w = (float) $lot['weight'];
		if ( $w <= 0 ) {
			continue;
		}
		$pool[ $i ] = $w;
		$sum        += $w;
	}

	if ( $sum <= 0 ) {
		$last = count( $lots ) - 1;
		return array( $last, $lots[ $last ] );
	}

	$r = ( wp_rand( 0, PHP_INT_MAX - 1 ) / PHP_INT_MAX ) * $sum;
	foreach ( $pool as $i => $w ) {
		$r -= $w;
		if ( $r <= 0 ) {
			return array( $i, $lots[ $i ] );
		}
	}

	$keys = array_keys( $pool );
	$i    = end( $keys );
	return array( $i, $lots[ $i ] );
}

/**
 * Point d'entrée AJAX : jouer une partie.
 */
function jmk_ajax_play() {
	check_ajax_referer( 'jmk_public', 'nonce' );

	list( $index, $lot ) = jmk_draw();

	$losing = ! empty( $lot['losing'] ) || '' === trim( (string) $lot['code'] );
	if ( ! $losing ) {
		jmk_award( $index );
	}

	$code = $losing ? '' : (string) $lot['code'] . '-' . strtoupper( wp_generate_password( 4, false, false ) );

	// Le résultat est mémorisé côté serveur et rendu au navigateur sous
	// forme de jeton. C'est ce jeton — pas le libellé affiché — qui sera
	// enregistré avec le participant : un formulaire bricolé ne peut donc
	// pas s'attribuer un lot qui n'a jamais été tiré.
	$token = wp_generate_password( 20, false, false );
	set_transient(
		'jmk_draw_' . $token,
		array(
			'label' => (string) $lot['label'],
			'code'  => $code,
		),
		HOUR_IN_SECONDS
	);

	wp_send_json_success(
		array(
			'index' => (int) $index,
			'label' => (string) $lot['label'],
			'code'  => $code,
			'token' => $token,
			'caps'  => get_option( 'jmk_awarded', array() ),
		)
	);
}
add_action( 'wp_ajax_jmk_play', 'jmk_ajax_play' );
add_action( 'wp_ajax_nopriv_jmk_play', 'jmk_ajax_play' );

/**
 * Point d'entrée AJAX : enregistrer un participant.
 */
function jmk_ajax_lead() {
	check_ajax_referer( 'jmk_public', 'nonce' );

	$name  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$token = isset( $_POST['token'] ) ? preg_replace( '/[^A-Za-z0-9]/', '', wp_unslash( $_POST['token'] ) ) : '';

	// Le lot enregistré vient du tirage mémorisé par le serveur, jamais de
	// ce que le navigateur affirme avoir gagné. Jeton inconnu ou déjà
	// utilisé : le participant est enregistré sans lot.
	$lot   = '';
	$code  = '';
	$drawn = $token ? get_transient( 'jmk_draw_' . $token ) : false;
	if ( is_array( $drawn ) ) {
		$lot  = isset( $drawn['label'] ) ? (string) $drawn['label'] : '';
		$code = isset( $drawn['code'] ) ? (string) $drawn['code'] : '';
		delete_transient( 'jmk_draw_' . $token );
	}

	if ( '' === $name ) {
		wp_send_json_error( array( 'message' => __( 'Indiquez un prénom.', 'jeux-marketing' ) ) );
	}
	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Cette adresse email n\'est pas valide.', 'jeux-marketing' ) ) );
	}

	// Déduplication.
	if ( jmk_get( 'one_play' ) ) {
		$dupe = get_posts(
			array(
				'post_type'      => 'jmk_lead',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => 'jmk_email',
				'meta_value'     => $email,
			)
		);
		if ( ! empty( $dupe ) ) {
			wp_send_json_error( array( 'message' => __( 'Cette adresse a déjà participé. Une seule participation par personne.', 'jeux-marketing' ) ) );
		}
	}

	$id = wp_insert_post(
		array(
			'post_type'   => 'jmk_lead',
			'post_status' => 'publish',
			'post_title'  => $email,
		),
		true
	);

	if ( is_wp_error( $id ) ) {
		wp_send_json_error( array( 'message' => __( 'Enregistrement impossible. Réessayez.', 'jeux-marketing' ) ) );
	}

	update_post_meta( $id, 'jmk_name', $name );
	update_post_meta( $id, 'jmk_email', $email );
	update_post_meta( $id, 'jmk_lot', $lot );
	update_post_meta( $id, 'jmk_code', $code );

	// Webhook.
	$hook = jmk_get( 'webhook' );
	if ( $hook ) {
		wp_remote_post(
			$hook,
			array(
				'timeout'  => 5,
				'blocking' => false,
				'headers'  => array( 'Content-Type' => 'application/json' ),
				'body'     => wp_json_encode(
					array(
						'name'  => $name,
						'email' => $email,
						'lot'   => $lot,
						'code'  => $code,
						'date'  => current_time( 'c' ),
						'site'  => home_url(),
					)
				),
			)
		);
	}

	// Notification.
	$notify = jmk_get( 'email' );
	if ( $notify && is_email( $notify ) ) {
		wp_mail(
			$notify,
			__( 'Nouveau participant', 'jeux-marketing' ),
			sprintf(
				/* translators: 1: name, 2: email, 3: prize, 4: code */
				__( "Prénom : %1\$s\nEmail : %2\$s\nLot : %3\$s\nCode : %4\$s", 'jeux-marketing' ),
				$name,
				$email,
				$lot,
				$code
			)
		);
	}

	wp_send_json_success(
		array(
			'message' => __( 'Participation enregistrée et code envoyé.', 'jeux-marketing' ),
			// Renvoyé pour que la ligne affichée dans l'aperçu soit celle
			// réellement enregistrée, et non celle annoncée par le navigateur.
			'lot'     => $lot,
			'code'    => $code,
		)
	);
}
add_action( 'wp_ajax_jmk_lead', 'jmk_ajax_lead' );
add_action( 'wp_ajax_nopriv_jmk_lead', 'jmk_ajax_lead' );

/**
 * Colonnes de la liste des participants.
 *
 * @param array $cols Colonnes.
 * @return array
 */
function jmk_lead_columns( $cols ) {
	return array(
		'cb'        => isset( $cols['cb'] ) ? $cols['cb'] : '',
		'jmk_name'  => __( 'Prénom', 'jeux-marketing' ),
		'jmk_email' => __( 'Email', 'jeux-marketing' ),
		'jmk_lot'   => __( 'Lot', 'jeux-marketing' ),
		'jmk_code'  => __( 'Code', 'jeux-marketing' ),
		'date'      => __( 'Date', 'jeux-marketing' ),
	);
}
add_filter( 'manage_jmk_lead_posts_columns', 'jmk_lead_columns' );

/**
 * Contenu des colonnes.
 *
 * @param string $col Colonne.
 * @param int    $id  Identifiant.
 */
function jmk_lead_column( $col, $id ) {
	if ( 0 === strpos( $col, 'jmk_' ) ) {
		echo esc_html( get_post_meta( $id, $col, true ) );
	}
}
add_action( 'manage_jmk_lead_posts_custom_column', 'jmk_lead_column', 10, 2 );

/**
 * Bouton d'export CSV.
 */
function jmk_export_button() {
	$screen = get_current_screen();
	if ( ! $screen || 'edit-jmk_lead' !== $screen->id ) {
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=jmk_export' ), 'jmk_export' );
	echo '<div class="notice notice-info"><p><a href="' . esc_url( $url ) . '" class="button button-primary">'
		. esc_html__( 'Exporter tous les participants en CSV', 'jeux-marketing' ) . '</a></p></div>';
}
add_action( 'admin_notices', 'jmk_export_button' );

/**
 * Export CSV.
 */
function jmk_export_csv() {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'jmk_export' ) ) {
		wp_die( esc_html__( 'Action non autorisée.', 'jeux-marketing' ) );
	}

	$leads = get_posts(
		array(
			'post_type'      => 'jmk_lead',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=participants-' . gmdate( 'Y-m-d' ) . '.csv' );

	$out = fopen( 'php://output', 'w' );
	fwrite( $out, "\xEF\xBB\xBF" ); // BOM pour Excel.
	fputcsv( $out, array( 'Date', 'Prénom', 'Email', 'Lot', 'Code' ), ';' );

	foreach ( $leads as $l ) {
		fputcsv(
			$out,
			array(
				get_the_date( 'Y-m-d H:i', $l ),
				get_post_meta( $l->ID, 'jmk_name', true ),
				get_post_meta( $l->ID, 'jmk_email', true ),
				get_post_meta( $l->ID, 'jmk_lot', true ),
				get_post_meta( $l->ID, 'jmk_code', true ),
			),
			';'
		);
	}
	fclose( $out );
	exit;
}
add_action( 'admin_post_jmk_export', 'jmk_export_csv' );
