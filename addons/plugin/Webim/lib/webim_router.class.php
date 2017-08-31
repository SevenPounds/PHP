<?php

/**
 * WebIM-for-PHP4 
 *
 * @author      Ery Lee <ery.lee@gmail.com>
 * @copyright   2014 NexTalk.IM
 * @link        http://github.com/webim/webim-for-php4
 * @license     MIT LICENSE
 * @version     5.5
 * @package     WebIM
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/**
 * WebIM Router
 *
 * @package WebIM
 * @autho Ery Lee
 * @since 5.4.1
 */

class webim_router {

    /**
     * current user
     */
    var $user = null;

	/*
	 * WebIM Model
	 */
	var $model;

	/*
	 * WebIM Plugin
	 */
	var $plugin;

	/*
	 * WebIM Client
	 */
	var $client;

    function __construct() {
    }

    function webim_router() {
        $this->__construct();
    }

    /**
     * Plugin Get/Set
     */
    function plugin($plugin = null) {
        if (func_num_args() === 0) {
            return $this->plugin;
        }
        $this->plugin = $plugin; 
    }

    /**
     * Model Get/Set
     */
    function model($model = null) {
        if (func_num_args() === 0) {
            return $model;
        }
        $this->model = $model;
    }

    /**
     * Route and dispatch ajax request
     */
    function route() {

        global $IMC;

        $user = $this->plugin->user();
        if($user == null &&  $IMC['visitor']) {
            $user = $this->model->visitor();
        }
        if(!$user) exit(json_encode("Login Required"));

        //webim user
        $this->user = $user;

		//webim ticket
		$ticket = $this->input('ticket');
		if($ticket) $ticket = stripslashes($ticket);	

		//webim client
        $this->client = new webim_client(
            $this->user,
            $ticket,
            $IMC['domain'], 
            $IMC['apikey'], 
            $IMC['host'], 
            $IMC['port']
        );
        $method = $this->input('action');
        if( !$method ) $method = 'index'; 
        if($method && method_exists($this, $method)) {
            call_user_func(array($this, $method));
        } else {
            header( "HTTP/1.0 400 Bad Request" );
            exit("Action Not Found.");
        }
    }

    /**
     * Index page
     */
    function index() {
        if( isset($_GET['uid']) ) $_SESSION['uid'] = 'uid' . $_GET['uid'];
        echo '<html>';
        echo '<head> <title>WebIM for PHP4</title></head>';
        echo '<body>';
        echo '<center><h1>WebIM for PHP4</h1></center>';
        echo '<script type="text/javascript" src="' . WEBIM_PATH() . 'index.php?action=boot"></script>';
        echo '</body>';
        echo '</html>';
    }

    /**
     * Boot Javascript
     */
	function boot() {
        global $IMC;
        $fields = array(
            'theme',
            'local',
            'emot',
            'opacity',
            'enable_room', 
            'discussion',
            'enable_chatlink', 
            'enable_shortcut',
            'enable_noti',
            'enable_menu',
            'show_unavailable',
            'upload');
        //from webim_plugin_discuzx
        $this->user->show = "unavailable";
        $uid = $this->user->id;

        $webim_path = WEBIM_PATH();
        if( substr($webim_path, strlen($webim_path)-1) !== '/' ) {
            $webim_path .= '/';
        }

        $scriptVar = array(
            'version' => WEBIM_VERSION,
            'product' => WEBIM_PRODUCT,
            'path' => $webim_path,
            'is_login' => '1',
            'is_visitor' => webim_isvid($uid),
            'login_options' => '',
            'user' => $this->user,
            'menu' => $this->plugin->menu($uid),
            'setting' => $this->model->setting($uid),
            'jsonp' => false,
            'min' => WEBIM_DEBUG ? "" : ".min"
        );
        foreach($fields as $f) { $scriptVar[$f] = $IMC[$f];	}

        header("Content-type: application/javascript");
        header("Cache-Control: no-cache");
        echo "var _IMC = " . json_encode($scriptVar) . ";" . PHP_EOL;
        $script = <<<EOF
_IMC.script = window.webim ? '' : ('<link href="' + _IMC.path + 'static/webim' + _IMC.min + '.css?' + _IMC.version + '" media="all" type="text/css" rel="stylesheet"/><link href="' + _IMC.path + 'static/themes/' + _IMC.theme + '/jquery.ui.theme.css?' + _IMC.version + '" media="all" type="text/css" rel="stylesheet"/><script src="' + _IMC.path + 'static/webim' + _IMC.min + '.js?' + _IMC.version + '" type="text/javascript"></script><script src="' + _IMC.path + 'static/i18n/webim-' + _IMC.local + '.js?' + _IMC.version + '" type="text/javascript"></script>');
_IMC.script += '<script src="' + _IMC.path + 'static/webim.' + _IMC.product + '.js?vsn=' + _IMC.version + '" type="text/javascript"></script>';
document.write( _IMC.script );

EOF;
        exit($script);
    }

    /**
     * Online
     */
	function online() {
        global $IMC;

        $uid = $this->user->id;
        $show = $this->input('show');

        //buddy, room, chatlink ids
		$chatlink_ids= $this->ids_array($this->input('chatlink_ids', '') );
		$active_room_ids = $this->ids_array( $this->input('room_ids') );
		$active_buddy_ids = $this->ids_array( $this->input('buddy_ids') );

		//active buddy who send a offline message.
		$offline_messages = $this->model->offline_histories($uid);
		foreach($offline_messages as $msg) {
			if(!in_array($msg->from, $active_buddy_ids)) {
				$active_buddy_ids[] = $msg->from;
			}
		}
        //buddies of uid
		$buddies = $this->plugin->buddies($uid);
        $buddy_ids = array();
        foreach($buddies as $buddy) {
            $buddy_ids[] = $buddy->id;
        }

        //buddies ids without info
        $buddy_ids_withoutinfo = array();
        foreach(array_merge($chatlink_ids, $active_buddy_ids) as $id) {
            if( !in_array($id, $buddy_ids) ) $buddy_ids_withoutinfo[] = $id;
        }
        //buddies by ids
        $buddies_by_ids = array_merge(
            $this->plugin->buddies_by_ids($uid, $buddy_ids_withoutinfo),
            $this->model->visitors($buddy_ids_withoutinfo)
        );

        //all buddies
        $buddies = array_merge($buddies, $buddies_by_ids);

        $all_buddy_ids = array();
        foreach($buddies as $buddy) { $all_buddy_ids[] = $buddy->id; }

        $rooms = array(); $room_ids = array();
		if( $IMC['enable_room'] ) {
            //persistent rooms
			$persist_rooms = $this->plugin->rooms($uid);
            //temporary rooms
			$temporary_rooms = $this->model->rooms($uid);
            $rooms = array_merge($persist_rooms, $temporary_rooms);
            foreach($rooms as $room) { $room_ids[] = $room->id; }
        }

		//===============online===============
		$data = $this->client->online($all_buddy_ids, $room_ids, $show);
		if( $data->success ) {
            $rt_buddies = array();
            $presences = $data->presences;
            foreach($buddies as $buddy) {
                $id = $buddy->id;
                if( isset($presences->$id) ) {
                    $buddy->presence = 'online';
                    $buddy->show = $presences->$id;
                } else {
                    $buddy->presence = 'offline';
                    $buddy->show = 'unavailable';
                }
                $rt_buddies[$id] = $buddy;
            }
			//histories for active buddies and rooms
			foreach($active_buddy_ids as $id) {
                if( isset($rt_buddies[$id]) ) {
                    $rt_buddies[$id]->history = $this->model->histories($uid, $id, "chat" );
                }
			}
            if( !$IMC['show_unavailable'] ) {
                $online_buddies = array();
                foreach($rt_buddies as $buddy) {
                    if($buddy->presence === 'online') {
                        $online_buddies[$buddy->id] = $buddy;
                    }
                }
                $rt_buddies = $online_buddies;
            }
            $rt_rooms = array();
            if( $IMC['enable_room'] ) {
                foreach($rooms as $room) {
                    $rt_rooms[$room->id] = $room;
                }
                foreach($active_room_ids as $id) {
                    if( isset($rt_rooms[$id]) ) {
                        $rt_rooms[$id]->history = $this->model->histories($uid, $id, "grpchat" );
                    }
                }
            }
			$this->model->offline_readed($uid);
            if( $show ) $this->user->show = $show;
            //TODO: FIX Nginx reverse proxy
//             $conn = $data->connection;
//             if(isset($IMC['server_proxy'])) {
//             	$conn['server'] = "http://{$IMC['server_proxy']}/v5/packets";
//             	$conn['jsonpd'] = "http://{$IMC['server_proxy']}/v5/packets";
//             	if(isset($conn['websocket'])) {
//             		$conn['websocket'] = "ws://{$IMC['server_proxy']}/v5/wsocket";
//             	}
            
//             }
            $this->json_reply(array(
                'success' => true,
                'connection' => $data->connection,
                'user' => $this->user,
                'presences' => $data->presences,
                'buddies' => array_values($rt_buddies),
                'rooms' => array_values($rt_rooms),
                'new_messages' => $offline_messages,
                'server_time' => $this->microtime_float() * 1000
            ));
		} else {
			$this->json_reply( array( 
				'success' => false,
                'error' => $data->error
            )); 
        }
    }

    /**
     * Offline
     */
	function offline() {
		$this->client->offline();
		$this->ok_reply();
	}

    /**
     * Browser Refresh, may be called
     */
	function refresh() {
		$this->client->offline();
		$this->ok_reply();
	}

    /**
     * Buddies by ids
     */
	function buddies() {
		$ids = $this->input('ids');
        $ids = explode(',', $ids);
        $buddies = array_merge(
            $this->plugin->buddies_by_ids($this->user->id, $ids),
            $this->model->visitors($ids)
        );
		$this->json_reply($buddies);
	}

    /**
     * Send Message
     */
	function message() {
		$type = $this->input("type");
		$offline = $this->input("offline");
		$to = $this->input("to");
		$body = stripslashes( $this->input("body") );
        if( defined('WEBIM_MESSAGE_DECODE') ) {
            $body = html_entity_decode($body);
        }
		$style = $this->input("style");
		$send = $offline == "true" || $offline == "1" ? 0 : 1;
		$timestamp = $this->microtime_float() * 1000;
		if( strpos($body, "webim-event:") !== 0 ) {
            $this->model->insert_history(array(
				"send" => $send,
				"type" => $type,
				"to" => $to,
                'from' => $this->user->id,
                'nick' => $this->user->nick,
				"body" => $body,
				"style" => $style,
				"timestamp" => $timestamp,
			));
		}
		if($send == 1){
			$this->client->message(null, $to, $body, $type, $style, $timestamp);
		}
        //Error Reply
        //$this->json_reply(array('status' => 'error', 'message' => $body));
		$this->ok_reply();
    }

    /**
     * Update Presence
     */
	function presence() {
		$show = $this->input('show');
		$status = $this->input('status');
		$this->client->presence($show, $status);
		$this->ok_reply();
    }

    /**
     * Send Status
     */
	function status() {
		$to = $this->input("to");
		$show = $this->input("show");
		$this->client->status($to, $show);
		$this->ok_reply();
    }

    /**
     * Read History
     */
	function history() {
		$with = $this->input('id');
		$type = $this->input('type');
		$histories = $this->model->histories($this->user->id, $with, $type);
		$this->json_reply($histories);
    }

    /**
     * Clear History
     */
	function clear_history() {
		$with = $this->input('id');
		$this->model->clear_histories($this->user->id, $with);
		$this->ok_reply();
    }

    /**
     * Download History
     */
	function download_history() {
		$id = $this->input('id');
		$type = $this->input('type');
		$histories = $this->model->histories($this->user->id, $id, $type, 1000 );
		$date = date( 'Y-m-d' );
		if($this->input('date')) {
			$date = $this->input('date');
		}
		header('Content-Type',	'text/html; charset=utf-8');
		header('Content-Disposition: attachment; filename="histories-'.$date.'.html"');
		echo "<html><head>";
		echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
		echo "</head><body>";
		echo "<h1>Histories($date)</h1>".PHP_EOL;
		echo "<table><thead><tr><td>用户</td><td>消息</td><td>时间</td></tr></thead><tbody>";
		foreach($histories as $history) {
			$nick = $history->nick;
			$body = $history->body;
			$style = $history->style;
			$time = date( 'm-d H:i', (float)$history->timestamp/1000 ); 
			echo "<tr><td>{$nick}:</td><td style=\"{$style}\">{$body}</td><td>{$time}</td></tr>";
		}
		echo "</tbody></table>";
		echo "</body></html>";
    }

    /**
     * Get rooms
     */
	function rooms() {
        $uid = $this->user->id;
		$ids = $this->input("ids");
        $ids = explode(',', $ids);
        $persist_rooms = $this->plugin->rooms_by_ids($uid, $ids);
        $temporary_rooms = $this->model->rooms_by_ids($uid, $ids);
		$this->json_reply(array_merge($persist_rooms, $temporary_rooms));	
    }

    /**
     * Invite room
     */
    function invite() {
        $room_id = $this->input('id');
        $nick = $this->input('nick');
        if(strlen($nick) === 0) {
			header("HTTP/1.0 400 Bad Request");
			exit("Nick is Null");
        }
        //find persist room 
        $room = $this->find_room($this->model, $room_id);
        if(!$room) {
            //create temporary room
            $room = $this->model->create_room(array(
                'owner' => $this->user->id,
                'name' => $room_id,
                'nick' => $nick
            ));
        }
        //join the room
        $this->model->join_room($room_id, $this->user->id, $this->user->nick);
        //invite members
        $members = explode(",", $this->input('members'));
        $members = array_merge(
            $this->plugin->buddies_by_ids($this->user->id, $members),
            $this->model->visitors($members)
        );
        $this->model->invite_room($room_id, $members);
        //send invite message to members
        foreach($members as $m) {
            $body = "webim-event:invite|,|{$room_id}|,|{$nick}";
            $this->client->message(null, $m->id, $body); 
        }
        //tell server that I joined
        $this->client->join($room_id);
        $this->json_reply(array(
            'id' => $room->name,
            'nick' => $room->nick,
            'temporary' => true,
            'avatar' => WEBIM_IMAGE('room.png')
        ));
    }

    /**
     * Join room
     */
	function join() {
        $room_id = intval($this->input('id'));
        $nick = $this->input('nick');
        $room = $this->find_room($this->plugin, $room_id);
        if(!$room) {
            $room = $this->find_room($this->model, $room_id);
        }
        if(!$room) {
			header("HTTP/1.0 404 Not Found");
			exit("Can't found room: {$room_id}");
        }
        $this->model->join_room($room_id, $this->user->id, $this->user->nick);
        $data = $this->client->join($room_id);
        $this->json_reply(array(
            'id' => $room_id,
            'nick' => $nick,
            'temporary' => true,
            'avatar' => WEBIM_IMAGE('room.png')
        ));
    }

    /**
     * Leave room
     */
	function leave() {
		$room = $this->input('id');
		$this->client->leave( $room );
        $this->model->leave_room($room, $this->user->id);
		$this->ok_reply();
    }

    /**
     * room members
     */
	function members() {
        $members = array();
        $room_id = $this->input('id');
        $room = $this->find_room($this->plugin, $room_id);
        if($room) {
            $members = $this->plugin->members($room_id);
        } else {
            $room = $this->find_room($this->model, $room_id);
            if($room) {
                $members = $this->model->members($room_id);
            }
        }
        if(!$room) {
			header("HTTP/1.0 404 Not Found");
			exit("Can't found room: {$room_id}");
        }
        $presences = $this->client->members($room_id);
        $rtMembers = array();
        foreach($members as $m) {
            $id = $m->id;
            if(isset($presences->$id)) {
                $m->presence = 'online';
                $m->show = $presences->$id;
            } else {
                $m->presence = 'offline';
                $m->show = 'unavailable';
            }
            $rtMembers[] = $m;
        }
        usort($rtMembers, array($this, 'sort_by_presence'));
        $this->json_reply($rtMembers);
    }

    function sort_by_presence($m1, $m2) {
        if($m1->presence === $m2->presence) return 0;
        if($m1->presence === 'online') return -1;
        return 1;
    }
    
    /**
     * find room
     */
    function find_room($from, $id) {
        $uid = $this->user->id;
        $rooms = $from->rooms_by_ids($uid, array($id));
        if($rooms && isset($rooms[0])) return $rooms[0];
        return null;
    }

    /**
     * Block room
     */
    function block() {
        $room = $this->input('id');
        $this->model->block_room($room, $this->user->id);
        $this->ok_reply();
    }

    /**
     * Unblock room
     */
    function unblock() {
        $room = $this->input('id');
        $this->model->unblock_room($room, $this->user->id);
        $this->ok_reply();
    }

    /**
     * Notifications
     */
	function notifications() {
		$notifications = $this->plugin->notifications($this->user->id);
		$this->json_reply($notifications);
    }

    /**
     * Setting
     */
    function setting() {
        $data = $this->input('data');
		$this->model->setting($this->user->id, $data);
		$this->ok_reply();
    }

    /**
     * openchat
     */
    function openchat() {
        $room = $this->input("room");
        $nick = $this->input("nick");
        $this->client->openchat($room, $nick);
    }

    /**
     * closechat
     */
    function closechat() {
        $room = $this->input("room");
        $nick = $this->input("nick");
        $this->client->closechat($room, $nick);
    }
 
    /*-------------------------------------------
     * private
     -------------------------------------------*/
	function input($name, $default = null) {
		if( isset( $_POST[$name] ) ) return $_POST[$name];
		if( isset( $_GET[$name] ) ) return $_GET[$name]; 
		return $default;
	}

	function ok_reply() {
		$this->json_reply('ok');
	}

	function json_reply($data) {
		header('Content-Type:application/json; charset=utf-8');
		exit(json_encode($data));
	}

	function ids_array( $ids ){
		return ($ids===null || $ids==="") ? array() : (is_array($ids) ? array_unique($ids) : array_unique(explode(",", $ids)));
	}

    function microtime_float() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

}

?>
