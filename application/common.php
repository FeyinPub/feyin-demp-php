<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
	/**
     * Http Get/Post 函数,
     * 请求为 json 格式
     * @param string $url 请求连接
     * @param array $data 请求数据
     * @param string $method 请求类型 默认为get
     * @param string $dataType 返回的数据类型，arr处理为数组，obj处理为对象，original 不处理，默认处理为数组
     * @return 成功返回对应数据，否则返回false
     */
    function httpPostGetRequest($url,$data=array(),$method = "GET",$dataType='arr',$sendType='urlencode'){
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_URL, $url);     //以下两行，忽略 https 证书
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    	$method = strtoupper($method);
    	if ($method == "POST") {
    		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			if($sendType=='json'){
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
			}else{
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/x-www-form-urlencoded"));
			}
     		
    		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    		curl_setopt($ch, CURLOPT_TIMEOUT, time());
    	}
    	$content = curl_exec($ch);
    	$errno = curl_errno($ch);
    	curl_close($ch);
    	if($errno!=0){
    		return false;
    	}
    
    	//处理返回数据
    	switch ($dataType){
    		case 'arr':
    			$content=json_decode($content,true);
    			break;
    		case 'obj':
    			$content=json_decode($content);
    			break;
    		case 'original':
    			break;
    	}
    	return $content;
    }