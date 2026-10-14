<?php
/**
 * Inhoudstypes die de club zelf beheert: ploegen, evenementen, sponsors en documenten.
 * Nieuws gebruikt de gewone berichten.
 */

defined( 'ABSPATH' ) || exit;

function skl_labels( $singular, $plural ) {
	return array(
		'name'               => $plural,
		'singular_name'      => $singular,
		'menu_name'          => $plural,
		'add_new'            => 'Nieuwe toevoegen',
		'add_new_item'       => $singular . ' toevoegen',
		'edit_item'          => $singular . ' bewerken',
		'new_item'           => 'Nieuw: ' . strtolower( $singular ),
		'view_item'          => $singular . ' bekijken',
		'search_items'       => $plural . ' zoeken',
		'not_found'          => 'Niets gevonden',
		'not_found_in_trash' => 'Niets gevonden in de prullenbak',
		'all_items'          => 'Alle ' . strtolower( $plural ),
	);
}

add_action( 'init', function () {
	$common = array(
		'public'             => false,
		'show_ui'            => true,
		'show_in_rest'       => true,
		'publicly_queryable' => false,
		'has_archive'        => false,
		'rewrite'            => false,
	);

	register_post_type( 'ploeg', $common + array(
		'labels'        => skl_labels( 'Ploeg', 'Ploegen' ),
		'menu_icon'     => 'dashicons-groups',
		'menu_position' => 20,
		'supports'      => array( 'title', 'page-attributes' ),
	) );

	register_post_type( 'evenement', $common + array(
		'labels'        => skl_labels( 'Evenement', 'Evenementen' ),
		'menu_icon'     => 'dashicons-calendar-alt',
		'menu_position' => 21,
		'supports'      => array( 'title' ),
	) );

	register_post_type( 'sponsor', $common + array(
		'labels'        => skl_labels( 'Sponsor', 'Sponsors' ),
		'menu_icon'     => 'dashicons-awards',
		'menu_position' => 22,
		'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
	) );

	register_post_type( 'document', $common + array(
		'labels'        => skl_labels( 'Document', 'Documenten' ),
		'menu_icon'     => 'dashicons-media-document',
		'menu_position' => 23,
		'supports'      => array( 'title', 'editor', 'page-attributes', 'custom-fields' ),
	) );

	register_taxonomy( 'rubriek', 'document', array(
		'labels'            => skl_labels( 'Rubriek', 'Rubrieken' ),
		'public'            => false,
		'show_ui'           => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'hierarchical'      => true,
		'rewrite'           => false,
	) );

	// Pagina's krijgen een samenvatting: die tekst staat onder de titel bovenaan de pagina.
	add_post_type_support( 'page', 'excerpt' );
} );

// Logo's: sponsors gebruiken de uitgelichte afbeelding als logo.
add_filter( 'admin_post_thumbnail_html', function ( $html, $post_id ) {
	if ( 'sponsor' === get_post_type( $post_id ) ) {
		$html = str_replace( array( 'Uitgelichte afbeelding instellen', 'Set featured image' ), 'Logo kiezen', $html );
	}
	return $html;
}, 10, 2 );

// Lijsten in de admin volgen dezelfde volgorde als op de site.
add_action( 'pre_get_posts', function ( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || $query->get( 'orderby' ) ) {
		return;
	}
	$type = $query->get( 'post_type' );
	if ( in_array( $type, array( 'ploeg', 'sponsor', 'document' ), true ) ) {
		$query->set( 'orderby', 'menu_order title' );
		$query->set( 'order', 'ASC' );
	}
	if ( 'evenement' === $type ) {
		$query->set( 'meta_key', 'datum' );
		$query->set( 'orderby', 'meta_value' );
		$query->set( 'order', 'ASC' );
	}
} );

// Extra kolommen in de admin.
add_filter( 'manage_ploeg_posts_columns', fn( $cols ) => array_slice( $cols, 0, 2 ) + array( 'groep' => 'Groep', 'reeks' => 'Reeks / leeftijd' ) + $cols );
add_action( 'manage_ploeg_posts_custom_column', function ( $col, $id ) {
	if ( 'groep' === $col ) {
		echo esc_html( ucfirst( (string) get_post_meta( $id, 'groep', true ) ) );
	}
	if ( 'reeks' === $col ) {
		echo esc_html( (string) get_post_meta( $id, 'reeks', true ) );
	}
}, 10, 2 );

add_filter( 'manage_evenement_posts_columns', fn( $cols ) => array_slice( $cols, 0, 2 ) + array( 'datum' => 'Datum', 'locatie' => 'Locatie' ) + $cols );
add_action( 'manage_evenement_posts_custom_column', function ( $col, $id ) {
	if ( 'datum' === $col ) {
		$date = skl_meta_date( $id, 'datum' );
		echo esc_html( $date ? wp_date( 'l j F Y', $date ) : '' );
	}
	if ( 'locatie' === $col ) {
		echo esc_html( (string) get_post_meta( $id, 'locatie', true ) );
	}
}, 10, 2 );

/**
 * Datumveld van ACF (opgeslagen als Ymd) als timestamp om 12u, of null.
 */
function skl_meta_date( $post_id, $key ) {
	$raw = (string) get_post_meta( $post_id, $key, true );
	if ( ! preg_match( '/^\d{8}$/', $raw ) ) {
		return null;
	}
	$date = DateTimeImmutable::createFromFormat( 'Ymd H:i', $raw . ' 12:00', wp_timezone() );
	return $date ? $date->getTimestamp() : null;
}

// Velden (ACF). Staan in code zodat ze mee in git zitten.
add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
		'key'      => 'group_skl_ploeg',
		'title'    => 'Ploeg',
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'ploeg' ) ) ),
		'position' => 'acf_after_title',
		'fields'   => array(
			array(
				'key'           => 'field_skl_ploeg_groep',
				'name'          => 'groep',
				'label'         => 'Groep',
				'type'          => 'button_group',
				'choices'       => array( 'seniors' => 'Seniors', 'jeugd' => 'Jeugd' ),
				'default_value' => 'seniors',
				'required'      => 1,
			),
			array(
				'key'          => 'field_skl_ploeg_reeks',
				'name'         => 'reeks',
				'label'        => 'Reeks of leeftijd',
				'type'         => 'text',
				'instructions' => 'Staat rechts naast de naam. Bv. "3de provinciale C" of "U8 tot U12".',
			),
			array(
				'key'          => 'field_skl_ploeg_info',
				'name'         => 'info',
				'label'        => 'Extra info',
				'type'         => 'text',
				'instructions' => 'Kleine regel onder de naam. Bv. trainer, trainingsuren of coördinator. Mag leeg blijven.',
			),
		),
	) );

	acf_add_local_field_group( array(
		'key'      => 'group_skl_evenement',
		'title'    => 'Evenement',
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'evenement' ) ) ),
		'position' => 'acf_after_title',
		'fields'   => array(
			array(
				'key'            => 'field_skl_evenement_datum',
				'name'           => 'datum',
				'label'          => 'Datum',
				'type'           => 'date_picker',
				'display_format' => 'd/m/Y',
				'return_format'  => 'Ymd',
				'first_day'      => 1,
				'required'       => 1,
				'instructions'   => 'Voorbije evenementen verdwijnen vanzelf van de site.',
			),
			array(
				'key'          => 'field_skl_evenement_uur',
				'name'         => 'uur',
				'label'        => 'Uur',
				'type'         => 'text',
				'instructions' => 'Bv. "19u" of "Bbq vanaf 19u30".',
			),
			array(
				'key'   => 'field_skl_evenement_locatie',
				'name'  => 'locatie',
				'label' => 'Locatie',
				'type'  => 'text',
			),
			array(
				'key'          => 'field_skl_evenement_voorlopig',
				'name'         => 'voorlopig',
				'label'        => 'Voorlopige datum',
				'type'         => 'true_false',
				'ui'           => 1,
				'instructions' => 'Aan = onder de lijst staat dat de datum nog bevestigd wordt.',
			),
		),
	) );

	acf_add_local_field_group( array(
		'key'      => 'group_skl_sponsor',
		'title'    => 'Sponsor',
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'sponsor' ) ) ),
		'position' => 'acf_after_title',
		'fields'   => array(
			array(
				'key'          => 'field_skl_sponsor_website',
				'name'         => 'website',
				'label'        => 'Website',
				'type'         => 'url',
				'instructions' => 'Mag leeg blijven. Het logo kies je rechts bij "Logo".',
			),
		),
	) );

} );

// Nieuwsberichten: auteur en affiche worden ingevuld via de berichtkaart in de editor.
add_action( 'init', function () {
	$auth = fn() => current_user_can( 'edit_posts' );
	register_post_meta( 'post', 'auteur', array( 'type' => 'string', 'single' => true, 'show_in_rest' => true, 'default' => '', 'auth_callback' => $auth ) );
	register_post_meta( 'document', 'omschrijving', array( 'type' => 'string', 'single' => true, 'show_in_rest' => true, 'default' => '', 'auth_callback' => $auth ) );
	register_post_meta( 'document', 'pdf', array( 'type' => 'integer', 'single' => true, 'show_in_rest' => true, 'default' => 0, 'auth_callback' => $auth ) );
	register_post_meta( 'post', 'affiche', array( 'type' => 'boolean', 'single' => true, 'show_in_rest' => true, 'default' => false, 'auth_callback' => $auth ) );
} );
