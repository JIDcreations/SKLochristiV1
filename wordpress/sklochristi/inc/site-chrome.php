<?php
/**
 * Header en footer. De menu's beheert de club via Weergave > Menu's.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	register_nav_menus( array(
		'hoofdmenu'      => 'Hoofdmenu (header)',
		'footer-club'    => 'Footer: kolom 1',
		'footer-info'    => 'Footer: kolom 2',
		'footer-onderaan' => 'Footer: onderaan',
	) );
} );

/**
 * Menu-items van een locatie als boom, met current-klassen van WordPress.
 */
function skl_menu_tree( $location ) {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return array();
	}
	$items = wp_get_nav_menu_items( $locations[ $location ], array( 'update_post_term_cache' => false ) );
	if ( ! $items ) {
		return array();
	}
	_wp_menu_item_classes_by_context( $items );

	$by_parent = array();
	foreach ( $items as $item ) {
		$by_parent[ (int) $item->menu_item_parent ][] = $item;
	}
	$build = function ( $parent ) use ( &$build, $by_parent ) {
		$out = array();
		foreach ( $by_parent[ $parent ] ?? array() as $item ) {
			$item->children = $build( (int) $item->ID );
			$out[]          = $item;
		}
		return $out;
	};
	return $build( 0 );
}

function skl_menu_link( $item, $icon = false ) {
	$classes = (array) $item->classes;
	$attrs   = ' href="' . esc_url( $item->url ) . '"';
	if ( '_blank' === $item->target ) {
		$attrs .= ' target="_blank" rel="noopener"';
	}
	if ( in_array( 'current-menu-item', $classes, true ) ) {
		$attrs .= ' aria-current="page"';
	}
	$label = esc_html( $item->title );
	if ( $icon && '_blank' === $item->target ) {
		$label .= ' ' . skl_icon( 'external' );
	}
	return '<a' . $attrs . '>' . $label . '</a>';
}

function skl_render_site_header() {
	$items = '';
	foreach ( skl_menu_tree( 'hoofdmenu' ) as $item ) {
		$classes = (array) $item->classes;
		if ( $item->children ) {
			$current = array_intersect( array( 'current-menu-ancestor', 'current-menu-parent', 'current-menu-item' ), $classes );
			$id      = 'nav-sub-' . $item->ID;
			$sub     = '';
			foreach ( $item->children as $child ) {
				$sub .= '<li>' . skl_menu_link( $child ) . '</li>';
			}
			$items .= '<li class="site-nav__group' . ( $current ? ' is-current' : '' ) . '">'
				. '<button class="site-nav__trigger" type="button" aria-expanded="false" aria-controls="' . esc_attr( $id ) . '">' . esc_html( $item->title ) . ' ' . skl_icon( 'chevron' ) . '</button>'
				. '<ul class="site-nav__sub" id="' . esc_attr( $id ) . '">' . $sub . '</ul></li>';
			continue;
		}
		$cta    = in_array( 'site-nav__cta', $classes, true );
		$items .= '<li' . ( $cta ? ' class="site-nav__cta"' : '' ) . '>' . skl_menu_link( $item, $cta ) . '</li>';
	}

	return '<header class="site-header"><div class="wrap">'
		. '<a class="brand" href="' . esc_url( home_url( '/' ) ) . '"><img src="' . esc_url( skl_asset( 'img/crest.png' ) ) . '" alt="" width="44" height="44"><span>' . esc_html( get_bloginfo( 'name' ) ) . '</span></a>'
		. '<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="Menu openen">' . skl_icon( 'menu' ) . '</button>'
		. '<nav class="site-nav" id="site-nav" aria-label="Hoofdmenu"><ul>' . $items . '</ul></nav>'
		. '</div></header>';
}

function skl_render_footer_column( $location ) {
	$locations = get_nav_menu_locations();
	$menu      = ! empty( $locations[ $location ] ) ? wp_get_nav_menu_object( $locations[ $location ] ) : null;
	if ( ! $menu ) {
		return '';
	}
	$links = '';
	foreach ( skl_menu_tree( $location ) as $item ) {
		$links .= '<li>' . skl_menu_link( $item, true ) . '</li>';
	}
	return '<div><h2>' . esc_html( $menu->name ) . '</h2><ul class="footer-links">' . $links . '</ul></div>';
}

function skl_render_site_footer() {
	$email   = skl_config( 'email' );
	$socials = '';
	$names   = array( 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube' );
	foreach ( skl_config( 'socials' ) as $key => $url ) {
		$socials .= '<li><a href="' . esc_url( $url ) . '" target="_blank" rel="noopener" aria-label="SK Lochristi op ' . esc_attr( $names[ $key ] ) . '">' . skl_icon( $key ) . '</a></li>';
	}

	$complexen = '';
	foreach ( skl_config( 'complexen' ) as $c ) {
		$complexen .= '<address><strong>' . esc_html( $c['name'] ) . '</strong><br>' . esc_html( $c['address'] ) . '<br>'
			. '<a href="tel:' . esc_attr( $c['tel'] ) . '">' . esc_html( $c['phone'] ) . '</a></address>';
	}

	$bottom = '';
	foreach ( skl_menu_tree( 'footer-onderaan' ) as $item ) {
		$bottom .= '<li>' . skl_menu_link( $item ) . '</li>';
	}

	return '<footer class="site-footer"><div class="wrap"><div class="footer-top">'
		. '<div class="footer-brand"><img src="' . esc_url( skl_asset( 'img/crest.png' ) ) . '" alt="" width="64" height="64">'
		. '<a class="footer-mail" href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>'
		. '<ul class="socials">' . $socials . '</ul></div>'
		. skl_render_footer_column( 'footer-club' )
		. skl_render_footer_column( 'footer-info' )
		. '<div><h2>Complexen</h2>' . $complexen . '</div>'
		. '</div><div class="footer-bottom">'
		. '<p style="margin:0">© ' . esc_html( wp_date( 'Y' ) ) . ' ' . esc_html( get_bloginfo( 'name' ) ) . ', KBVB-stamnummer ' . esc_html( skl_config( 'stamnummer' ) ) . '</p>'
		. '<ul>' . $bottom . '</ul>'
		. '</div></div></footer>';
}
