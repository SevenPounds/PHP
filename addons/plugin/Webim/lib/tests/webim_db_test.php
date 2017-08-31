<?php

require_once( dirname( __FILE__ ) . '/../webim_db.class.php' );

define( 'WEBIMDB_DEBUG', true );
define( 'WEBIMDB_CHARSET', 'utf8' );
$imdb = new webim_db( 'root', 'public', 'webim', '127.0.0.1' );
$imdb->set_prefix( 'webim_' );
$imdb->add_tables( array( 'histories', 'settings', 'rooms', 'visitors', 'members', 'blocked' ) );

$q = $imdb->prepare( "SELECT * FROM $imdb->histories;" );

print_r( $imdb->get_results( $q ) );
//print_r( $imdb->query( $q ) );

