<?php
/**
 * Admin op maat van de club: "Berichten" heet "Nieuws", en op de nieuwspagina
 * wijst de bovenbalk naar de berichten in plaats van naar de (lege) pagina.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	$labels = get_post_type_object( 'post' )->labels;

	$labels->name               = 'Nieuws';
	$labels->menu_name          = 'Nieuws';
	$labels->name_admin_bar     = 'Nieuwsbericht';
	$labels->singular_name      = 'Nieuwsbericht';
	$labels->all_items          = 'Alle nieuws';
	$labels->add_new            = 'Nieuw bericht';
	$labels->add_new_item       = 'Nieuw bericht';
	$labels->edit_item          = 'Nieuwsbericht bewerken';
	$labels->new_item           = 'Nieuw bericht';
	$labels->view_item          = 'Bericht bekijken';
	$labels->search_items       = 'Nieuws zoeken';
	$labels->not_found          = 'Geen nieuws gevonden';
	$labels->not_found_in_trash = 'Geen nieuws in de prullenbak';
} );

// Pagina's met een automatische lijst: in de bovenbalk meteen naar die lijst.
add_action( 'admin_bar_menu', function ( $bar ) {
	$map = array(
		'evenementen'    => array( 'evenement', 'Evenementen beheren', 'Nieuw evenement' ),
		'ploegen'        => array( 'ploeg', 'Ploegen beheren', 'Nieuwe ploeg' ),
		'sponsor-worden' => array( 'sponsor', 'Sponsors beheren', 'Nieuwe sponsor' ),
		'documenten'     => array( 'document', 'Documenten beheren', 'Nieuw document' ),
	);
	$page = is_admin() || ! is_page() ? null : get_queried_object();
	if ( ! $page || ! isset( $map[ $page->post_name ] ) || ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	list( $type, $manage, $new ) = $map[ $page->post_name ];
	$bar->add_node( array( 'id' => 'skl-manage', 'title' => $manage, 'href' => admin_url( 'edit.php?post_type=' . $type ) ) );
	$bar->add_node( array( 'id' => 'skl-manage-new', 'title' => $new, 'href' => admin_url( 'post-new.php?post_type=' . $type ) ) );
}, 100 );

add_action( 'admin_bar_menu', function ( $bar ) {
	if ( is_admin() || ! is_home() || ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	$bar->remove_node( 'edit' );
	$bar->add_node( array(
		'id'    => 'skl-news',
		'title' => 'Nieuws beheren',
		'href'  => admin_url( 'edit.php' ),
	) );
	$bar->add_node( array(
		'id'    => 'skl-news-new',
		'title' => 'Nieuw bericht',
		'href'  => admin_url( 'post-new.php' ),
	) );
}, 100 );

// In de editor van de nieuwspagina zelf: duidelijk zeggen waar het nieuws wél beheerd wordt.
add_action( 'enqueue_block_editor_assets', function () {
	$id = (int) get_option( 'page_for_posts' );
	if ( ! $id || get_the_ID() !== $id ) {
		return;
	}
	$notice = 'Deze pagina toont automatisch alle nieuwsberichten. Hier pas je enkel de titel en de samenvatting (tekst bovenaan) aan. Nieuw bericht: Nieuws > Nieuw bericht in het menu links.';
	wp_add_inline_script( 'wp-edit-post', 'wp.domReady(function(){wp.data.dispatch("core/notices").createInfoNotice(' . wp_json_encode( $notice ) . ',{isDismissible:false,actions:[{label:"Nieuws beheren",url:' . wp_json_encode( admin_url( 'edit.php' ) ) . '},{label:"Nieuw bericht",url:' . wp_json_encode( admin_url( 'post-new.php' ) ) . '}]});});', 'after' );
} );

// Editor: titel niet tegen de rand, en nieuwsberichten zoals het artikel op de site.
add_action( 'enqueue_block_assets', function () {
	if ( ! is_admin() ) {
		return;
	}
	$css = <<<'CSS'
.editor-styles-wrapper .editor-post-title { padding-inline: var(--gutter); max-width: var(--max); margin-inline: auto; }

/* Nieuwsbericht: titel in de blauwe kop, daaronder de leeskolom */
.post-type-post .editor-visual-editor__post-title-wrapper {
  position: relative; overflow: hidden; margin: 0 !important;
  background: var(--color-primary);
  padding: clamp(2.5rem, 2rem + 3vw, 4.5rem) 0 clamp(5rem, 4rem + 4vw, 7rem);
}
.post-type-post .editor-visual-editor__post-title-wrapper::after {
  content: ""; position: absolute; right: -6rem; top: 50%; width: 24rem; height: 24rem; translate: 0 -50%;
  border: 2px solid rgb(255 255 255 / .14); border-radius: 50%; pointer-events: none;
}
.post-type-post .editor-post-title {
  max-width: 64rem; color: var(--color-white) !important;
  font-size: clamp(2.75rem, 1.4rem + 5.2vw, 5.75rem); font-weight: 900; line-height: .88;
}
.post-type-post .editor-post-title [data-rich-text-placeholder]::after,
.post-type-post .editor-post-title.is-placeholder::before { color: rgb(255 255 255 / .45); opacity: 1; }
.post-type-post .is-root-container {
  max-width: calc(42rem + 2 * var(--gutter)); margin-inline: auto; padding: 0 var(--gutter) 6rem;
  font-size: 1.125rem; line-height: 1.75;
}
.post-type-post .is-root-container > p:nth-child(2) {
  font-size: clamp(1.25rem, 1.1rem + .6vw, 1.5rem); font-weight: 600; line-height: 1.5;
  padding-left: 1.25rem; border-left: 3px solid var(--color-primary-hover);
}
.post-type-post .is-root-container > .skl-setup + p:nth-child(2):not(.has-text) { margin-top: 2.5rem; }

/* Berichtkaart */
.skl-setup {
  position: relative; z-index: 2; margin: calc(-1 * clamp(3.5rem, 3rem + 2vw, 5rem)) calc(-1 * clamp(0rem, 4vw, 8rem)) 2.5rem !important;
  background: var(--color-white); border-radius: var(--radius-m);
  box-shadow: 0 2rem 3rem -1.5rem rgb(0 22 95 / .35), 0 0 0 1px rgb(0 22 95 / .08);
  font-family: var(--font-body); font-size: 15px; line-height: 1.5; color: var(--color-primary);
}
.skl-setup__head { display: flex; flex-wrap: wrap; align-items: baseline; gap: .25rem 1rem; padding: 1rem 1.5rem; border-bottom: 1px solid var(--color-rule); }
.skl-setup__head strong { font-family: var(--font-display); font-weight: 800; font-size: 1.5rem; line-height: 1; }
.skl-setup__head span { color: var(--color-muted); font-size: 14px; }
.skl-setup__grid { display: grid; grid-template-columns: 1.15fr 1fr; gap: 1.5rem; padding: 1.5rem; }
@media (max-width: 900px) { .skl-setup__grid { grid-template-columns: 1fr; } }
.skl-setup__photo .components-placeholder { min-height: 100%; border-radius: var(--radius-s); box-shadow: inset 0 0 0 2px var(--color-rule); background: var(--color-line); }
.skl-setup__preview { position: relative; border-radius: var(--radius-s); overflow: hidden; background: var(--color-line); }
.skl-setup__preview img { display: block; width: 100%; aspect-ratio: 16 / 9; object-fit: cover; border-radius: 0 !important; }
.skl-setup__preview.is-poster img { aspect-ratio: auto; max-height: 26rem; object-fit: contain; }
.skl-setup__photo-actions { display: flex; gap: .5rem; padding: .75rem 0 0; }
.skl-setup__fields { display: grid; gap: 1.1rem; align-content: start; }
.skl-setup__label { display: block; margin-bottom: 8px; font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: .04em; }
.skl-setup__pills { display: flex; flex-wrap: wrap; gap: .5rem; }
.skl-setup__pill {
  appearance: none; cursor: pointer; padding: .5rem 1rem; border-radius: 999px;
  border: 2px solid var(--color-rule); background: var(--color-white);
  font: inherit; font-weight: 700; color: var(--color-primary);
}
.skl-setup__pill:hover { border-color: var(--color-primary); }
.skl-setup__pill.is-on { background: var(--color-primary); border-color: var(--color-primary); color: var(--color-white); }
.skl-setup__warn { margin: .6rem 0 0; color: #b32d2e; font-size: 13px; }
.skl-setup__grid--doc { grid-template-columns: 1fr 1fr; }
.skl-setup__file { padding: 1rem; background: var(--color-line); border-radius: var(--radius-s); }
.skl-setup__file-info { display: flex; gap: .75rem; align-items: center; color: var(--color-shirt); }
.skl-setup__file-info strong { display: block; color: var(--color-primary); }
.skl-setup__file-info span { display: block; color: var(--color-muted); font-size: 13px; overflow-wrap: anywhere; }

/* Document: zelfde leeskolom als op de site */
.post-type-document .is-root-container, .post-type-document .editor-post-title { max-width: calc(48rem + 2 * var(--gutter)); margin-inline: auto; padding-inline: var(--gutter); }
.post-type-document .editor-post-title { margin-top: 3rem; margin-bottom: 2rem; font-size: var(--step-3); }
.post-type-document .skl-setup { margin: 0 calc(-1 * clamp(0rem, 3vw, 6rem)) 2.5rem !important; }

.skl-manage {
  display: flex; flex-wrap: wrap; align-items: center; gap: .5rem 1rem; margin-bottom: 1rem; padding: .75rem 1rem;
  background: #fff8e5; border: 1px solid #f0c33c; border-radius: 6px;
  font-family: var(--font-body); font-size: 14px; line-height: 1.4; color: #1e1e1e;
}
.skl-manage > span { margin-right: auto; }
.skl-manage__btn { padding: .45rem .9rem; border-radius: 4px; border: 1px solid #1e1e1e; background: #fff; color: #1e1e1e !important; font-weight: 600; text-decoration: none !important; white-space: nowrap; }
.skl-manage__btn.is-primary { background: #1e1e1e; color: #fff !important; }
.skl-setup .components-base-control__help { margin-top: 4px; color: var(--color-muted); }
CSS;
	wp_register_style( 'skl-editor', false, array(), null );
	wp_enqueue_style( 'skl-editor' );
	wp_add_inline_style( 'skl-editor', $css );
} );

// Ploegen, evenementen en sponsors zijn enkel velden: een eenvoudig formulier i.p.v. de blokeditor.
add_filter( 'use_block_editor_for_post_type', function ( $use, $type ) {
	return in_array( $type, array( 'ploeg', 'evenement', 'sponsor' ), true ) ? false : $use;
}, 10, 2 );

// Reacties staan uit op deze site: menu-item weg.
add_action( 'admin_menu', fn() => remove_menu_page( 'edit-comments.php' ) );
add_action( 'admin_bar_menu', fn( $bar ) => $bar->remove_node( 'comments' ), 999 );

add_filter( 'enter_title_here', function ( $text, $post ) {
	$hints = array( 'evenement' => 'Naam van het evenement', 'ploeg' => 'Naam van de ploeg, bv. U10 of Eerste ploeg', 'sponsor' => 'Naam van de sponsor' );
	return $hints[ $post->post_type ] ?? $text;
}, 10, 2 );
