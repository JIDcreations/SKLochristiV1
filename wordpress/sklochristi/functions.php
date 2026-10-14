<?php
/**
 * SK Lochristi theme.
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/content-model.php';
require_once __DIR__ . '/inc/site-chrome.php';
require_once __DIR__ . '/inc/blocks.php';
require_once __DIR__ . '/inc/admin.php';

add_action( 'after_setup_theme', function () {
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'style.css' );
	remove_theme_support( 'core-block-patterns' );
} );

add_action( 'wp_enqueue_scripts', function () {
	$version = wp_get_theme()->get( 'Version' );
	wp_enqueue_style( 'sklochristi', get_stylesheet_uri(), array(), filemtime( get_stylesheet_directory() . '/style.css' ) ?: $version );
	wp_enqueue_script( 'sklochristi', get_theme_file_uri( 'assets/js/main.js' ), array(), filemtime( get_theme_file_path( 'assets/js/main.js' ) ) ?: $version, array( 'strategy' => 'defer', 'in_footer' => true ) );
} );

// Knopstijlen die de club in de editor kan kiezen.
add_action( 'init', function () {
	register_block_style( 'core/button', array( 'name' => 'solid', 'label' => 'Blauw', 'is_default' => true ) );
	register_block_style( 'core/button', array( 'name' => 'ghost', 'label' => 'Omlijnd' ) );
	register_block_style( 'core/button', array( 'name' => 'white', 'label' => 'Wit (op blauw)' ) );
} );

add_filter( 'document_title_separator', fn() => '|' );

add_filter( 'excerpt_more', fn() => '…' );

// Zelfde als de statische site: animaties enkel als JavaScript draait.
add_action( 'wp_head', function () {
	echo "<script>document.documentElement.classList.add('js');</script>\n";
}, 1 );

// Favicon valt terug op het clublogo zolang er geen site-icoon is ingesteld.
add_action( 'wp_head', function () {
	if ( ! has_site_icon() ) {
		echo '<link rel="icon" href="' . esc_url( skl_asset( 'img/logo.png' ) ) . '">' . "\n";
	}
} );
