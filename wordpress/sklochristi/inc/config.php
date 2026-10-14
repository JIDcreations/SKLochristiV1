<?php
/**
 * Vaste clubgegevens die op meerdere plaatsen terugkomen (header, footer, snelkoppelingen).
 */

defined( 'ABSPATH' ) || exit;

function skl_config( $key = null ) {
	$config = array(
		'email'     => 'info@sklochristi.be',
		'stamnummer' => '5617',
		'links'     => array(
			'kalender'      => 'https://www.rbfa.be/nl/club/1969/komende-wedstrijden',
			'afgelastingen' => 'https://static.belgianfootball.be/project/publiek/remises/nl/afgelastingen.htm?m=15d',
			'aanduidingen'  => 'https://static.belgianfootball.be/project/publiek/aanduidingenclub/nl/aanduiding_05617.htm',
			'webshop'       => 'https://sklochristi.shop4clubs.eu',
		),
		'socials'   => array(
			'facebook'  => 'https://www.facebook.com/sklochristi',
			'instagram' => 'https://www.instagram.com/sklochristi',
			'youtube'   => 'https://www.youtube.com/channel/UCnQBT0DaA0Etlryf5HTszVQ',
		),
		'complexen' => array(
			array(
				'name'    => 'Bosdreef',
				'address' => 'Bosdreef 57, 9080 Lochristi',
				'phone'   => '09 355 02 52',
				'tel'     => '+3293550252',
			),
			array(
				'name'    => 'Beerveldsebaan',
				'address' => 'Beerveldsebaan 24, 9080 Lochristi',
				'phone'   => '09 355 95 10',
				'tel'     => '+3293559510',
			),
		),
	);

	return $key ? ( $config[ $key ] ?? null ) : $config;
}
