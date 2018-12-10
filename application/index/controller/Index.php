<?php
//http://serverName/index.php/控制器/操作/[参数名/参数值...]
namespace app\index\controller;
use think\Controller;
use think\Db;
use v3\Openapi;
header("Content-type: text/html; charset=utf-8");
class Index extends Controller
{
	
	private $config=[];
	public function _initialize()
    {
       /**
		 * 配置项
		 */
		$this->config['encrypt'] = ''; //加密方式;普通对接对解放为空
		$this->config['source'] = '62714'; //填写对应的source
		$this->config['secret'] = '6c9c6c0c39b9c61e'; //填写对应的secret
		$this->config['url'] = 'https://api-be.ele.me';
		
    }
	
    public function index()
    {
    	   return $this->fetch('index',['name'=>'thinkphp']);
//         return '<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> ThinkPHP V5<br/><span style="font-size:30px">十年磨一剑 - 为API开发设计的高性能框架</span></p><span style="font-size:22px;">[ V5.0 版本由 <a href="http://www.qiniu.com" target="qiniu">七牛云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="https://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="https://e.topthink.com/Public/static/client.js"></script><think id="ad_bd568ce7058a1091"></think>';
    }
	
	public function home(){
		
// 		$this->demo();
// 		exit;
// 		$info=['name'=>"调试",'value'=>"1111"];
		return $this->fetch('index',['name'=>'thinkphp']);
		//$this->assign('info', $info);
		//$this->display ();
	}
	
	/**
	 * 飞印接口调试
	 */
	public function feyin(){
		$nowTime=time();//当前时间
		$result_code=0;
		$result_msg="网络超时，请刷新";
		$result_data="";
		
		if(empty($_POST['appid'])){
			$result_msg='请填写飞印应用APPID';
			goto end;
		}
		$APPID=trim($_POST['appid']);
		
		//查询数据token是否过期
		$tokenData=Db::table('token_log')->where('appid',$APPID)->find();
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
		}else{
			//请求获得动态access_token
			$MEMBERCODE=$tokenData['MEMBERCODE'];//'a2a25aae18ff11e8b361525400ee10bb';
			$APIKEY=$tokenData['appkey'];//'360bda2e';
			$APPID=$tokenData['appid'];//'123736';
// 			$MEMBERCODE='a2a25aae18ff11e8b361525400ee10bb';
// 			$APIKEY='360bda2e';
// 			$APPID='123736';
			$url="https://api.open.feyin.net/token?code={$MEMBERCODE}&secret={$APIKEY}&appid={$APPID}";
			//dump($url);
			$re=httpPostGetRequest($url);
			if(!empty($re['access_token'])){
				$tokenData=Db::table('token_log')->where('appid',$APPID)->find();
				$data=array(
					'utime'=>$nowTime,
					'access_token'=>$re['access_token'],
					'expires_in'=>$re['expires_in']
					);
				Db::table('token_log')->where('appid',$APPID)->update($data);
				
				$token=$re['access_token'];
			}else{
				return $re;
			}
			
		}
		//绑定打印机
		/* $device_no='4600408903132241';
		$bindUrl="https://api.open.feyin.net/device/{$device_no}/bind?access_token={$token}";
		dump($bindUrl);
		$reBind=httpPostGetRequest($bindUrl);
		dump($reBind); */

		$sendUrl="https://api.open.feyin.net/msg?access_token={$token}";
// 		dump($sendUrl);
		$msg_no="ORDER-".date('YmdHis');
		$sendData=array(
			"device_no"=>$tokenData["device_no"],//"4600416530041837",
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
// 		dump($reSend);
// 		//
// 		dump($token);
// 		exit;
		
		end:;
		$this->ajaxReturn($result_code,$result_msg,$result_data);
		
	}
	
	
	
	//饿百零售调试
	function demo(){
		
		//$data=Db::query('select * from send_result_log');
		
		exit;
		/**
		 * 商户信息获取DEMO
		 */
		
// 		$cmd = 'shop.get';//获取商户信息
// 		$cmd = 'shop.open';//
// 		$cmd = 'order.create';//
// 		$cmd = 'order.get';//
// 		$cmd = 'common.iplist';//
// 		$cmd = 'common.expresslist';//
		$data = array();
// 		$data['baidu_shop_id']='2235674837';
		$data['order_id'] = '14703062072007';
// 		$data=array(
// 				"baidu_shop_id"=>2235674837,	
// 				"start_time"=>time()-3600,
// 				"end_time"=>time(),
// 				"status"=>"1",
// 				"page"=>"1",
// 		);
// 		$data['shop_id'] = 'test_225593_62714';
		$obj = new Openapi($this->config);
		$re=$obj->send($cmd, $data,$reData);
		dump($reData);
		dump($re);
		if(false === $re){
			$msg = '获取商户信息失败！';
			$this->re_display($msg, $obj->getLastError(), $obj->getLastErrno());
		}else{
			$msg = '获取商户信息成功！';
			$this->re_display($msg, $obj->getLastError(), $obj->getLastErrno(), $obj->getLastData());
		}
		
// 		return $api;
	}
	
	public function callback(){
		       return '<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> ThinkPHP V5<br/><span style="font-size:30px">十年磨一剑 - 为API开发设计的高性能框架</span></p><span style="font-size:22px;">[ V5.0 版本由 <a href="http://www.qiniu.com" target="qiniu">七牛云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="https://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="https://e.topthink.com/Public/static/client.js"></script><think id="ad_bd568ce7058a1091"></think>';
   
	}
	
	/**
	 * 获取订单详情 
	 */
	public function getOrderDesc(){
		$desc_info='{"errno":0,"error":"success","data":{"source":"65472","shop":{"id":"baidubqa01","name":"测试-QA-骑兵专用","baidu_shop_id":"1483446181"},"order":{"expect_time_mode":1,"pickup_time":0,"atshop_time":0,"delivery_time":0,"delivery_phone":"","finished_time":"0","confirm_time":"0","order_id":"14788454249947","eleme_order_id":"14788454249947","order_index":"1","status":1,"order_flag":0,"send_immediately":2,"send_time":"1479091500","send_fee":0,"package_fee":1500,"discount_fee":0,"total_fee":5100,"shop_fee":5100,"user_fee":5100,"pay_type":1,"pay_status":1,"need_invoice":2,"invoice_title":"","remark":"请提供餐具","delivery_party":2,"create_time":"1478845425","cancel_time":"0"},"user":{"name":"邓","phone":"13261158090","gender":1,"address":"上地 彩虹大厦","province":"北京市","city":"北京市","district":"海淀区","coord":{"longitude":116.321306,"latitude":40.041169}},"products":[[{"baidu_product_id":"1529731460","other_dish_id":"1529731460","upc":"","product_name":"紫薯粥_小盒","product_type":1,"product_price":600,"product_amount":1,"product_fee":600,"package_price":500,"package_amount":"1","package_fee":500,"total_fee":1100,"product_custom_index":"1529731460_0_0","product_attr":[{"baidu_attr_id":"1703572077","attr_id":"","name":"规格","option":"小盒"}],"product_features":[{"baidu_feature_id":"1703572074","name":"辣的","option":"微辣"},{"baidu_feature_id":"1703573994","name":"温度","option":"常温"}]},{"baidu_product_id":"1724959964","product_id":"","product_type":2,"product_name":"奥运会青春套餐","is_fixed_price":"0","product_price":2500,"product_amount":1,"product_fee":2500,"package_price":500,"package_amount":"1","package_fee":500,"total_fee":3000,"group":[{"group_name":"大叔套餐","baidu_group_id":"1724959965","product":[{"baidu_product_id":"1529731874","other_dish_id":"1529731874","upc":"","product_name":"花生粥","product_type":1,"product_price":1000,"product_amount":1,"product_fee":1000,"product_attr":[],"product_features":[{"baidu_feature_id":"1776052375","name":"温度","option":"高"}]}]},{"group_name":"学生套餐","baidu_group_id":"1724959966","product":[{"baidu_product_id":"1537991176","other_dish_id":"1537991176","upc":"","product_name":"蛋炒饭_超辣","product_type":1,"product_price":1500,"product_amount":1,"product_fee":1500,"product_attr":[{"baidu_attr_id":"1723616803","attr_id":"","name":"规格","option":"超辣"}],"product_features":[{"baidu_feature_id":"1723619110","name":"配菜","option":"韭菜"}]}]}]},{"baidu_product_id":"1591504578","other_dish_id":"1591504578","upc":"","product_name":"看(⊙o⊙)","product_type":1,"product_price":500,"product_amount":1,"product_fee":500,"package_price":500,"package_amount":"1","package_fee":500,"total_fee":1000,"product_attr":[],"product_features":[]}]],"discount":[],"part_refund_info":[{"status":"10","total_price":200,"shop_fee":0,"order_price":100,"package_fee":0,"discount_fee":0,"send_fee":100,"refund_price":1000,"refund_box_price":0,"refund_send_price":0,"refund_discount_price":0,"refuse_platform":0,"commission":0,"order_detail":[[{"baidu_product_id":"1772493433","upc":"","product_name":"13241","product_type":1,"product_price":100,"product_amount":1,"product_fee":100,"package_price":0,"package_amount":"1","package_fee":0,"total_fee":100,"product_attr":[],"product_features":[],"product_custom_index":"1772493433_0_0"}]],"refund_detail":[[{"baidu_product_id":"1926765752","other_dish_id":"1926765727","upc":"","product_name":"同步菜","product_type":1,"product_price":1000,"product_amount":1,"product_fee":1000,"package_price":0,"package_amount":"1","package_fee":0,"total_fee":1000,"product_attr":[],"product_features":[],"product_custom_index":"1926765752_0_0"}]],"discount":[]}]}}';
		$result_code=0;
		$result_msg='网络超时，请刷新后再试';
		$result_data='';
		$cmd = 'order.get';//获取订单详情
		if(empty($_POST['order_id'])){$result_msg='请选择查询订单';goto end;}		
		
		if(!empty($_POST['order_id']) && $_POST['order_id'] == '14788454249947'){
			$data['order_id'] = '14788454249947';
			$re=true;
			$reData=json_decode($desc_info,true);
		}else {
			$data['order_id'] = trim($_POST['order_id']);
			$obj =new Openapi($this->config);
			$re=$obj->send($cmd, $data, $reData);
		}
		$this->__addSendLog($cmd,$data,json_encode($reData));
		if(false === $re){
			//请求失败
			$result_msg='请求失败';
			$result_data=$reData;
			goto end;
		}else{
			//请求成功
			if($reData['errno']==0 && $reData['error']=='success'){
				$indata=array(
					'shop_id'=>'ceshi123456',
					'order_id'=>$data['order_id'],
					'source'=>$reData['data']['source'],
					'order'=>json_encode($reData['data']),
					'ctime'=>time(),
					'utime'=>time()	
				);
				$bl=db('order_log')->insert($indata);
				$result_code=1;
				$result_msg='获取订单详情成功';
				$result_data['desc']=$reData['data'];
				goto end;
			}
				
		}
		
		end:;
		$this->ajaxReturn($result_code,$result_msg,$result_data);
	}
	
	/**
	 * 获取订单列表
	 */
	public function orderList(){
		
		$result_code=0;
		$result_msg='网络超时，请刷新后再试';
		$result_data='';
		
		if(empty($_POST['stime'])){$result_msg='请选择开始时间';goto end;}
		if(empty($_POST['etime'])){$result_msg='请选择结束时间';goto end;}
		
		$pageNums=empty($_POST['pageNums'])?1:trim($_POST['pageNums']);
		$stime=trim($_POST['stime']);
		$etime=trim($_POST['etime']);
		$cmd = 'order.list';//获取订单列表
		$data=array(
			'shop_id'=>'',//合作方ID
			"start_time"=>strtotime($stime),
			"end_time"=>strtotime($etime),
			"status"=>"1",
			"page"=>$pageNums,
		);
		$obj =new Openapi($this->config);
		$re=$obj->send($cmd, $data, $reData);
		if(false === $re){
			//请求失败
			$result_msg='请求失败';
			$result_data=$reData;
			goto end;
		}else{
			//请求成功
			if($reData['errno']==0 && $reData['error']=='success'){
				$result_code=1;
				$result_msg='获取列表成功';
				$result_data['list']=$reData['data'];
				goto end;
			}
			
		}
		
		
		end:;		
		$this->ajaxReturn($result_code,$result_msg,$result_data);
	}
	
	/**
	 * 订单创建
	 */
	public function orderCreat(){
		//接受参数
		$request = $_REQUEST;
		$config = array();
		$config['source'] = $this->config['source']; //填写对应的source
		$config['secret'] = $this->config['secret']; //填写对应的secret

		//校验推送过来数据的签名
		$_temp = $request;
		$this->__addSendLog('orderCreat',$request,json_encode('0'));//添加日志
		if (empty($_temp)) {
			echo "数据有误";exit();;
		}
		$_temp['body'] = !isset($_temp['body'])?[]:json_decode($_temp['body'],true);
		$request['sign']=!isset($request['sign'])?"":$request['sign'];
		$request['cmd']=!isset($request['cmd'])?"":$request['cmd'];
		if($request['sign'] != $this->_genSign($_temp,$config)){
			$request['body'] = array('errno'=>-1,'error'=>'check sign failed!'); 
		}else{
			if('order.create' == $request['cmd']){    
				//执行打印操作
				
				
				//demo为了方便直接随机生成一个source_order_id返回
				$request['body'] = array('errno'=>0,'error'=>'success','data'=>array('source_order_id'=>rand()));
			}else{
				echo "其他接口参考order.create进行处理";exit();
			}
		}
		$request['cmd']  = 'resp.' . $request['cmd'];
		$request['sign'] = $this->_genSign($request,$config);
		ksort($request);
		$ret = json_encode($request);
		echo $ret;
		exit();
	}
	
	//签名
	function _genSign($data,$config)
	{
		$arr = array();
		$arr['body'] = !isset($data['body'])?"":json_encode($data['body']);
		$arr['cmd'] = !isset($data['cmd'])?"":$data['cmd'];
		$arr['encrypt'] = !isset($data['encrypt'])?"":$data['encrypt'];
		$arr['secret'] = !isset($data['secret'])?"":$config['secret'];
		$arr['source'] = !isset($data['source'])?"":$data['source'];
		$arr['ticket'] = !isset($data['ticket'])?"":$data['ticket'];
		$arr['timestamp'] = !isset($data['timestamp'])?"":$data['timestamp'];
		$arr['version'] = !isset($data['version'])?"":$data['version'];
		ksort($arr);
		$tmp = array();
		foreach ($arr as $key => $value) {
			$tmp[] = "$key=$value";
		}
		$strSign = implode('&', $tmp);
		$sign = strtoupper(md5($strSign));
		return $sign;
	}
     
	//日志记录
	function __addSendLog($url,$send,$result){
		$data=array(
			'ctime'=>time(),
			'url'=>$url,
			'sendData'=>json_encode($send),
			'resultData'=>$result,
		);
		Db::name('send_result_log')->insert($data);
		$userId = Db::name('send_result_log')->getLastInsID();
		return $userId;
	}


	function re_display($msg, $error, $errno = 0, $data = null){
		echo sprintf('%s[%s] %s, errno[%s] error[%s], data[%s]' . PHP_EOL, str_repeat('=', 80) . PHP_EOL, date('Y-m-d H:i:s'), $msg, $errno, $error, var_export($data, true));
	}
	
	/**
	 * 编辑返回的json数据格式
	 * @param unknown_type $result_code
	 * @param unknown_type $msg
	 * @param unknown_type $data
	 * @author: gaosheren<861216024@qq.com> 2014-10-10 16:40:00
	 */
	function ajaxReturn($result_code,$msg='',$data=''){
		echo json_encode(array('result_code'=>$result_code,'result_msg'=>$msg,'result_data'=>$data),JSON_UNESCAPED_UNICODE);exit;
	}
}
