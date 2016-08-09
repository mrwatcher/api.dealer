<?php
/**
 * @name RequestPlugin
 * @desc 请求加工
 * @author chens
 */
class RequestPlugin extends Yaf_Plugin_Abstract {

	public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {

	}

	public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {

	}

	public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
		$caller = $request->getParam('caller');
		if ($caller){
			$request->setParam('caller',strtoupper($caller));
		}else{
			$request->setParam('caller','ALL');
		}
		$caller_list = Caller::get();
		if (!isset($caller_list[$request->getParam('caller')])) {
			echo json_encode(array('status'=>503,'msg'=>'This caller not found,please call dealer-dev!'));die;
		}

		//路由结束后反射当前类,重构request方法中的Params变量
		$ref = new ReflectionClass($request->getControllerName().'Controller');
		$params_arr = $request->getParams();
		foreach ($ref->getMethod($request->getActionName().'Action')->getParameters() as $param) {
			if (strtolower($param->name) == 'key') {
				$has_key = true;
			}
			//如果请求中没有这个变量,就插入方法中的变量并附上默认值
			if (!isset($params_arr[$param->name])) {
				if ($param->isOptional()) {
					$request->setParam($param->name,$param->getDefaultValue());
				}else{
					$request->setParam($param->name,'');
				}
			}
		}

		if ($ref->hasProperty('config')) {
			$config = $ref->getStaticPropertyValue('config');
			if(!isset($config[$request->getActionName()][$request->getParam('caller')]) && $request->getActionName() != 'yar'){
				echo json_encode(array('status'=>503,'msg'=>'This caller not found in this action,please call dealer-dev!'));die;
			}else{
				$_caller = $config[$request->getActionName()][$request->getParam('caller')];
				if (!$_caller['user_key'] && $has_key) {
					if($caller_list[$request->getParam('caller')] != $request->getParam('key')){
						echo json_encode(array('status'=>500,'msg'=>'this caller key error!'));die;
					}
				}elseif ($_caller['user_key'] && $has_key) {
					if ($_caller['user_key'] != $request->getParam('key')) {
						echo json_encode(array('status'=>500,'msg'=>'this action key error!'));die;
					}
				}
			}
		}
		Caller::$pre = 'DEALER.API_'.$request->getControllerName().'_'.$request->getActionName().'_'.$request->getParam('caller').'_';
		Caller::$now = $request->getParam('caller');
	}

	public function preDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {

	}

	public function postDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {

	}

	public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {

	}
}