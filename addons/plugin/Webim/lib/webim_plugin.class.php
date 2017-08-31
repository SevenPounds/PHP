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

class webim_plugin {

    /**
     * constructor
     */
    function __construct() {
        global $IMC;
    }

    function webim_plugin() {
        $this->__construct();
    }

    /**
     * API: current user
     *
     * TODO: demo code
     *
     * @return object current user
     */
    function user() {
        global $_SESSION;
		$uid = isset($_SESSION['uid']) ? $_SESSION['uid'] : null;
        if( !$uid ) return null;
		return (object)array(
            'id' => $uid,
            'nick' => 'user' . $uid,
            'presence' => 'online',
            'show' => "available",
            'avatar' => WEBIM_IMAGE('male.png'),
            'url' => "#",
            'role' => 'user',
            'status' => "",
        );
    }

	/*
	 * API: Buddies of current user.
     *
     * @param string $uid current uid
	 *
     * @return array Buddy list or current user
     *
     * TODO: DEMO Code
     *
	 * Buddy:
	 *
	 * 	id:         uid
	 *	nick:       nick
	 *	avatar:    url of photo
     *	presence:   online | offline
	 *	show:       available | unavailable | away | busy | hidden
	 *  url:        url of home page of buddy 
	 *  status:     buddy status information
	 *  group:      group of buddy
	 *
	 */
	function buddies($uid) {
        $ids = range(1, 10);
        $buddies = array();
        foreach($ids as $id) {
            $buddies[] = (object)array(
                'id' => $id,
                'nick' => 'user' . $id,
                'group' => 'friend',
                'presence' => 'offline',
                'show' => 'unavailable',
                'status' => '#',
                'avatar' => WEBIM_IMAGE('male.png')
            );
        }
        return $buddies;
	}

	/*
	 * API: buddies by ids
     *
     * TODO: DEMO Code
	 *
     * @param string $uid 
     * @param array $ids buddy id array
     *
     * @return array Buddy list
     *
	 * Buddy
	 */
	function buddies_by_ids($uid, $ids) {
        $buddies = array();
        foreach($ids as $id) {
            $buddies[] = (object)array(
                'id' => $id,
                'group' => 'friend',
                'nick' => 'user' . $id,
                'presence' => 'offline',
                'show' => 'unavailable',
                'status' => '#',
                'avatar' => WEBIM_IMAGE('male.png')
            );
        
        }
        return $buddies;
	}

	/*
	 * API：rooms of current user
     *
     * TODO: DEMO Code
     * 
     * @param string $uid 
     *
     * @return array rooms
     *
	 * Room:
	 *
	 *	id:		    Room ID,
	 *	nick:	    Room Nick
	 *	url:	    Home page of room
	 *	avatar:     Pic of Room
	 *	status:     Room status 
	 *	count:      count of online members
	 *	all_count:  count of all members
	 *	blocked:    true | false
	 */
	function rooms($uid) {
		$room = (object)array(
			'id' => 'room',
			'nick' => 'Room',
			'url' => "#",
			'avatar' => WEBIM_IMAGE('room.png'),
			'status' => "Room",
			'blocked' => false,
            'temporary' => false
		);
		return array( $room );	
	}

	/*
	 * API: rooms by ids
     *
     * TODO: DEMO Code
     *
     * @param string $uid 
     * @param array $ids 
     *
     * @return array rooms
	 *
	 * Room
     *
	 */
	function rooms_by_ids($uid, $ids) {
        $rooms = array();
        foreach($ids as $id) {
            if($id === 'room') { 
                $rooms[] = (object)array(
                    'id' => $id,
                    'nick' => 'Room',
                    'url' => "#",
                    'avatar' => WEBIM_IMAGE('room.png')
                );
            }
        }
		return $rooms;
	}

    /**
     * API: members of room
     *
     * TODO: DEMO Code
     *
     * $param $room string roomid
     * 
     */
    function members($room) {
        $ids = range(1, 10);
        $members = array();
        foreach($ids as $id) {
            $members[] = (object)array(
                'id' => "$id",
                'nick' => 'user' . $id
            ); 
        }
        return $members;
    }

	/*
	 * API: notifications of current user
     *
     * TODO: DEMO Code
	 *
     * @return array  notification list
     *
	 * Notification:
	 *
	 * 	text: text
	 * 	link: link
	 */	
	function notifications($uid) {
		return array();
	}

    /**
     * API: menu
     *
     * TODO: DEMO Code
     *
     * @return array menu list
     *
     * Menu:
     *
     * icon
     * text
     * link
     */
    function menu($uid) {
        return array();
    }

}

?>
