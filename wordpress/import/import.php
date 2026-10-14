<?php
/**
 * Zet de inhoud van de statische site in WordPress.
 *
 * Gebruik (WP-CLI):  wp eval-file wordpress/import/import.php          (alles)
 *                    wp eval-file wordpress/import/import.php news     (enkel nieuwsberichten)
 * Vooraf:            python3 build_pages.py && python3 build_data.py
 *
 * Mag opnieuw gedraaid worden: bestaande items (zelfde slug) worden bijgewerkt,
 * media worden maar één keer geüpload.
 */

defined( 'ABSPATH' ) || exit;

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

define( 'SKL_IMPORT_ROOT', dirname( __DIR__, 2 ) );
$content = __DIR__ . '/content';
$data    = json_decode( file_get_contents( $content . '/data.json' ), true );

function skl_log( $msg ) {
	if ( class_exists( 'WP_CLI' ) ) {
		WP_CLI::log( $msg );
	}
}

/* ---------------------------------------------------------------- Media */

/**
 * Bijlage voor een pad in de repo of een url. Wordt bijgehouden in _skl_source.
 * Downloadpagina's van de oude site (/download/...) worden gevolgd tot het bestand.
 */
function skl_media( $source ) {
	$existing = get_posts( array(
		'post_type'   => 'attachment',
		'post_status' => 'inherit',
		'numberposts' => 1,
		'fields'      => 'ids',
		'meta_key'    => '_skl_source',
		'meta_value'  => $source,
	) );
	if ( $existing ) {
		return (int) $existing[0];
	}

	if ( preg_match( '#^https?://#', $source ) ) {
		$url = $source;
		if ( str_contains( $url, '/download/' ) ) {
			$page = wp_remote_retrieve_body( wp_remote_get( $url, array( 'timeout' => 30 ) ) );
			if ( ! preg_match( '/data-downloadurl="([^"]+)"/', $page, $m ) ) {
				skl_log( "  ! geen bestand gevonden op $url" );
				return 0;
			}
			$url = html_entity_decode( $m[1] );
		}
		$response = wp_remote_get( $url, array( 'timeout' => 60 ) );
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			skl_log( "  ! download mislukt: $url" );
			return 0;
		}
		$name = basename( wp_parse_url( $url, PHP_URL_PATH ) );
		if ( preg_match( '/filename="([^"]+)"/', (string) wp_remote_retrieve_header( $response, 'content-disposition' ), $m ) ) {
			$name = $m[1];
		}
		$tmp = wp_tempnam( $name );
		file_put_contents( $tmp, wp_remote_retrieve_body( $response ) );
	} else {
		$path = SKL_IMPORT_ROOT . '/' . $source;
		if ( ! file_exists( $path ) ) {
			skl_log( "  ! bestand ontbreekt: $source" );
			return 0;
		}
		$name = basename( $path );
		$tmp  = wp_tempnam( $name );
		copy( $path, $tmp );
	}

	$id = media_handle_sideload( array( 'name' => $name, 'tmp_name' => $tmp ), 0 );
	if ( is_wp_error( $id ) ) {
		skl_log( '  ! upload mislukt: ' . $source . ' (' . $id->get_error_message() . ')' );
		@unlink( $tmp );
		return 0;
	}
	update_post_meta( $id, '_skl_source', $source );
	skl_log( "  media: $name" );
	return (int) $id;
}

function skl_fill_placeholders( $markup ) {
	return preg_replace_callback( '/\{\{(ID|URL|FILE):([^}]+)\}\}/', function ( $m ) {
		$id = skl_media( $m[2] );
		if ( 'ID' === $m[1] ) {
			return (string) $id;
		}
		if ( 'URL' === $m[1] ) {
			return (string) wp_get_attachment_image_url( $id, 'large' );
		}
		return (string) wp_get_attachment_url( $id );
	}, $markup );
}

/* ---------------------------------------------------------------- Posts */

function skl_upsert( $type, $slug, $fields, $meta = array() ) {
	$existing = get_posts( array(
		'post_type'   => $type,
		'name'        => $slug,
		'post_status' => 'any',
		'numberposts' => 1,
	) );
	$postarr = array_merge( array(
		'post_type'   => $type,
		'post_name'   => $slug,
		'post_status' => 'publish',
		'post_author' => 1,
	), $fields );
	if ( $existing ) {
		$postarr['ID'] = $existing[0]->ID;
	}
	$id = wp_insert_post( wp_slash( $postarr ), true );
	if ( is_wp_error( $id ) ) {
		skl_log( "  ! $type $slug: " . $id->get_error_message() );
		return 0;
	}
	foreach ( $meta as $key => $value ) {
		update_post_meta( $id, $key, $value );
	}
	return $id;
}

/* ---------------------------------------------------------------- Instellingen */

skl_log( 'Instellingen' );
update_option( 'blogname', 'SK Lochristi' );
update_option( 'blogdescription', 'Voetbalclub in Lochristi' );
update_option( 'timezone_string', 'Europe/Brussels' );
update_option( 'date_format', 'j F Y' );
update_option( 'time_format', 'H:i' );
update_option( 'start_of_week', 1 );
update_option( 'posts_per_page', 12 );
update_option( 'default_comment_status', 'closed' );
update_option( 'default_ping_status', 'closed' );
update_option( 'permalink_structure', '/%postname%/' );
update_option( 'uploads_use_yearmonth_folders', 1 );

// Voorbeeldinhoud van een verse installatie.
foreach ( array( 'hello-world', 'hallo-wereld' ) as $slug ) {
	$p = get_page_by_path( $slug, OBJECT, 'post' );
	if ( $p ) {
		wp_delete_post( $p->ID, true );
	}
}
foreach ( array( 'sample-page', 'voorbeeld-pagina' ) as $slug ) {
	$p = get_page_by_path( $slug );
	if ( $p ) {
		wp_delete_post( $p->ID, true );
	}
}

/* ---------------------------------------------------------------- Nieuws */

skl_log( 'Nieuws' );
$cats = array();
foreach ( $data['categories'] as $slug => $name ) {
	$term = get_term_by( 'slug', $slug, 'category' );
	if ( ! $term ) {
		$term = get_term( wp_insert_term( $name, 'category', array( 'slug' => $slug ) )['term_id'], 'category' );
	}
	$cats[ $slug ] = (int) $term->term_id;
}
// "Geen categorie" krijgt een nette naam; het blijft de standaard.
wp_update_term( (int) get_option( 'default_category' ), 'category', array( 'name' => 'Algemeen', 'slug' => 'algemeen' ) );

foreach ( $data['news'] as $item ) {
	$id = skl_upsert( 'post', $item['slug'], array(
		'post_title'    => $item['title'],
		'post_content'  => skl_fill_placeholders( $item['content'] ),
		'post_excerpt'  => $item['excerpt'],
		'post_date'     => $item['date'] . ' 12:00:00',
		'post_category' => array( $cats[ $item['category'] ] ),
	), array(
		'auteur'  => $item['author'],
		'affiche' => $item['poster'] ? 1 : 0,
	) );
	if ( $id ) {
		set_post_thumbnail( $id, skl_media( $item['image'] ) );
		skl_log( '  ' . $item['title'] );
	}
}

if ( in_array( 'news', $args ?? array(), true ) ) {
	skl_log( 'Klaar (enkel nieuws).' );
	return;
}

/* ---------------------------------------------------------------- Evenementen, sponsors, ploegen */

skl_log( 'Evenementen' );
foreach ( $data['events'] as $event ) {
	skl_upsert( 'evenement', sanitize_title( $event['title'] ), array( 'post_title' => $event['title'] ), array(
		'datum'     => $event['date'],
		'uur'       => $event['time'],
		'locatie'   => $event['place'],
		'voorlopig' => $event['provisional'] ? 1 : 0,
	) );
}

skl_log( 'Sponsors' );
foreach ( $data['sponsors'] as $i => $sponsor ) {
	$id = skl_upsert( 'sponsor', sanitize_title( $sponsor['name'] ), array(
		'post_title' => $sponsor['name'],
		'menu_order' => $i,
	) );
	if ( $id ) {
		set_post_thumbnail( $id, skl_media( $sponsor['logo'] ) );
	}
}

skl_log( 'Ploegen' );
foreach ( $data['teams'] as $i => $team ) {
	skl_upsert( 'ploeg', sanitize_title( $team['group'] . '-' . $team['name'] ), array(
		'post_title' => $team['name'],
		'menu_order' => $i,
	), array(
		'groep' => $team['group'],
		'reeks' => $team['reeks'],
		'info'  => $team['info'],
	) );
}

skl_log( 'Documenten' );
foreach ( $data['documents'] as $i => $doc ) {
	$term = get_term_by( 'slug', $doc['rubriek'], 'rubriek' );
	if ( ! $term ) {
		$term = get_term( wp_insert_term( $doc['rubriek_name'], 'rubriek', array( 'slug' => $doc['rubriek'] ) )['term_id'], 'rubriek' );
	}
	$id = skl_upsert( 'document', $doc['slug'], array(
		'post_title'   => $doc['title'],
		'post_content' => $doc['content'],
		'menu_order'   => $doc['order'],
	), array(
		'omschrijving' => $doc['meta'],
		'pdf'          => $doc['pdf'] ? skl_media( $doc['pdf'] ) : '',
	) );
	if ( $id ) {
		wp_set_object_terms( $id, array( (int) $term->term_id ), 'rubriek' );
		skl_log( '  ' . $doc['title'] );
	}
}

/* ---------------------------------------------------------------- Pagina's */

skl_log( "Pagina's" );
$pages = array(
	'home'           => array( 'Home', '' ),
	'nieuws'         => array( 'Nieuws', 'Wedstrijdverslagen van de eerste ploeg, nieuws uit de jeugdwerking en alles wat er op en naast het veld gebeurt.' ),
	'over-de-club'   => array( 'Over de club', 'SK Lochristi: blauw-wit, twee complexen en een jeugdwerking van U6 tot U21 die de eerste ploeg voedt.' ),
	'ploegen'        => array( 'Ploegen', 'Van de U6 tot de zondagsreserven: iedereen speelt in blauw-wit.' ),
	'lid-worden'     => array( 'Lid worden', 'Wil je bij SK Lochristi voetballen? Kom eerst een teamtraining meevolgen. Zo leer je de ploeg en de trainer kennen voor je beslist.' ),
	'sponsor-worden' => array( 'Sponsor worden', 'Om onze ambities waar te maken, rekenen wij op u. Wij zijn erin geslaagd een sponsoringpakket naar ieders beurs en gading samen te stellen.' ),
	'evenementen'    => array( 'Evenementen', 'Mosselsouper, tornooien en clubfeesten. Alle activiteiten van SK Lochristi op een rij.' ),
	'documenten'     => array( 'Documenten', 'Onze visie, de charters, de clubafspraken en alle formulieren op één plek. Open een document om het hier te lezen, of download de pdf.' ),
	'contact'        => array( 'Contact', 'Wie je best contacteert, en waar je ons vindt.' ),
);
$page_ids = array();
foreach ( $pages as $slug => list( $title, $excerpt ) ) {
	$file             = $content . '/pages/' . $slug . '.html';
	$page_ids[ $slug ] = skl_upsert( 'page', $slug, array(
		'post_title'   => $title,
		'post_excerpt' => $excerpt,
		'post_content' => file_exists( $file ) ? skl_fill_placeholders( file_get_contents( $file ) ) : '',
		'menu_order'   => count( $page_ids ),
	) );
	skl_log( "  $title" );
}
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $page_ids['home'] );
update_option( 'page_for_posts', $page_ids['nieuws'] );

/* ---------------------------------------------------------------- Menu's */

skl_log( "Menu's" );
$links = skl_config( 'links' );

function skl_menu( $name, $location, $items ) {
	$menu = wp_get_nav_menu_object( $name );
	if ( $menu ) {
		foreach ( (array) wp_get_nav_menu_items( $menu->term_id ) as $old ) {
			wp_delete_post( $old->ID, true );
		}
		$menu_id = $menu->term_id;
	} else {
		$menu_id = wp_create_nav_menu( $name );
	}
	$add = function ( $items, $parent = 0 ) use ( &$add, $menu_id ) {
		foreach ( $items as $i => $item ) {
			$args = array(
				'menu-item-title'     => $item['title'],
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $parent,
				'menu-item-position'  => $i + 1,
				'menu-item-classes'   => $item['class'] ?? '',
			);
			if ( isset( $item['page'] ) ) {
				$args += array( 'menu-item-object' => 'page', 'menu-item-object-id' => $item['page'], 'menu-item-type' => 'post_type' );
			} else {
				$args += array( 'menu-item-url' => $item['url'] ?? '#', 'menu-item-type' => 'custom' );
				if ( ! empty( $item['external'] ) ) {
					$args['menu-item-target'] = '_blank';
				}
			}
			$id = wp_update_nav_menu_item( $menu_id, 0, $args );
			if ( ! empty( $item['children'] ) ) {
				$add( $item['children'], $id );
			}
		}
	};
	$add( $items );
	$locations              = get_theme_mod( 'nav_menu_locations', array() );
	$locations[ $location ] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

$page = fn( $slug ) => array( 'title' => $pages[ $slug ][0], 'page' => $page_ids[ $slug ] );

skl_menu( 'Hoofdmenu', 'hoofdmenu', array(
	array( 'title' => 'Club', 'url' => '#', 'children' => array(
		$page( 'over-de-club' ), $page( 'ploegen' ), $page( 'lid-worden' ), $page( 'sponsor-worden' ), $page( 'documenten' ),
	) ),
	$page( 'nieuws' ),
	array( 'title' => 'Kalender', 'url' => $links['kalender'], 'external' => true ),
	$page( 'evenementen' ),
	$page( 'contact' ),
	array( 'title' => 'Webshop', 'url' => $links['webshop'], 'external' => true, 'class' => 'site-nav__cta' ),
) );

skl_menu( 'Club', 'footer-club', array(
	$page( 'over-de-club' ), $page( 'ploegen' ), $page( 'lid-worden' ), $page( 'evenementen' ), $page( 'documenten' ), $page( 'sponsor-worden' ),
) );

skl_menu( 'Wedstrijdinfo', 'footer-info', array(
	array( 'title' => 'Kalender', 'url' => $links['kalender'], 'external' => true ),
	array( 'title' => 'Afgelastingen', 'url' => $links['afgelastingen'], 'external' => true ),
	array( 'title' => 'Aanduidingen', 'url' => $links['aanduidingen'], 'external' => true ),
	array( 'title' => 'Webshop', 'url' => $links['webshop'], 'external' => true ),
) );

skl_menu( 'Footer onderaan', 'footer-onderaan', array(
	$page( 'contact' ),
	$page( 'documenten' ),
	array( 'title' => 'Algemene voorwaarden', 'url' => 'https://sklochristi.be/algemene-voorwaarden/' ),
) );

/* ---------------------------------------------------------------- Site-icoon */

update_option( 'site_icon', skl_media( 'assets/img/logo.png' ) );

flush_rewrite_rules();
skl_log( 'Klaar.' );
