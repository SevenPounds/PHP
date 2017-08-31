<?php

/**
 * PHP SDK For iFLYTEK
 *
 * @version         $Id$
 * @author          IFLYTEK Education Division Architecture Team
 * @copyright       © 2013, iFlyTEK CO.Ltd. All rights reserved.
 * @history
 *                  shenghe | 2013-04-15 11:05:44 | created
 */

require_once (dirname(dirname(__FILE__)) .'/base.php');

/**
 * 资源操作接口
 * @package         Helper\API
 */
class Resource extends Base {
    /**
     * 构造函数
     * @param string $accesstoken 校验用accesstoken
     * @param array $header       http协议头
     */
    public function __construct($appkey, $accesstoken, $header) {
        parent::__construct($appkey, $accesstoken, $header);
    }

    /**
     * 查询资源
     *
     * 全文检索：
     * 当在condition中传递 q=[待检索内容] 参数时，默认使用全文检索。
     * 其中 q 的内容组织方式和一般搜索引擎一致。
     *
     * @param  array        $condition             查询条件，以键值对方式赋值
     * @param  array        $fields                指定返回的字段名称
     * @return array|string                        错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function get_resource($condition, $fields = array()) {
        if (!is_array($condition) || !is_array($fields)) {
            return array(
                'total' => 1,
                'statuscode' => 400,
                'data' => 'invalid parameters'
            );
        }

        $condition['fields'] = implode(',', $fields);

        return $this->send('get', '/resourceservice/resource/', $condition);
    }

    /**
     * 创建资源
     *
     * @param  string          $file               要上传的文件路径
     * @param  string          $file_index         文件描述信息，resource_index类的实例后生成的json字符串
     * @return array|string                        错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function create_resource($file, $file_index) {
        if (!is_string($file) || !is_string($file_index)) {
            return array(
                'total' => 1,
                'statuscode' => 400,
                'data' => 'invalid parameters'
            );
        }

        $params = array(
            'file_index' => $file_index,
            'file' => '@' . $file
        );

        return $this->send('put', '/resourceservice/resource/', $params);
    }


    /**
     * 更新资源(支持批量更新)
     *
     * @param  string|array            $resourceid                 要更新的资源ID|资源ID数组
     * @param  array                   $document                   修改的条件,字典数组.
     * @return array|string                                错误码数组信息|Json字符串(由用户进行反序列化)
     * @example
     *   若原有数据的id=0123456789, title=file1.txt, 欲将title改为file2.txt，则传递如下参数
     *   resourceid = '0123456789', document = array('title' => 'file2.txt')
     */
    public function update_resource($resourceid, $document) {

        if (is_array($resourceid)) {
            $resourceid = implode(',', $resourceid);
        }
        $params = array(
            'document' => json_encode(array(
                '$set' => $document
            )),
            'id' => $resourceid
        );

        return $this->send('post', '/resourceservice/resource/', $params);
    }

    /**
     * 删除资源
     *
     * @param      string              $resourceid             资源id, 指的是资源索引的general.id
     * @param      string               $author                 author，记录谁进行的操作
     * @return     array|string                        错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function del_resource($resourceid, $author = '') {
        if (!is_string($resourceid) || empty($resourceid)) {
            return array(
                'total' => 1,
                'statuscode' => 400,
                'data' => 'invalid parameters'
            );
        }

        $params = array(
            'id' => $resourceid
        );
        if (!empty($author)) {
            $params['author'] = $author;
        }

        return $this->send('del', '/resourceservice/resource/', $params);
    }

    /**
     * 下载资源
     * @param  string $resourceid 资源ID
     * @param  string $type       资源类型，支持： index, file, thumbnail, preview
     * @param  array  $condition  下载的条件，例如要指定下载的缩略图大小：array("size"=>"120_90"), array("size"=>"120_160")
     *                            array("size"=>164_123")
     * @param  bool   $show        是否直接显示图片地址, img标签中可以直接引用返回来的地址
     * @return     array|string                        错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function download_resource($resourceid, $type, $condition = array(), $show = false) {
        if (!is_string($resourceid) || empty($resourceid)) {
            return array(
                'total' => 1,
                'statuscode' => 400,
                'data' => 'invalid parameters'
            );
        }
        $condition['get_type'] = $type;
        if ($show) {
            $condition['data'] = true;
            return "http://". $this->host .'/resourceservice/resource/metadata/'.$resourceid .'/?'. http_build_query($condition);
        }
        return $this->send('get', '/resourceservice/resource/metadata/' . $resourceid . '/', $condition);
    }

    /**
     * 获取资源详细信息
     * @param  string $resourceid 资源ID
     * @return     array|string                        错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function get_resource_index($resourceid) {
        if (!is_string($resourceid) || empty($resourceid)) {
            return array(
                'total' => 1,
                'statuscode' => 400,
                'data' => 'invalid parameters'
            );
        }

        return $this->send('get', '/resourceservice/resource/metadata/' . $resourceid . '/', array(
            'get_type' => "index"
        ));
    }

    /**
     * 可以为某一资源设定一个属性
     * @param  string $resourceid 资源ID
     * @param  string $enabbr     新属性的名称
     * @param  string|array $code       多个新属性的Code
     * @return     array|string                        错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function create_resource_properties($resourceid, $enabbr, $code = array()) {
        if (!is_string($resourceid) || !is_string($enabbr)) {
            return array(
                'total' => 1,
                'statuscode' => 400,
                'data' => 'invalid parameters'
            );
        }

        if (is_string($code)) {
            $code = array(
                $code
            );
        }

        $params = array(
            'enabbr' => $enabbr,
            'values' => implode(',', $code)
        );

        return $this->send('put', '/resourceservice/resource/properties/' . $resourceid . '/', $params);
    }

    /**
     * [get_resource_properties description]
     * @param  string $resourceid 资源ID
     * @param  array  $fields     设定返回的属性信息
     * @return     array|string                        错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function get_resource_properties($resourceid, $fields = array()) {
        if (!is_string($resourceid) || !is_array($fields)) {
            return array(
                'total' => 1,
                'statuscode' => 400,
                'data' => 'invalid parameters'
            );
        }

        $params = array();
        if (!empty($fields)) {
            $params = array(
                'fields' => implode(',', $fields)
            );
        }

        return $this->send('get', '/resourceservice/resource/properties/' . $resourceid . '/', $params);
    }

    /**
     * 删除某一资源的一个中的属性
     *
     * @param  string $resourceid   资源ID
     * @param  string $enabbr       属性的名称
     * @param  string $value        属性值
     * @return     array|string                        错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function del_resource_properties($resourceid, $enabbr, $value) {
        if (!is_string($resourceid) || !is_string($enabbr)) {
            return array(
                'total' => 1,
                'statuscode' => 400,
                'data' => 'invalid parameters'
            );
        }

        $params = array(
            'enabbr' => $enabbr,
            'values' => $value
        );

        return $this->send('del', '/resourceservice/resource/properties/' . $resourceid . '/', $params);
    }

    /**
     * 获取资源的统计信息
     *
     * @param  string        $resourceid                   资源ID
     * @param  string        $staname                     统计信息的名称，留空则表示全部获取, 可以为:
     *                                                     score, favorate,up, down, recommended , recommendcounts,
     *                                                     editorate , downloadcount , viewcount , commentcount
     * @return     array|string                            错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function get_resource_statistics($resourceid, $staname = '') {
        if (!is_string($resourceid) || !is_string($staname)) {
            return array(
                'total' => 1,
                'statuscode' => 400,
                'data' => 'invalid parameters'
            );
        }

        $params = array(
            'resourceid' => $resourceid,
            'staname' => $staname
        );

        return $this->send('get', '/resourceservice/resource/statistics/', $params);
    }

    /**
     * 编辑推荐/评分
     *
     * @param  string        $resourceid                   资源ID
     * @param  string        $action                    操作类型，例如：score，recommended
     * @param  array         $params                    对应类型要传的参数，例如，action=score时，$params = array('scores'=> 100)
     * @return     array|string                            错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function create_resource_editorate($resourceid, $action, $params = array()) {
        if (!is_string($resourceid) || empty($resourceid) || !is_array($params) || !is_string($action)) {
            return array(
                'total' => 1,
                'statuscode' => 400,
                'data' => 'invalid parameters'
            );
        }

        $params['action']     = $action;
        $params['resourceid'] = $resourceid;

        return $this->send('get', '/resourceservice/resource/statistics/editorate/', $params);
    }

    /**
     * 用户评分|收藏|赞|踩|推荐
     *
     * @param  string        $resourceid                   资源ID
     * @param  string        $action                    操作类型，例如：score，favorate, up, down, recommend
     * @param  array         $params                    对应类型要传的参数，例如，action=score时，$params = array('scores'=> 100)
     * @return     array|string                            错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function create_resource_userate($resourceid, $action, $params = array()) {
        if (!is_string($resourceid) || empty($resourceid) || !is_array($params) || !is_string($action)) {
            return array(
                'total' => 1,
                'statuscode' => 400,
                'data' => 'invalid parameters'
            );
        }

        $params['action']     = $action;
        $params['resourceid'] = $resourceid;

        return $this->send('put', '/resourceservice/resource/statistics/userate/', $params);
    }

    /**
     * 更新审核状态
     * @param  string|array $resourceid 资源ID(或数组)
     * @param  string $status     资源审核状态
     * @return     array|string                            错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function get_resource_pendingaudit($resourceid, $status) {
        if (!is_string($resourceid) || !is_string($status)) {
            return array(
                'total' => 1,
                'statuscode' => 400,
                'data' => 'invalid parameters'
            );
        }

        if (is_array($resourceid)) {
            $resourceid = implode(',', $resourceid);
        }
        $params = array(
            'resourceid' => $resourceid,
            'status' => $status
        );

        return $this->send('get', '/resourceservice/resource/pendingaudit/', $params);
    }

    /**
     * 获取制定标签信息
     * @param  string  $tag_name 标签名称
     * @param  integer $limit    限制返回的数目,默认返回30条
     * @return array|string                            错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function get_tags($tag_name, $limit = 30) {
        if (!is_string($tag_name) || empty($tag_name) || !is_int($limit)) {
            return array(
                'total' => 1,
                'statuscode' => 400,
                'data' => 'invalid parameters'
            );
        }

        $params = array(
            "tag_name" => $tag_name,
            "limit" => $limit
        );
        return $this->send('get', '/resourceservice/resource/tags/', $params);
    }

    /**
     * 获取指定资源的Tag
     * @param  string $resourceid 资源ID
     * @param  string $tag_name   Tag名称
     * @return array|string                            错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function get_tag($resourceid, $tag_name) {
        if (!is_string($resourceid) || empty($resourceid) || !is_string($tag_name) || empty($tag_name)) {
            return array(
                'total' => 1,
                'statuscode' => 400,
                'data' => 'invalid parameters'
            );
        }

        $params = array(
            "tag_name" => $tag_name
        );

        return $this->send('get', '/resourceservice/resource/tags/' . $resourceid . '/', $params);
    }

    /**
     * 设置指定资源的Tag
     * @param  string $resourceid 资源ID
     * @param  array  $tag_values 使用tag_values传递新的tag值，tag_values可传多个值（1-n个）
     * @return array|string                            错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function create_tag($resourceid, $tag_values = array()) {
        if (!is_string($resourceid) || empty($resourceid) || !is_array($tag_values)) {
            return array(
                'total' => 1,
                'statuscode' => 400,
                'data' => 'invalid parameters'
            );
        }

        $params = array(
            "tag_values" => implode(",", $tag_values)
        );

        return $this->send('put', '/resourceservice/resource/tags/' . $resourceid . '/', $params);
    }

    /**
     * 删除指定资源的Tag
     * @param  string $resourceid 资源ID
     * @param  string $tag_name   待删除的Tag名称
     * @param  array  $tag_values 使用tag_values传递欲删除的tag值，tag_values可传多个值（0-n个）
     *                            tag_values未传则删除指定tag_name全部值
     * @return array|string                            错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function delete_tag($resourceid, $tag_name, $tag_values = array()) {
        if (!is_string($resourceid) || empty($resourceid) || !is_string($tag_name) || empty($tag_name) || !is_array($tag_values)) {
            return array(
                'total' => 1,
                'statuscode' => 400,
                'data' => 'invalid parameters'
            );
        }

        $params = array(
            "tag_name" => $tag_name
        );

        if (!empty($tag_values)) {
            $params["tag_values"] = implode(",", $tag_values);
        }

        return $this->send('del', '/resourceservice/resource/tags/' . $resourceid . '/', $params);
    }

    /**
     * 获取推荐资源
     * @param  string  $resourceid 资源ID
     * @param  integer $limit      使用limit控制返回数量，默认返回11条
     * @return array|string                            错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function get_recommend_resources($resourceid, $limit = 11) {
        if (!is_string($resourceid) || empty($resourceid)) {
            return array(
                'total' => 1,
                'statuscode' => 400,
                'data' => 'invalid parameters'
            );
        }

        $params = array(
            "id" => $resourceid,
            "limit" => $limit
            );
        return $this->send('get', '/resourceservice/resource/recommend/'. $params);
    }

    /**
     * 获取热门资源
     * @param  string  $fields      设置返回信息的字段，同资源查询接口
     * @param  integer $limit       使用limit控制返回数量，默认返回10条
     * @return array|string                            错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function get_hot_resource($fields = array(), $condition = array()) {
        if (!is_array($condition) || !is_array($fields)) {
            return array(
                'total' => 1,
                'statuscode' => 400,
                'data' => 'invalid parameters'
            );
        }

        $params = $condition;

        if (!isset($params['limit'])) {
            $params['limit'] = 10;
        }

        if (!empty($fields)) {
            $params['fields'] = implode(',', $fields);
        }

        return $this->send('get', '/resourceservice/resource/hot/', $params);
    }

    /**
     * 获取对应资源的bookindex信息， 单独列出来是从效率考虑
     * @param  string $resourceid 资源Id
     * @return array|string                            错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function get_resource_bookindex($resourceid) {
        if (!is_string($resourceid)) {
            return array(
                'total' => 1,
                'statuscode' => 400,
                'data' => 'invalid parameters'
            );
        }

        return $this->send('get', '/resourceservice/resource/bookindex/'. $resourceid .'/');
    }
    
    /**
     * 获取符合条件资源的数目
     *
     * @param  array  $condition 条件键值对
     * @return array|string                            错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function get_resource_counts($condition = array()) {
    	if (!is_array($condition)) {
    		return array(
    				'total' => 1,
    				'statuscode' => 400,
    				'data' => 'invalid parameters'
    		);
    	}
    	 return $this->send('get', '/resourceservice/resource/count/', $condition);
    }
    
}

?>