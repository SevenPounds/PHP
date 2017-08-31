<?php

/**
 * Convert string ids to array
 *
 * @param string $ids
 *
 * @return array ids
 *
 */
function webim_ids_array( $ids ){
	return ($ids===NULL || $ids==="") ? array() : (is_array($ids) ? array_unique($ids) : array_unique(explode(",", $ids)));
}

/** 
 * url helper 
 */

function webim_is_remote() {
	$remote = false;
	if ( strlen($_SERVER['HTTP_REFERER']) ) {
		$referer = parse_url( $_SERVER['HTTP_REFERER'] );
		$referer['port'] = isset( $referer['port'] ) ? $referer['port'] : "80";
		if ( $referer['port'] != $_SERVER['SERVER_PORT'] || $referer['host'] != $_SERVER['HTTP_HOST'] || $referer['scheme'] != ( (@$_SERVER["HTTPS"] == "on") ? "https" : "http" ) ){
			$remote = true;
		}
	}
	return $remote;
}

function webim_urlpath() {
	global $_SERVER;
	$name = htmlspecialchars($_SERVER['SCRIPT_NAME'] ? $_SERVER['SCRIPT_NAME'] : $_SERVER['PHP_SELF']);
	return ( (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://" ) . ( ( $_SERVER["SERVER_PORT"] != "80" ) ? ( $_SERVER["HTTP_HOST"].":".$_SERVER["SERVER_PORT"] ) : $_SERVER["HTTP_HOST"] ) . substr( $name, 0, strrpos( $name, '/' ) ) . "/";
}

function webim_isvid($uid) {
    return strpos($uid, 'vid:') === 0;
}

function webim_to_utf8($charset, $str) {
    if(strtoupper($charset) == 'UTF-8') {
        return $str;
    }
    if(function_exists('iconv')) {
        return iconv($charset, 'utf-8', $str);
    }
    require_once dirname(__FILE__) . '/class_chinese.php';
    $chs = new Chinese($charset, 'utf-8');
    return $chs->Convert($str);
}

function webim_from_utf8($charset, $str) {
    if(strtoupper($charset) == 'UTF-8') {
        return $str;
    }
    if(function_exists('iconv')) {
        return iconv('utf-8', $charset, $str);
    }
    require_once dirname(__FILE__) . '/class_chinese.php';
    $chs = new Chinese( 'utf-8', $charset );
    return $chs->Convert( $str );
}

/** In PHP 5.2 or higher we don't need to bring this in */

if ( !function_exists( 'json_encode' ) ) {

	require_once( dirname( __FILE__ ) . '/JSON/JSON.php' );
	function json_encode( $arg ) {
		global $services_json;
		if ( !isset( $services_json ) ) {
			$services_json = new Services_JSON();
		}
		return $services_json->encode( $arg );
	}

	function json_decode( $arg ) {
		global $services_json;
		if (!isset( $services_json ) ) {
			$services_json = new Services_JSON();
		}
		return $services_json->decode( $arg );
	}

}

?>
