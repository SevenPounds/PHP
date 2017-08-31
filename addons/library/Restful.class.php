<?php
class Restful{

	
   
   /**
     * 发送httpful get请求
     * @param  [array] $params [description]
     * @return [object]         [description]
     */
    public static function sendGetRequest($params,$serverUrl = ''){
    	
    	$serverUrl = empty($serverUrl) ? C('YUN_SERVER_URL') : $serverUrl;
        try{
            $str_params = '';
            foreach ($params as $key => $value) {           
                $str_params .= "&$key=".urlencode($value);
            }
            $response =  \Httpful\Request::get($serverUrl.$str_params)->send();
            return  $response->body;
        }catch(Execption $e){
           return array("statuscode"=>'-1',"message"=>'服务器出错了');
        }
        
    }
    
    /**
     * 发送httpful post请求
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public static function sendPostRequest($params){
         try{
         	$serverUrl = C('YUN_SERVER_URL');
            $filepath = $params['filePath'];
            $str_params = '';
            foreach ($params as $key => $value) {           
                $str_params .= "&$key=".urlencode($value);
            }
            unset($params['filePath']);
            $response =  \Httpful\Request::post($serverUrl.$str_params)
                 ->attach(array('multiFile'=>$filepath))
                 ->send();
            return  $response->body;
        }catch(Execption $e){
           return array("statuscode"=>'-1',"message"=>'服务器出错了');
        }
    }

    public static function object_to_array($obj){  
      /*  $_arr = is_object($obj) ? get_object_vars($obj) :$obj;
        foreach ($_arr as $key=>$val){  
            $val = (is_array($val) || is_object($val)) ? $this->object_to_array($val):$val;  
            $arr[$key] = $val;  
        }  
        return $arr;  */
    }

    /**
    timestamp :时间戳
    appId:应用Id
    //nonce :随机数
    //signature :timestamp,nonce,apikey（初审通过后获得的//apiKey）算出来的消息接口验证码
    //MD5(apiKey + nonce + timestamp)
    message: json格式消息列表格式如下： 
    [
    {
    "touser":"admin",  //用户名
    "type":"5",        //消息类型（根据此类型对消息进行分类，消息类型为：3为通知，4为应用消息，5为待办，6为计划）
    "content":"Hello World" ,
    "url":"http://me.daxingedu.cn/oa/list.do" //消息url
    }
    ]
     * @param $params  touser  用户名，type
     * @return array
     */
    public static function  sendMessage($params){
        try{
            $serverUrl = C('LZX_MESSAGE_URL');
            //$filepath = $params['filePath'];
            $str_params = '';
            foreach ($params as $key => $value) {
                $str_params .= "&$key=".urlencode($value);
            }
            unset($params['filePath']);
            $response =  \Httpful\Request::post($serverUrl.$str_params)
                //->attach(array('multiFile'=>$filepath))
                ->send();

            return  $response->body;
        }catch(Execption $e){
            return array("statuscode"=>'-1',"message"=>'服务器出错了');
        }
    }
}