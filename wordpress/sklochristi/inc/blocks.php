<?php
/**
 * Eigen blokken. Ze worden op de server opgebouwd, zodat inhoud uit Ploegen,
 * Evenementen, Sponsors, Documenten en Nieuws altijd actueel is.
 * De editor-kant staat in assets/js/blocks-editor.js.
 */

defined( 'ABSPATH' ) || exit;

function skl_blocks() {
	return array(
		'site-header'  => array( 'render' => 'skl_render_site_header' ),
		'site-footer'  => array( 'render' => 'skl_render_site_footer' ),
		'page-hero'    => array( 'render' => 'skl_block_page_hero' ),
		'hero'         => array(
			'render'     => 'skl_block_hero',
			'attributes' => array(
				'imageId'    => array( 'type' => 'number', 'default' => 0 ),
				'leftTitle'  => array( 'type' => 'string', 'default' => '3de provinciale C' ),
				'leftText'   => array( 'type' => 'string', 'default' => 'Eerste ploeg, seizoen 2026–2027' ),
				'rightTitle' => array( 'type' => 'string', 'default' => 'Stamnummer 5617' ),
				'rightText'  => array( 'type' => 'string', 'default' => 'Complexen Bosdreef en Beerveldsebaan' ),
			),
		),
		'shortcuts'    => array( 'render' => 'skl_block_shortcuts' ),
		'latest-news'  => array( 'render' => 'skl_block_latest_news' ),
		'news-feed'    => array( 'render' => 'skl_block_news_feed' ),
		'article-head' => array( 'render' => 'skl_block_article_head' ),
		'article-share' => array( 'render' => 'skl_block_article_share' ),
		'post-setup'   => array( 'render' => '__return_empty_string' ),
		'document-setup' => array( 'render' => '__return_empty_string' ),
		'more-news'    => array( 'render' => 'skl_block_more_news' ),
		'events'       => array( 'render' => 'skl_block_events' ),
		'sponsors'     => array(
			'render'     => 'skl_block_sponsors',
			'attributes' => array(
				'limit' => array( 'type' => 'number', 'default' => 0 ),
				'cta'   => array( 'type' => 'boolean', 'default' => false ),
			),
		),
		'team-list'    => array(
			'render'     => 'skl_block_team_list',
			'attributes' => array( 'groep' => array( 'type' => 'string', 'default' => 'seniors' ) ),
		),
		'documents'    => array(
			'render'     => 'skl_block_documents',
			'attributes' => array( 'rubriek' => array( 'type' => 'string', 'default' => '' ) ),
		),
		'person'       => array(
			'render'     => 'skl_block_person',
			'attributes' => array(
				'name'    => array( 'type' => 'string', 'default' => '' ),
				'role'    => array( 'type' => 'string', 'default' => '' ),
				'email'   => array( 'type' => 'string', 'default' => '' ),
				'imageId' => array( 'type' => 'number', 'default' => 0 ),
			),
		),
	);
}

add_action( 'init', function () {
	$asset = get_theme_file_path( 'assets/js/blocks-editor.js' );
	wp_register_script(
		'skl-blocks-editor',
		get_theme_file_uri( 'assets/js/blocks-editor.js' ),
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-data', 'wp-core-data', 'wp-dom-ready' ),
		file_exists( $asset ) ? (string) filemtime( $asset ) : null,
		true
	);

	wp_add_inline_script( 'skl-blocks-editor', 'window.sklEditor = ' . wp_json_encode( array( 'adminUrl' => admin_url() ) ) . ';', 'before' );

	foreach ( skl_blocks() as $name => $block ) {
		register_block_type( 'skl/' . $name, array(
			'api_version'          => 3,
			'attributes'           => ( $block['attributes'] ?? array() ) + array( 'className' => array( 'type' => 'string' ) ),
			'render_callback'      => fn( $attrs ) => call_user_func( $block['render'], $attrs ),
			'editor_script_handles' => array( 'skl-blocks-editor' ),
			'supports'             => array( 'html' => false ),
		) );
	}
} );

add_filter( 'block_categories_all', function ( $categories ) {
	array_unshift( $categories, array( 'slug' => 'sklochristi', 'title' => 'SK Lochristi' ) );
	return $categories;
} );

/* ---------- Paginatitel ---------- */

function skl_block_page_hero() {
	$id    = is_home() ? (int) get_option( 'page_for_posts' ) : get_queried_object_id();
	$title = $id ? get_the_title( $id ) : 'Paginatitel';
	$intro = $id && has_excerpt( $id ) ? get_the_excerpt( $id ) : '';
	$long  = mb_strlen( $title ) > 9 ? ' page-hero--long' : '';

	return '<section class="page-hero' . $long . '" aria-labelledby="page-title">'
		. '<svg class="page-hero__lines" viewBox="0 0 400 400" aria-hidden="true"><circle cx="200" cy="200" r="150"/><line x1="200" y1="-200" x2="200" y2="600"/><circle cx="200" cy="200" r="4" style="fill:currentColor"/></svg>'
		. '<div class="wrap"><h1 id="page-title">' . esc_html( $title ) . '</h1>'
		. ( $intro ? '<p>' . esc_html( $intro ) . '</p>' : '' )
		. '</div></section>';
}

/* ---------- Home ---------- */

function skl_block_hero( $a ) {
	$photo = $a['imageId']
		? wp_get_attachment_image( $a['imageId'], 'full', false, array( 'class' => 'hero__photo', 'alt' => '', 'fetchpriority' => 'high', 'loading' => false, 'sizes' => '100vw' ) )
		: '';

	return '<section class="hero" aria-labelledby="hero-title">' . $photo
		. '<div class="hero__inner"><h1 class="hero__title" id="hero-title"><span class="hero__crest">'
		. '<svg class="hero__ring" viewBox="0 0 100 100" aria-hidden="true"><circle cx="50" cy="50" r="49.4" pathLength="100" transform="rotate(-90 50 50)"/></svg>'
		. '<img src="' . esc_url( skl_asset( 'img/crest.png' ) ) . '" alt="Clubwapen ' . esc_attr( get_bloginfo( 'name' ) ) . '" width="509" height="640"></span>'
		. '<span class="hero__name">' . esc_html( get_bloginfo( 'name' ) ) . '</span></h1>'
		. '<div class="hero__meta">'
		. '<div><strong>' . esc_html( $a['leftTitle'] ) . '</strong><small>' . esc_html( $a['leftText'] ) . '</small></div>'
		. '<div><strong>' . esc_html( $a['rightTitle'] ) . '</strong><small>' . esc_html( $a['rightText'] ) . '</small></div>'
		. '</div></div></section>';
}

function skl_block_shortcuts() {
	$links = skl_config( 'links' );
	$ext   = '<svg class="shortcut__ext" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M7 17L17 7M9 7h8v8"/></svg><span class="visually-hidden">(opent in een nieuw venster)</span>';
	$card  = function ( $url, $icon, $title, $desc, $external ) use ( $ext ) {
		return '<a class="shortcut" href="' . esc_url( $url ) . '"' . ( $external ? ' target="_blank" rel="noopener"' : '' ) . '>'
			. '<span class="shortcut__icon" aria-hidden="true">' . skl_icon( $icon ) . '</span>'
			. '<span class="shortcut__title">' . esc_html( $title ) . '</span>'
			. '<span class="shortcut__desc">' . esc_html( $desc ) . '</span>'
			. ( $external ? $ext : '' ) . '</a>';
	};
	$events = get_page_by_path( 'evenementen' );

	return '<section class="shortcuts" aria-labelledby="shortcuts-title"><div class="wrap">'
		. '<h2 id="shortcuts-title" class="visually-hidden">Wedstrijdinfo</h2>'
		. '<nav class="shortcuts__bar" aria-label="Wedstrijdinfo">'
		. $card( $links['kalender'], 'calendar', 'Kalender', 'Wedstrijden van alle ploegen', true )
		. $card( $events ? get_permalink( $events ) : home_url( '/evenementen/' ), 'star', 'Evenementen en activiteiten', 'Mosselsouper, tornooien en clubfeesten', false )
		. $card( $links['webshop'], 'bag', 'Webshop', 'Clubkledij en supportersartikelen', true )
		. '</nav></div></section>';
}

/* ---------- Nieuws ---------- */

function skl_block_latest_news() {
	$posts = get_posts( array( 'numberposts' => 3 ) );
	if ( ! $posts ) {
		return '<p class="feed-empty">Nog geen nieuws.</p>';
	}
	$side = '';
	foreach ( array_slice( $posts, 1 ) as $post ) {
		$side .= skl_story_card( $post, 'row' );
	}
	return '<div class="news-lead">' . skl_story_card( $posts[0] ) . '<div class="news-lead__side">' . $side . '</div></div>';
}

add_filter( 'query_vars', function ( $vars ) {
	$vars[] = 'categorie';
	return $vars;
} );

add_action( 'pre_get_posts', function ( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_home() ) {
		return;
	}
	$cat = $query->get( 'categorie' );
	if ( in_array( $cat, array( 'seniors', 'jeugd' ), true ) ) {
		$query->set( 'category_name', $cat );
	}
} );

function skl_block_news_feed() {
	global $wp_query;
	$base    = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' );
	$current = get_query_var( 'categorie' );
	$current = in_array( $current, array( 'seniors', 'jeugd' ), true ) ? $current : 'alles';

	$pills = '';
	foreach ( array( 'alles' => 'Alles', 'seniors' => 'Seniors', 'jeugd' => 'Jeugd' ) as $slug => $label ) {
		if ( 'alles' === $slug ) {
			$count = (int) wp_count_posts()->publish;
			$url   = $base;
		} else {
			$term  = get_category_by_slug( $slug );
			$count = $term ? (int) $term->count : 0;
			$url   = add_query_arg( 'categorie', $slug, $base );
		}
		$pills .= '<a class="pill" href="' . esc_url( $url ) . '"' . ( $current === $slug ? ' aria-current="page"' : '' ) . '>'
			. esc_html( $label ) . ' <span class="count">' . $count . '</span></a>';
	}

	$cards = '';
	if ( $wp_query instanceof WP_Query && $wp_query->is_home() && $wp_query->have_posts() ) {
		$first = 1 === max( 1, (int) get_query_var( 'paged' ) );
		foreach ( $wp_query->posts as $i => $post ) {
			$cards .= skl_story_card( $post, $first && 0 === $i ? 'featured' : '', true );
		}
	} else {
		$cards = '<p class="feed-empty">Nog geen berichten in deze categorie. Bekijk <a class="text-link" href="' . esc_url( $base ) . '">alle nieuws</a>.</p>';
	}

	$pagination = paginate_links( array(
		'type'      => 'list',
		'prev_text' => 'Vorige',
		'next_text' => 'Volgende',
	) );

	return '<div class="filter-bar"><div class="wrap"><nav class="filters" aria-label="Filter nieuws op ploeg">' . $pills . '</nav></div></div>'
		. '<section class="section section--tight" aria-labelledby="page-title"><div class="wrap">'
		. '<div class="feed">' . $cards . '</div>'
		. ( $pagination ? '<nav class="pagination" aria-label="Meer nieuws">' . $pagination . '</nav>' : '' )
		. '</div></section>';
}

function skl_block_article_head() {
	$post = get_post();
	if ( ! $post || 'post' !== $post->post_type ) {
		return '<header class="article-hero article-hero--plain"><div class="wrap article-hero__inner"><div><h1>Titel van het artikel</h1></div></div></header>';
	}
	$author  = skl_post_author( $post );
	$base    = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' );
	$minutes = max( 1, (int) round( str_word_count( wp_strip_all_tags( $post->post_content ) ) / 200 ) );
	$poster  = skl_is_poster( $post ) && has_post_thumbnail( $post );
	$photo   = has_post_thumbnail( $post ) && ! $poster;
	$modifier = $poster ? ' article-hero--poster' : ( $photo ? '' : ' article-hero--plain' );
	$meta    = str_replace( '</div>', '<span class="date">' . $minutes . ' min lezen</span></div>', skl_story_meta( $post ) );

	return '<header class="article-hero' . $modifier . '">'
		. '<svg class="page-hero__lines" viewBox="0 0 400 400" aria-hidden="true"><circle cx="200" cy="200" r="150"/><line x1="200" y1="-200" x2="200" y2="600"/><circle cx="200" cy="200" r="4" style="fill:currentColor"/></svg>'
		. '<div class="wrap article-hero__inner"><div>'
		. '<a class="back-link" href="' . esc_url( $base ) . '">' . skl_icon( 'back' ) . 'Alle nieuws</a>'
		. $meta
		. '<h1>' . esc_html( get_the_title( $post ) ) . '</h1>'
		. '<div class="byline"><span class="avatar" aria-hidden="true">' . esc_html( skl_initials( $author ) ) . '</span>'
		. '<div><strong>' . esc_html( $author ) . '</strong><time datetime="' . esc_attr( get_the_date( 'Y-m-d', $post ) ) . '">' . esc_html( get_the_date( 'l j F Y', $post ) ) . '</time></div></div>'
		. '</div>'
		. ( $poster ? '<figure class="article-hero__poster">' . get_the_post_thumbnail( $post, 'large', array( 'alt' => '', 'loading' => false ) ) . '</figure>' : '' )
		. '</div></header>'
		. ( $photo ? '<figure class="article-photo" style="margin-bottom:0">' . get_the_post_thumbnail( $post, 'full', array( 'alt' => '', 'loading' => false, 'fetchpriority' => 'high', 'sizes' => '(max-width: 64rem) 100vw, 64rem' ) ) . '</figure>' : '' );
}

function skl_block_article_share() {
	$post  = get_post();
	$url   = $post ? get_permalink( $post ) : home_url( '/' );
	$title = $post ? get_the_title( $post ) : '';

	return '<aside class="article-share" aria-label="Deel dit bericht"><div class="article-share__inner">'
		. '<span class="article-share__label">Deel dit bericht</span><div class="article-share__links">'
		. '<a class="share-btn" href="' . esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $url ) ) . '" target="_blank" rel="noopener">' . skl_icon( 'facebook' ) . 'Facebook</a>'
		. '<a class="share-btn" href="' . esc_url( 'https://wa.me/?text=' . rawurlencode( $title . ' ' . $url ) ) . '" target="_blank" rel="noopener">' . skl_icon( 'whatsapp' ) . 'WhatsApp</a>'
		. '<button class="share-btn" type="button" data-copy-link="' . esc_attr( $url ) . '">' . skl_icon( 'link' ) . '<span>Link kopiëren</span></button>'
		. '</div></div></aside>';
}

function skl_block_more_news() {
	$post  = get_post();
	$id    = $post ? $post->ID : 0;
	$term  = $post ? skl_post_category( $post ) : null;
	$posts = $term ? get_posts( array( 'numberposts' => 3, 'cat' => $term->term_id, 'post__not_in' => array( $id ) ) ) : array();
	if ( count( $posts ) < 3 ) {
		$exclude = array_merge( array( $id ), wp_list_pluck( $posts, 'ID' ) );
		$posts   = array_merge( $posts, get_posts( array( 'numberposts' => 3 - count( $posts ), 'post__not_in' => $exclude ) ) );
	}
	$base  = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' );
	$cards = implode( '', array_map( 'skl_story_card', $posts ) );

	return '<section class="section" aria-labelledby="more-title"><div class="wrap">'
		. '<div class="halfway" aria-hidden="true" style="margin-bottom:var(--section)"><span></span></div>'
		. '<div class="section-head"><h2 id="more-title">Meer nieuws</h2><a class="btn btn--ghost" href="' . esc_url( $base ) . '">Alle nieuws</a></div>'
		. '<div class="feed">' . $cards . '</div></div></section>';
}

/* ---------- Evenementen ---------- */

function skl_block_events() {
	$events = get_posts( array(
		'post_type'   => 'evenement',
		'numberposts' => -1,
		'meta_key'    => 'datum',
		'orderby'     => 'meta_value',
		'order'       => 'ASC',
		'meta_query'  => array(
			array( 'key' => 'datum', 'value' => wp_date( 'Ymd' ), 'compare' => '>=' ),
		),
	) );

	if ( ! $events ) {
		return '<p class="feed-empty">Binnenkort meer activiteiten. Volg ons op <a class="text-link" href="' . esc_url( skl_config( 'socials' )['facebook'] ) . '" target="_blank" rel="noopener">Facebook</a>.</p>';
	}

	$items     = '';
	$voorlopig = false;
	foreach ( $events as $event ) {
		$date       = skl_meta_date( $event->ID, 'datum' );
		$voorlopig  = $voorlopig || get_post_meta( $event->ID, 'voorlopig', true );
		$items     .= '<li class="event">'
			. '<time class="event__date" datetime="' . esc_attr( wp_date( 'Y-m-d', $date ) ) . '">' . esc_html( wp_date( 'l j F Y', $date ) ) . '</time>'
			. '<strong class="event__title">' . esc_html( get_the_title( $event ) ) . '</strong>'
			. '<span class="event__time">' . esc_html( (string) get_post_meta( $event->ID, 'uur', true ) ) . '</span>'
			. '<span class="event__place">' . esc_html( (string) get_post_meta( $event->ID, 'locatie', true ) ) . '</span>'
			. '</li>';
	}

	return '<ul class="events">' . $items . '</ul>'
		. ( $voorlopig ? '<p class="placeholder-note">Voorlopige data. De definitieve datum en locatie bevestigen we hier en op Facebook.</p>' : '' );
}

/* ---------- Sponsors ---------- */

function skl_block_sponsors( $a ) {
	$sponsors = get_posts( array(
		'post_type'   => 'sponsor',
		'numberposts' => $a['limit'] > 0 ? (int) $a['limit'] : -1,
		'orderby'     => 'menu_order title',
		'order'       => 'ASC',
	) );

	$items = '';
	foreach ( $sponsors as $sponsor ) {
		$logo = get_the_post_thumbnail( $sponsor, 'medium', array( 'alt' => get_the_title( $sponsor ), 'loading' => 'lazy' ) );
		if ( ! $logo ) {
			$logo = '<span>' . esc_html( get_the_title( $sponsor ) ) . '</span>';
		}
		$url    = (string) get_post_meta( $sponsor->ID, 'website', true );
		$items .= '<li class="board">' . ( $url ? '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . $logo . '</a>' : $logo ) . '</li>';
	}

	if ( $a['cta'] ) {
		$page   = get_page_by_path( 'sponsor-worden' );
		$items .= '<li class="board board--cta"><a href="' . esc_url( $page ? get_permalink( $page ) : home_url( '/sponsor-worden/' ) ) . '">'
			. '<span>Jouw bord hier?<small>Word sponsor</small></span></a></li>';
	}

	return '<ul class="boards" aria-label="Sponsors">' . $items . '</ul>';
}

/* ---------- Ploegen ---------- */

function skl_block_team_list( $a ) {
	$teams = get_posts( array(
		'post_type'   => 'ploeg',
		'numberposts' => -1,
		'orderby'     => 'menu_order title',
		'order'       => 'ASC',
		'meta_key'    => 'groep',
		'meta_value'  => $a['groep'],
	) );

	$items = '';
	foreach ( $teams as $team ) {
		$reeks  = (string) get_post_meta( $team->ID, 'reeks', true );
		$info   = (string) get_post_meta( $team->ID, 'info', true );
		$items .= '<li><strong>' . esc_html( get_the_title( $team ) ) . '</strong><span>' . esc_html( $reeks ) . '</span>'
			. ( $info ? '<small>' . esc_html( $info ) . '</small>' : '' ) . '</li>';
	}

	return $items ? '<ul class="team-list">' . $items . '</ul>' : '<p class="feed-empty">Nog geen ploegen in deze groep.</p>';
}

/* ---------- Documenten ---------- */

function skl_block_documents( $a ) {
	if ( ! $a['rubriek'] ) {
		return '<p class="feed-empty">Kies een rubriek in de instellingen van dit blok.</p>';
	}
	$docs = get_posts( array(
		'post_type'   => 'document',
		'numberposts' => -1,
		'orderby'     => 'menu_order title',
		'order'       => 'ASC',
		'tax_query'   => array( array( 'taxonomy' => 'rubriek', 'field' => 'slug', 'terms' => $a['rubriek'] ) ),
	) );

	$out = '';
	foreach ( $docs as $doc ) {
		$meta = (string) get_post_meta( $doc->ID, 'omschrijving', true );
		$pdf  = (int) get_post_meta( $doc->ID, 'pdf', true );
		$url  = $pdf ? wp_get_attachment_url( $pdf ) : '';
		$text = trim( wp_strip_all_tags( excerpt_remove_blocks( $doc->post_content ) ) );
		$head = '<span class="doc__title">' . esc_html( get_the_title( $doc ) ) . '</span>'
			. ( $meta ? '<span class="doc__meta">' . esc_html( $meta ) . '</span>' : '' );

		// Enkel een pdf, geen tekst: meteen een downloadlijn.
		if ( '' === $text ) {
			if ( $url ) {
				$out .= '<a class="doc doc--file" id="' . esc_attr( $doc->post_name ) . '" href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . $head . '</a>';
			}
			continue;
		}

		$body = apply_filters( 'the_content', $doc->post_content );
		if ( $url ) {
			$body .= '<p class="doc__download"><a class="btn btn--ghost" href="' . esc_url( $url ) . '" target="_blank" rel="noopener">'
				. skl_icon( 'download' ) . 'Download pdf<span class="visually-hidden">: ' . esc_html( get_the_title( $doc ) ) . '</span></a></p>';
		}
		$out .= '<details class="doc" id="' . esc_attr( $doc->post_name ) . '"><summary>' . $head . '</summary><div class="doc__body">' . $body . '</div></details>';
	}

	return '<div class="docs">' . $out . '</div>';
}

/* ---------- Persoon ---------- */

function skl_block_person( $a ) {
	$name   = trim( $a['name'] );
	$avatar = $a['imageId']
		? '<span class="avatar">' . wp_get_attachment_image( $a['imageId'], 'thumbnail', false, array( 'alt' => '' ) ) . '</span>'
		: ( $name && 'Naam' !== $name
			? '<span class="avatar" aria-hidden="true">' . esc_html( skl_initials( $name ) ) . '</span>'
			: '<span class="avatar avatar--empty" aria-hidden="true">?</span>' );

	return '<div class="person">' . $avatar . '<div><strong>' . esc_html( $name ? $name : 'Naam' ) . '</strong>'
		. ( $a['role'] ? '<span>' . esc_html( $a['role'] ) . '</span>' : '' )
		. ( $a['email'] ? '<a href="mailto:' . esc_attr( $a['email'] ) . '">' . esc_html( $a['email'] ) . '</a>' : '' )
		. '</div></div>';
}

/* ---------- Startopbouw voor een nieuw nieuwsbericht ---------- */

add_action( 'init', function () {
	$post = get_post_type_object( 'post' );
	$post->template = array(
		array( 'skl/post-setup', array( 'lock' => array( 'move' => true, 'remove' => true ) ) ),
		array( 'core/paragraph', array( 'placeholder' => 'Intro: vat het bericht samen in 1 à 2 zinnen. Deze tekst staat groot bovenaan het artikel.' ) ),
		array( 'core/paragraph', array( 'placeholder' => 'Schrijf hier het bericht. Druk op Enter voor een nieuwe alinea, of op + voor een foto of galerij.' ) ),
	);
	$doc = get_post_type_object( 'document' );
	$doc->template = array(
		array( 'skl/document-setup', array( 'lock' => array( 'move' => true, 'remove' => true ) ) ),
		array( 'core/paragraph', array( 'placeholder' => 'Tekst van het document (optioneel). Laat leeg als je enkel een pdf wil aanbieden.' ) ),
	);
}, 20 );

// Bestaande berichten krijgen de berichtkaart ook zodra ze in de editor openen.
foreach ( array( 'post' => 'skl/post-setup', 'document' => 'skl/document-setup' ) as $skl_type => $skl_block ) {
	add_filter( 'rest_prepare_' . $skl_type, function ( $response, $post, $request ) use ( $skl_block ) {
		if ( 'edit' !== $request['context'] || has_block( $skl_block, $post ) ) {
			return $response;
		}
		$data = $response->get_data();
		if ( isset( $data['content']['raw'] ) ) {
			$data['content']['raw'] = '<!-- wp:' . $skl_block . ' {"lock":{"move":true,"remove":true}} /-->' . "\n\n" . $data['content']['raw'];
			$response->set_data( $data );
		}
		return $response;
	}, 10, 3 );
}
