<?php

/**
 * PHP4 WebIM Library for interacting with NexTalk server.
 *
 * Author: Hidden <zzdhidden@gmail.com>
 * Date: Mon Aug 23 15:15:41 CST 2010
 *
 *
 */

class webim_client
{
    var $apivsn = "v5";

	var $user;
	var $domain;
	var $apikey;
	var $host;
	var $port;
	var $client;
	var $ticket;
	var $version = 5;

	/**
	 * New
	 *
	 * @param object $user
	 * 	-id:
	 * 	-nick:
	 * 	-show:
	 * 	-status:
	 *
	 * @param string $ticket
	 * @param string $domain
	 * @param string $apikey
	 * @param string $host
	 * @param string $port
	 *
	 */

	function __construct($user, $ticket, $domain, $apikey, $host, $port = 8000) {
		register_shutdown_function( array( &$this, '__destruct' ) );
		$this->user = $user;
		$this->domain = trim($domain);
		$this->apikey = trim($apikey);
		$this->ticket = trim($ticket);
		$this->host = trim($host);
		$this->port = trim($port);
		$this->client = new HttpClient($this->host, $this->port);
        $this->client->setAuthorization($this->domain, $this->apikey);
	}

	function webim_client($user, $ticket, $domain, $apikey, $host, $port = 8000) {
		return $this->__construct( $user, $ticket, $domain, $apikey, $host, $port );
	}

	/**
	 * PHP5 style destructor and will run when database object is destroyed.
	 *
	 * @see webim_client::__construct()
	 * @return bool true
	 */
	function __destruct() {
		return true;
	}
    
	/**
	 * User online
	 *
	 * @param string $buddy_ids
	 * @param string $room_ids
	 *
	 * @return object
	 * 	-success: true
	 * 	-connection:
	 * 	-user:
	 * 	-buddies: [&buddyInfo]
	 * 	-groupss: [&groupInfo]
	 * 	-error_msg:
	 *
	 */
	function online($buddy_ids, $room_ids, $show = null){
        if(is_array($buddy_ids)) $buddy_ids = implode(',', $buddy_ids);
        if(is_array($room_ids)) $room_ids = implode(',', $room_ids);
        if( !$show ) $show = $this->user->show;
		$data = array(
			'version' => $this->version,
			'rooms'=> $room_ids, 
			'buddies'=> $buddy_ids, 
			'domain' => $this->domain, 
			'name'=> $this->user->id, 
			'nick'=> $this->user->nick, 
			'status'=> $this->user->status, 
			'show' => $show
		);
		$this->client->post($this->apiurl('presences/online'), $data);
		$cont = $this->client->getContent();
		$da = json_decode($cont);
        $code = $this->client->status;
		if( $code != "200" || empty($da->ticket)){
			return (object)array("success" => false, "error" => "status: {$code}, content: {$cont}");
		}
        $ticket = $da->ticket;
        $this->ticket = $ticket;
        $connection = array(
            "ticket" => $ticket,
            "domain" => $this->domain,
            "server" => $da->jsonpd,
            "jsonpd" => $da->jsonpd
        );
        //if websocket 
//         if(isset($da->websocket)) $connection['websocket'] = $da->websocket;
        //if mqttd
        if(isset($da->mqttd)) $connection['mqttd'] = $da->mqttd;
        return (object)array(
            "success" => true, 
            "connection" => (object)$connection, 
            "presences" => $da->presences 
        );
	}

	/**
	 * User offline
	 *
	 * @return ok
	 */

	function offline(){
		$data = array(
			'version' => $this->version,
			'ticket' => $this->ticket,
			'domain' => $this->domain
		);
		$this->client->post($this->apiurl('presences/offline'), $data);
		return $this->client->getContent();
	}


    /**
     * Get presences
     *
     * @param $ids
     *
     * @return object
     */
    function presences($ids) {
        if(is_array($ids)) $ids = implode(",", $ids);
		$data = array(
			'version' => $this->version,
			'ticket' => $this->ticket,
			'domain' => $this->domain,
			'ids' => $ids
		);
		$this->client->get($this->apiurl('presences'), $data);
        //don't care if it is successful...
		$cont = $this->client->getContent();
		if($this->client->status == "200"){
			return json_decode($cont);
		}else{
			return null;
		}
    }

	/**
	 * Send user presence
	 *
	 * @param string $show
	 * @param string $status
	 *
	 * @return ok
	 *
	 */
	function presence($show, $status = ""){
		$data = array(
			'version' => $this->version,
			'ticket' => $this->ticket,
			'domain' => $this->domain,
			'nick' => $this->user->nick,
			'show' => $show,
			'status' => $status,
		);
		$this->client->post($this->apiurl('presences/show'), $data);
		return $this->client->getContent();
	}

	/**
	 * Send user chat status to other.
	 *
	 * @param string $to status receiver
	 * @param string $show status
	 *
	 * @return ok
	 *
	 */
	function status($to, $show){
		$data = array(
			'version' => $this->version,
			'ticket' => $this->ticket,
			'domain' => $this->domain,
			'nick' => $this->user->nick,
			'to' => $to,
			'show' => $show,
		);
		$this->client->post($this->apiurl('statuses'), $data);
		return $this->client->getContent();
	}

	/**
	 * Send message to other.
	 *
	 * @param string $type chat or grpchat or boardcast
	 * @param string $to message receiver
	 * @param string $body message
	 * @param string $style css
	 *
	 * @return ok
	 *
	 */
	function message($from, $to, $body, $type = 'chat', $style='', $timestamp = null){
        if(!$timestamp) $timestamp = $this->microtimeFloat() * 1000;
		$data = array(
			'version' => $this->version,
			'ticket' => $this->ticket,
			'domain' => $this->domain,
			'nick' => $this->user->nick,
			'type' => $type,
			'to' => $to,
			'body' => $body,
			'style' => $style,
			'timestamp' => $timestamp
		);
        if($from) $data['from'] = $from;
		$this->client->post($this->apiurl('messages'), $data);
		return $this->client->getContent();
	}

	/**
	 * Get room members.
	 *
	 * @param string $room
	 *
	 * @return array $members
	 * 	array($member_info)
	 *
	 */
	function members($room){
		$data = array(
			'version' => $this->version,
			'ticket' => $this->ticket,
			'domain' => $this->domain,
			'room' => $room,
		);
		$this->client->get($this->apiurl("rooms/{$room}/members"), $data);
		$cont = $this->client->getContent();
		if($this->client->status == "200"){
			return json_decode($cont);
		}else{
			return null;
		}
	}

	/**
	 * Join room.
	 *
	 * @param string $room
	 *
	 * @return ok
	 */
	function join($room){
		$data = array(
			'version' => $this->version,
			'ticket' => $this->ticket,
			'domain' => $this->domain,
			'nick' => $this->user->nick,
			'room' => $room
		);
		$this->client->post($this->apiurl("rooms/{$room}/join"), $data);
		$cont = $this->client->getContent();
		if($this->client->status == "200"){
			return json_decode($cont);
		}else{
			return null;
		}
	}

	/**
	 * Leave room.
	 *
	 * @param string $room
	 *
	 * @return ok
	 *
	 */
	function leave($room){
		$data = array(
			'version' => $this->version,
			'ticket' => $this->ticket,
			'domain' => $this->domain,
			'nick' => $this->user->nick,
			'room' => $room,
		);
		$this->client->post($this->apiurl("rooms/{$room}/leave"), $data);
		return $this->client->getContent();
	}

	/**
	 * Open chat
	 *
	 * @param string $room
	 *
	 * @return 
	 *
	 */
	function openchat($room, $nick){
		$data = array(
			'version' => $this->version,
			'ticket' => $this->ticket,
			'domain' => $this->domain,
			'room' => $room,
			'nick' => $nick,
			'timestamp' => (string)$this->microtimeFloat()*1000,
		);
		$this->client->post($this->apiurl('chats/open'), $data);
		$da = json_decode( $this->client->getContent() );
		if($this->client->status != "200" || empty($da->status)){
			return array();
		} else {
			return $da->buddies;
		}
	}

	/**
	 * Open chat
	 *
	 * @param string $room_id
	 *
	 * @return 
	 *
	 */
	function closechat($room_id, $buddy_id){
		$data = array(
			'version' => $this->version,
			'ticket' => $this->ticket,
			'domain' => $this->domain,
			'room' => $room_id,
			'buddyid' => $buddy_id,
		);
		$this->client->post($this->apiurl('chats/close'), $data);
		return json_decode( $this->client->getContent() );
	}

	/**
	 * Check the server is connectable or not.
	 *
	 * @return object
	 * 	-success: true
	 * 	-error_msg:
	 *
	 */

	function check_connect(){
		$data = array(
			'version' => $this->version,
			'rooms'=> "", 
			'buddies'=> "", 
			'domain' => $this->domain, 
			'name'=> $this->user->id, 
			'nick'=> $this->user->nick, 
			'show' => $this->user->show
		);
		$this->client->post($this->apiurl('presences/online'), $data);
		$cont = $this->client->getContent();
		$da = json_decode($cont);
		if($this->client->status != "200" || empty($da->ticket)){
			return (object)array("success" => false, "error_msg" => $cont);
		}else{
			$this->ticket = $da->ticket;
			return (object)array("success" => true, "ticket" => $da->ticket);
		}
	}

    function apiurl($path) {
        return '/' . $this->apivsn . '/' . $path;
    }

    function microtimeFloat() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

}

?>
