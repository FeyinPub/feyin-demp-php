<?php
header("Content-type: text/html; charset=utf-8");
class feyin
{
	
	
	/**
	 * 飞印接口调试
	 */
	public function feyin($APPID,$APIKEY,$MEMBERCODE,$device_no){
		$nowTime=time();//当前时间
		$result_code=0;
		$result_msg="网络超时，请刷新";
		$result_data="";
		
		/* if(empty($_POST['appid'])){
			$result_msg='请填写飞印应用APPID';
			goto end;
		}
		$APPID=trim($_POST['appid']); */
		
		//查询数据token是否过期
		/* $tokenData=Db::table('token_log')->where('appid',$APPID)->find();
		$result_data=$tokenData;
		if(empty($tokenData)){
			$result_msg='查无应用，请联系管理员配置应用信息';
			goto end;
		}
		if (empty($tokenData['device_no'])) {
			$result_msg='请联系管理员绑定设备信息';
			goto end;
		}
		if(!empty($data) && ($data['utime']+7200)>=$nowTime){
			//两小时有效access_token
			$token=$data['access_token'];
		}else{ */
			//请求获得动态access_token
			$MEMBERCODE=$tokenData['MEMBERCODE'];//'a2a25aae18ff11e8b361525400ee10bb';(应用唯一标)
			$APIKEY=$tokenData['appkey'];//'360bda2e';（apikey）
			$APPID=$tokenData['appid'];//'123736';（appid）
			$url="https://api.open.feyin.net/token?code={$MEMBERCODE}&secret={$APIKEY}&appid={$APPID}";
			//dump($url);
			$re=httpPostGetRequest($url);
			if(!empty($re['access_token'])){
				// $tokenData=Db::table('token_log')->where('appid',$APPID)->find();
				// $data=array(
					// 'utime'=>$nowTime,
					// 'access_token'=>$re['access_token'],
					// 'expires_in'=>$re['expires_in']
					// );
				// Db::table('token_log')->where('appid',$APPID)->update($data);
				
				$token=$re['access_token'];
			}else{
				return $re;
			}
			
//		}
		

		$sendUrl="https://api.open.feyin.net/msg?access_token={$token}";
// 		dump($sendUrl);
		$msg_no="ORDER-".date('YmdHis');
		$sendData=array(
			"device_no"=>$device_no,//$tokenData["device_no"],//"4600416530041837",(打印机设备号)
			"msg_no"=>$msg_no,
			"msg_content"=> "<BinaryOrder 1B 61 01><Font# Bold=0 Width=2 Height=2>#2 美团外卖</Font#>\n 谷屋百味（嘉禾店）\n <BinaryOrder 1B 61 00>下单时间：2018-11-26 15:39:13\n 备注：<Font# Bold=0 Width=2 Height=2>收餐人隐私号 132********_6459，手机号 166****5582</Font#> \n******************************** \n<Font# Bold=0 Width=1 Height=2>蒜香排骨饭 X1 15.58 </Font#>\n------------- 其他 -------------\n 餐盒费 1.0 配送费 2.0\n ********************************\n <BinaryOrder 1B 61 02>原价：18.58元 <Font# Bold=0 Width=2 Height=2>(在线支付)18.58元</Font#> <BinaryOrder 1B 61 00>\n--------------------------------\n <Font# Bold=0 Width=2 Height=2>香江公寓(望岗东胜街) (嘉禾望岗东胜街28号) 132********_6459 陈(女士)</Font#>\n"
		);
		$reSend=httpPostGetRequest($sendUrl,json_encode($sendData),"POST",'arr','json');
		if($reSend['msg_no'] == $msg_no && empty($reSend['errmsg'])){
			$result_code=1;
			$result_msg='发送请求成功';
			$result_data=$reSend;
		}else{
			$result_msg=$reSend['errmsg'];
			$result_data=$reSend;
		}

		end:;
		$this->ajaxReturn($result_code,$result_msg,$result_data);
		
	}
	
	function ajaxReturn($result_code,$msg='',$data=''){
		echo json_encode(array('result_code'=>$result_code,'result_msg'=>$msg,'result_data'=>$data),JSON_UNESCAPED_UNICODE);exit;
	}
	
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
	
}
