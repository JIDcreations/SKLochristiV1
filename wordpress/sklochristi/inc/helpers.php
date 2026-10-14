<?php
/**
 * Kleine hulpfuncties: iconen, nieuwskaarten, initialen.
 */

defined( 'ABSPATH' ) || exit;

function skl_icon( $name ) {
	$icons = array(
		'external' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M7 17L17 7M9 7h8v8"/></svg>',
		'chevron'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>',
		'back'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>',
		'download' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M12 4v11M7 10l5 5 5-5M5 20h14"/></svg>',
		'menu'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path class="i-open" d="M3 7h18M3 12h18M3 17h18"/><path class="i-close" d="M6 6l12 12M18 6L6 18"/></svg>',
		'calendar' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/><path d="M8 14h2M14 14h2M8 17.5h2"/></svg>',
		'star'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 16.4 6.8 19.1l1-5.8L3.5 9.2l5.9-.9z"/></svg>',
		'bag'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16l-1.2 12.1a2 2 0 0 1-2 1.9H7.2a2 2 0 0 1-2-1.9z"/><path d="M9 10V6a3 3 0 0 1 6 0v4"/></svg>',
		'facebook' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-7.5h2.5l.4-3h-2.9V8.6c0-.9.3-1.5 1.5-1.5h1.5V4.4c-.3 0-1.2-.1-2.2-.1-2.2 0-3.7 1.3-3.7 3.8v2.4H8v3h2.6V21h2.9z"/></svg>',
		'instagram' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r=".6" fill="currentColor"/></svg>',
		'whatsapp' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2c-1.5 0-3-.4-4.3-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1-.7-.3-1.4-.7-2-1.3-.5-.5-1-1.1-1.3-1.7-.1-.2 0-.4.1-.5l.4-.4.2-.4v-.4l-.8-1.9c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.6.6-.9 1.3-.9 2.1 0 .9.4 1.8.9 2.5 1.1 1.6 2.5 2.9 4.2 3.7.5.2 1 .4 1.4.5.6.2 1.1.1 1.6.1.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.1-1.2l-.2-.2z"/></svg>',
		'link'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M10 14a4.5 4.5 0 0 0 6.4 0l3-3a4.5 4.5 0 0 0-6.4-6.4l-1 1"/><path d="M14 10a4.5 4.5 0 0 0-6.4 0l-3 3a4.5 4.5 0 0 0 6.4 6.4l1-1"/></svg>',
		'youtube'  => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21.6 7.2a2.5 2.5 0 0 0-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.4A2.5 2.5 0 0 0 2.4 7.2C2 8.8 2 12 2 12s0 3.2.4 4.8a2.5 2.5 0 0 0 1.8 1.8C5.8 19 12 19 12 19s6.2 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8c.4-1.6.4-4.8.4-4.8s0-3.2-.4-4.8zM10 15V9l5.2 3L10 15z"/></svg>',
	);
	return $icons[ $name ] ?? '';
}

function skl_asset( $path ) {
	return get_theme_file_uri( 'assets/' . $path );
}

function skl_initials( $name ) {
	$parts = preg_split( '/\s+/', trim( (string) $name ) );
	$out   = '';
	foreach ( $parts as $part ) {
		if ( preg_match( '/^\p{Lu}/u', $part ) ) {
			$out .= mb_substr( $part, 0, 1 );
		}
		if ( mb_strlen( $out ) >= 2 ) {
			break;
		}
	}
	return $out;
}

const SKL_NEWS_CATEGORIES = array( 'seniors', 'jeugd', 'club' );

function skl_post_category( $post ) {
	foreach ( get_the_category( $post ) as $term ) {
		if ( in_array( $term->slug, SKL_NEWS_CATEGORIES, true ) ) {
			return $term;
		}
	}
	return null;
}

function skl_post_author( $post ) {
	$name = trim( (string) get_post_meta( $post->ID, 'auteur', true ) );
	return $name ? $name : get_the_author_meta( 'display_name', $post->post_author );
}

function skl_is_poster( $post ) {
	return (bool) get_post_meta( $post->ID, 'affiche', true );
}

function skl_story_meta( $post, $with_author = false ) {
	$term = skl_post_category( $post );
	$html = '<div class="story__meta">';
	if ( $term ) {
		$html .= '<span class="tag tag--' . esc_attr( $term->slug ) . '">' . esc_html( $term->name ) . '</span>';
	}
	$html .= '<time class="date" datetime="' . esc_attr( get_the_date( 'Y-m-d', $post ) ) . '">' . esc_html( get_the_date( 'j F Y', $post ) ) . '</time>';
	if ( $with_author ) {
		$html .= '<span class="date">door ' . esc_html( skl_post_author( $post ) ) . '</span>';
	}
	return $html . '</div>';
}

function skl_story_card( $post, $variant = '', $with_author = false ) {
	$post  = get_post( $post );
	$class = 'story' . ( $variant ? ' story--' . $variant : '' );
	$attr  = array( 'alt' => '', 'loading' => 'featured' === $variant ? false : 'lazy' );
	if ( 'featured' === $variant ) {
		$attr['fetchpriority'] = 'high';
	}
	if ( skl_is_poster( $post ) ) {
		$attr['style'] = 'object-position:50% 20%';
	}
	$image = has_post_thumbnail( $post )
		? get_the_post_thumbnail( $post, 'medium_large', $attr )
		: '<img src="' . esc_url( skl_asset( 'img/crest.png' ) ) . '" alt="" loading="lazy" style="object-fit:contain;padding:15%">';

	return '<article class="' . esc_attr( $class ) . '">'
		. '<div class="story__media">' . $image . '</div>'
		. '<div class="story__body">' . skl_story_meta( $post, $with_author )
		. '<h3><a href="' . esc_url( get_permalink( $post ) ) . '">' . esc_html( get_the_title( $post ) ) . '</a></h3>'
		. '<p>' . esc_html( get_the_excerpt( $post ) ) . '</p>'
		. '</div></article>';
}

function skl_is_external( $url ) {
	$host = wp_parse_url( $url, PHP_URL_HOST );
	return $host && wp_parse_url( home_url(), PHP_URL_HOST ) !== $host;
}
