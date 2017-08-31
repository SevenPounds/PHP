<?php

/**
 * WebIM-for-ThinkSNS插件入口文件
 *
 * @author ery lee <ery.lee at gmail.com>
 * @copyright   (C) 2014 NexTalk.IM
 * @license     http://nextalk.im/license
 * @lastmodify  2014-06-17
 * @version 5.5
 */ 

// Die if PHP is not new enough
if (version_compare( PHP_VERSION, '4.3', '<' ) ) {
	die( sprintf( 'Your server is running PHP version %s but webim requires at least 4.3', PHP_VERSION ) );
}

/**
 * Env
 */
require 'env.php';

/**
 * -------------------------
 * integrated with thinksns
 * -------------------------
 */

//NOTICE: Have to redefine SITE_URL.
define('__ROOT__', chop(chop($_SERVER['PHP_SELF'], 'ddons/plugin/Webim/index.php'),'/a'));
defined('SITE_PATH') or define('SITE_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
require_once (SITE_PATH . '/core/core.php');

//define('WEBIM_URL', SITE_URL . '/addons/plugin/Webim');

/**
 * Configuration
 */
$IMC = require('config.php');

$_CFG = model('Xdata')->get('hook_webim_plugin:config');

if( $_CFG && count($_CFG) > 0 ) {
    
    $IMC = array_merge($IMC, $_CFG); 

}

if( !$IMC['isopen'] ) exit('WebIM Not Opened');

/**
 * Init database
 */
$IMC['dbuser'] = explode(",", C('DB_USER'))[0];
$IMC['dbpassword'] =explode(",", C('DB_PWD'))[0];
$IMC['dbname'] = explode(",", C('DB_NAME'))[0];
$IMC['dbhost'] = explode(",",C('DB_HOST'))[0];
$IMC['dbprefix'] =  C('DB_PREFIX') . 'webim_';

function WEBIM_PATH() {
	global $_SERVER;
    $name = htmlspecialchars($_SERVER['SCRIPT_NAME'] ? $_SERVER['SCRIPT_NAME'] : $_SERVER['PHP_SELF']); 
    return substr( $name, 0, strrpos( $name, '/' ) ) . "/";
}

function WEBIM_IMAGE($img) {
    return WEBIM_PATH() . "static/images/{$img}";
}

/**
 * -----------------------
 * end
 * -----------------------
 */

if($IMC['debug']) { define('WEBIM_DEBUG', true); } 

// Modify error reporting levels to exclude PHP notices

if( defined('WEBIM_DEBUG') ) {
	error_reporting( E_ALL );
} else {
	//新增& ~E_DEPRECATED 修复php5.5中mysql_connect模块过时警告的问题
	error_reporting( E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED );
}

if( !$IMC['isopen'] ) exit();

/**
 * load libraries
 */
if( $IMC['visitor'] ) {
    require 'lib/GeoIP.class.php';
}

/**
 *
 * WebIM Libraries
 *
 * https://github.com/webim/webim-for-php4
 *
 */
require 'lib/http_client.php';
require 'lib/webim_client.class.php';
require 'lib/webim_common.func.php';
require 'lib/webim_db.class.php';
require 'lib/webim_model.class.php';
require 'lib/webim_plugin.class.php';
require 'lib/webim_router.class.php';
require 'lib/webim_app.class.php';

require 'webim_plugin_thinksns.class.php';

/**
 * webim route
 */
$app = new webim_app();

$app->plugin(new webim_plugin_thinksns());

$app->model(new webim_model());

$app->run();

?>
