<?php

/**
 * WebIM-for-PHP4 
 *
 * @author      Ery Lee <ery.lee@gmail.com>
 * @copyright   2014 NexTalk.IM
 * @link        http://github.com/webim/webim-for-php4
 * @license     MIT LICENSE
 * @version     5.4.1
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
 * WebIM Data Model
 *
 * @package WebIM
 * @autho Ery Lee
 * @since 5.4.1
 */


define( "WEBIM_HISTORY_KEYS", "`to`,`nick`,`from`,`style`,`body`,`type`,`timestamp`" );

class webim_model {

    var $db;

    var $geoip;

    function __construct() {
        global $IMC;
        $this->db = new webim_db( $IMC['dbuser'], $IMC['dbpassword'], $IMC['dbname'], $IMC['dbhost'] );
        // Die if MySQL is not new enough
        if ( version_compare($this->db->db_version(), '4.1.2', '<') ) {
            die( sprintf( 'WebIM requires MySQL 4.1.2 or higher' ) );
        }
        $this->db->set_prefix( $IMC['dbprefix'] );
        $this->db->add_tables( array( 'settings', 'histories', 'rooms', 'members', 'visitors', 'blocked' ) );
        if( $IMC['visitor'] ) $this->geoip = new GeoIP();
    }

    function webim_model() {
        $this->__construct();
    }

    /**
     * Get histories 
     *
     * @params string $uid current uid
     * @params string $with the uid that talk with
     * @params 'chat'|'grpchat' $type history type
     * @params integer $limit result limit
     */
    function histories($uid, $with, $type = 'chat',  $limit = 50) {
        if( $type == "chat" ){
            $query = $this->db->prepare( "SELECT " . WEBIM_HISTORY_KEYS . " FROM {$this->db->histories}  
                WHERE `type` = 'chat' 
                AND ((`to`=%s AND `from`=%s AND `fromdel` != 1) 
                OR (`send` = 1 AND `from`=%s AND `to`=%s AND `todel` != 1))  
                ORDER BY timestamp DESC LIMIT %d", $with, $uid, $with, $uid, $limit );
        } else {
            $query = $this->db->prepare( "SELECT " . WEBIM_HISTORY_KEYS . " FROM {$this->db->histories}  
                WHERE `to`=%s AND `type`='grpchat' AND send = 1 
                ORDER BY timestamp DESC LIMIT %d", $with, $limit );
        }
        return array_reverse( $this->db->get_results( $query ) );
    }

    /**
     * Get offline histories
     *
     * @params string $uid current uid
     * @params integer $limit result limit
     */
	function offline_histories($uid, $limit = 50) {
        $query = $this->db->prepare( "SELECT " . WEBIM_HISTORY_KEYS . " FROM {$this->db->histories}  WHERE `to`=%s and send != 1 
            ORDER BY timestamp DESC LIMIT %d", $uid, $limit );
        return array_reverse( $this->db->get_results( $query ) );
    }

    /**
     * Save history
     *
     * @params array $message message object
     */
    function insert_history($message) {
        $message['created'] = date( 'Y-m-d H:i:s' ); 
        $this->db->insert($this->db->histories, $message );
    }

    /**
     * Clear histories
     *
     * @params string $uid current uid
     * @params string $with user that talked with
     */
    function clear_histories($uid, $with) {
        $this->db->update( $this->db->histories, array( "fromdel" => 1, "type" => "chat" ), array( "from" => $uid, "to" => $with ) );
        $this->db->update( $this->db->histories, array( "todel" => 1, "type" => "chat" ), array( "to" => $uid, "from" => $with ) );
        $this->db->query( $this->db->prepare( "DELETE FROM {$this->db->histories} WHERE todel=1 AND fromdel=1" ) );
    }

    /**
     * Offline histories readed
     *
     * @param string $uid user id
     */
	function offline_readed($uid) {
        $this->db->update( $this->db->histories, 
            array( "send" => 1 ), 
            array( "to" => $uid, "send" => 0 )
        );
    }


    /**
     * User setting
     *
     * @param string @uid userid
     * @param string @data json 
     *
     * @return object|null
     */
    function setting($uid, $data = null) {
        $setting = $this->db->get_row($this->db->prepare( "SELECT * FROM {$this->db->settings} WHERE uid = %d", $uid ));
        if(func_num_args() === 1) {//get setting
            if($setting) return json_decode($setting->data);
            return new stdClass();
        } 
        if($setting) {
            if(is_string( $data )) {
                $data = stripcslashes( $data );
            } else {
                $data = json_encode( $data );
            }
            $this->db->update($this->db->settings, array( "data" => $data, 'updated' => date('Y-m-d H:i:s')), array( 'uid' => $uid ));
        } else {
            $this->db->insert($this->db->settings, array(
                'uid' => $uid,
                'data' => $data,
                'created' => date( 'Y-m-d H:i:s' )
            ));
        }
    }

    /**
     * All rooms of the user
     *
     * @param string $uid user id
     * @return array rooms array
     */
    function rooms($uid) {

        $query = $this->db->prepare("SELECT t1.room as name, t2.nick as nick from {$this->db->members} t1 left join {$this->db->rooms} t2 on t1.room = t2.name where t1.uid = %s", $uid);
        $rooms = array();
        foreach($this->db->get_results($query) as $row) {
            $rooms[] = (object)array(
                'id' => $row->name,
                'nick' => $row->nick,
                "url" => "#", //TODO
                "avatar" => WEBIM_IMAGE('room.png'),//TODO
                "status" => '',
                "temporary" => true,
                "blocked" => false
            );
        }
        return $rooms;
    }

    /**
     * Get rooms by ids
     *
     * @param array $ids id list
     * @return array rooms
     */
    function rooms_by_ids($uid, $ids) {
        if($ids === '' || empty($ids)) return array();
        $in_ids = array();
        foreach($ids as $id) { $in_ids[] = "'$id'"; }
        $in_ids = implode(',', $in_ids);
        $query = $this->db->prepare("SELECT name as id, nick, url from {$this->db->rooms} where name in ({$in_ids})");
        $rooms = array();
        foreach($this->db->get_results($query) as $row) {
            $rooms[] = (object)array(
                'id' => $row->id,
                'nick' => $row->nick,
                "url" => "#", //TODO
                "avatar" => WEBIM_IMAGE('room.png'),//TODO
                "status" => '',
                "temporary" => true,
                "blocked" => false
            );
        }
        return $rooms;
    }

    /**
     * Members of room
     *
     * @param string $room room id
     * @return array members array
     */
    function members($room) {
        $query = $this->db->prepare("SELECT uid as id, nick FROM {$this->db->members} WHERE room = %s", $room);
        return $this->db->get_results($query);
    }

    /**
     * Create room
     *
     * @param array $data room data
     * @return Room as array
     */
    function create_room($data) {
        $data['created']  = date( 'Y-m-d H:i:s' ); 
        $this->db->insert($this->db->rooms, $data);
        return (object)$data;
    }

    /**
     * Invite members to join room
     *
     * $param string $room room id
     * $param array $members member array
     */
    function invite_room($room, $members) {
        foreach($members as $member) {
            $this->join_room($room, $member->id, $member->nick);
        }
    }

    /**
     * Join room
     *
     * $param string $room room id
     * $param string $uid user id
     * $param string $nick user nick
     */
    function join_room($room, $uid, $nick) {
        $query = $this->db->prepare("SELECT id FROM {$this->db->members} WHERE uid = %s and room = %s", $uid, $room);
        $id = $this->db->get_var($query);
        if(!$id) {
            $data = array(
                'uid' => $uid,
                'room' => $room, 
                'nick' => $nick,
                'joined' => date('Y-m-d H:i:s')
            );
            $this->db->insert($this->db->members, $data);
        }
    }

    /**
     * Leave room
     *
     * $param string $room room id
     * $param string $uid user id
     */
    function leave_room($room, $uid) {
        $this->db->query( $this->db->prepare( "DELETE FROM {$this->db->members} WHERE room = %s and uid = %s", $room, $uid ) );
        $query = $this->db->prepare("SELECT count(id) as total from {$this->db->members} WHERE room = %s", $room);
        $total = $this->db->get_var($query);
        if($total && $total === 0) {
            $this->db->query($this->db->prepare("DELETE FROM {$this->db->rooms} WHERE name = %s", $room));
        }
    }

    /**
     * Block room
     *
     * $param string $room room id
     * $param string $uid user id
     */
    function block_room($room, $uid) {
        $row = array(
            'room' => $room,
            'uid' => $uid,
            'blocked' => date('Y-m-d H:i:s')
        );
        $this->db->insert($this->db->blocked, $row);
    }

    /**
     * Is room blocked
     *
     * $param string $room room id
     * $param string $uid user id
     *
     * @return true|false
     */
    function is_room_blocked($room, $uid) {
        $query = $this->db->prepare("SELECT id FROM {$this->db->blocked} WHERE room = %s and uid = %s", $room, $uid);
        $id = $this->db->get_var($query);
        return $id && $id > 0;
    }


    /**
     * Unblock room
     *
     * @param string $room room id
     * @param string $uid user id
     */
    function unblock_room($room, $uid) {
        $query = $this->db->prepare("DELETE FROM {$this->webim_blocked} WHERE room = %s and uid = %s", $room, $uid);
        $this->db->query($query);
    }

    /**
     * Get visitor
     */
    function visitor() {
        global $_COOKIE, $_SERVER;
        if (isset($_COOKIE['_webim_visitor_id'])) {
            $id = $_COOKIE['_webim_visitor_id'];
        } else {
            $id = substr(uniqid(), 6);
            setcookie('_webim_visitor_id', $id, time() + 3600 * 24 * 30, "/", "");
        }
        $vid = 'vid:'. $id;
        $visitor = $this->db->get_row($this->db->prepare( "SELECT * FROM {$this->db->visitors} WHERE name = %s", $vid ));
        if( !$visitor ) {
            $ipaddr = isset($_SERVER['X-Forwarded-For']) ? $_SERVER['X-Forwarded-For'] : $_SERVER["REMOTE_ADDR"];
            $loc = $this->geoip->find($ipaddr);
            if(is_array($loc)) $loc = implode('',  $loc);
            $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            $this->db->insert($this->db->visitors, array(
                "name" => $vid,
                "ipaddr" => $ipaddr,
                "url" => $_SERVER['REQUEST_URI'],
                "referer" => $referer,
                "location" => $loc,
                "created" => date( 'Y-m-d H:i:s' )
            ));
        }
        return (object) array(
            'id' => $vid,
            'nick' => "v".$id,
            'group' => "visitor",
            'presence' => 'online',
            'show' => "available",
            'avatar' => WEBIM_IMAGE('male.png'),
            'role' => 'visitor',
            'url' => "#",
            'status' => "",
        );
    }

    /**
     * visitors by vids
     */
    function visitors($vids) {
        if( count($vids)  == 0 ) return array();
        $vids = implode("','", $vids);
        $query = $this->db->prepare("SELECT name, ipaddr, location from {$this->db->visitors} where name in (%s)", $vids);
        $visitors = array();
        foreach($this->db->get_results($query) as $v) {
            $status = $v->location;
            if( $v->ipaddr ) $status = $status . '(' . $v->ipaddr .')';
            $visitors[] = (object)array(
                "id" => $v->name,
                "nick" => "v".substr($v->name, 4), //remove vid:
                "group" => "visitor",
                "url" => "#",
                "avatar" => WEBIM_IMAGE('male.png'),
                "status" => $status, 
            );
        }
        return $visitors;
    }

}

?>
