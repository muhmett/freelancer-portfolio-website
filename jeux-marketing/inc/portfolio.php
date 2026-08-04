<?php
/**
 * Réalisations : le contenu qui fait le portfolio.
 *
 * Une réalisation est un vrai projet livré. Elle a son propre type de
 * contenu pour pouvoir porter une image, un texte libre et quelques
 * champs chiffrés, et pour être classée sans toucher aux réglages.
 *
 * @package JeuxMarketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Type de contenu « réalisation ».
 */
function jmk_register_work_cpt() {
	register_post_type(
		'jmk_work',
		array(
			'labels'        => array(
				'name'               => __( 'Réalisations', 'jeux-marketing' ),
				'singular_name'      => __( 'Réalisation', 'jeux-marketing' ),
				'menu_name'          => __( 'Réalisations', 'jeux-marketing' ),
				'add_new'            => __( 'Ajouter', 'jeux-marketing' ),
				'add_new_item'       => __( 'Ajouter une réalisation', 'jeux-marketing' ),
				'edit_item'          => __( 'Modifier la réalisation', 'jeux-marketing' ),
				'not_found'          => __( 'Aucune réalisation pour l\'instant.', 'jeux-marketing' ),
				'search_items'       => __( 'Rechercher une réalisation', 'jeux-marketing' ),
			),
			'public'        => true,
			'has_archive'   => true,
			'show_in_menu'  => 'jmk-settings',
			'menu_position' => 20,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
			'rewrite'       => array( 'slug' => 'realisations' ),
			'show_in_rest'  => true,
		)
	);
}
add_action( 'init', 'jmk_register_work_cpt' );

/**
 * Champs d'une réalisation.
 *
 * @return array
 */
function jmk_work_fields() {
	return array(
		'client'  => __( 'Client ou marque', 'jeux-marketing' ),
		'sector'  => __( 'Secteur', 'jeux-marketing' ),
		'game'    => __( 'Jeu utilisé', 'jeux-marketing' ),
		'result'  => __( 'Résultat mesuré', 'jeux-marketing' ),
		'link'    => __( 'Lien vers la campagne', 'jeux-marketing' ),
	);
}

/**
 * Boîte de saisie.
 */
function jmk_work_metabox() {
	add_meta_box(
		'jmk_work_meta',
		__( 'Détails de la réalisation', 'jeux-marketing' ),
		'jmk_work_metabox_render',
		'jmk_work',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'jmk_work_metabox' );

/**
 * Rendu de la boîte.
 *
 * @param WP_Post $post Réalisation.
 */
function jmk_work_metabox_render( $post ) {
	wp_nonce_field( 'jmk_work_save', 'jmk_work_nonce' );
	echo '<div class="jmk-work-meta">';
	foreach ( jmk_work_fields() as $key => $label ) {
		$val = get_post_meta( $post->ID, 'jmk_' . $key, true );
		echo '<p><label for="jmk-work-' . esc_attr( $key ) . '"><strong>' . esc_html( $label ) . '</strong></label><br>';
		echo '<input type="text" class="widefat" id="jmk-work-' . esc_attr( $key ) . '" name="jmk_work[' . esc_attr( $key ) . ']" value="' . esc_attr( $val ) . '"></p>';
	}
	echo '<p class="description">'
		. esc_html__( 'Le résultat mesuré est ce qui convainc : « 1 240 emails en 3 semaines » vaut mieux que « campagne réussie ». N\'écrivez que des chiffres que vous pouvez montrer.', 'jeux-marketing' )
		. '</p></div>';
}

/**
 * Enregistrement.
 *
 * @param int $post_id Identifiant.
 */
function jmk_work_save( $post_id ) {
	if ( ! isset( $_POST['jmk_work_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['jmk_work_nonce'] ) ), 'jmk_work_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$in = isset( $_POST['jmk_work'] ) ? (array) wp_unslash( $_POST['jmk_work'] ) : array();
	foreach ( jmk_work_fields() as $key => $label ) {
		$val = isset( $in[ $key ] ) ? $in[ $key ] : '';
		$val = ( 'link' === $key ) ? esc_url_raw( $val ) : sanitize_text_field( $val );
		update_post_meta( $post_id, 'jmk_' . $key, $val );
	}
}
add_action( 'save_post_jmk_work', 'jmk_work_save' );

/**
 * Colonnes de la liste.
 *
 * @param array $cols Colonnes.
 * @return array
 */
function jmk_work_columns( $cols ) {
	$out = array();
	foreach ( $cols as $k => $v ) {
		$out[ $k ] = $v;
		if ( 'title' === $k ) {
			$out['jmk_client'] = __( 'Client', 'jeux-marketing' );
			$out['jmk_result'] = __( 'Résultat', 'jeux-marketing' );
		}
	}
	return $out;
}
add_filter( 'manage_jmk_work_posts_columns', 'jmk_work_columns' );

/**
 * Contenu des colonnes.
 *
 * @param string $col Colonne.
 * @param int    $id  Identifiant.
 */
function jmk_work_column( $col, $id ) {
	if ( 0 === strpos( $col, 'jmk_' ) ) {
		echo esc_html( get_post_meta( $id, $col, true ) );
	}
}
add_action( 'manage_jmk_work_posts_custom_column', 'jmk_work_column', 10, 2 );
