<?php
/**
 * @name SamplePlugin
 * @desc Yaf定义了如下的6个Hook,插件之间的执行顺序是先进先Call
 * @see http://www.php.net/manual/en/class.yaf-plugin-abstract.php
 * @author root
 */
class YarPlugin extends Yaf_Plugin_Abstract {

	public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {

	}

	public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {

	}

	public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {

	}

	public function preDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {

	}

	public function postDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {

	}

	public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
		//当前请求处理完成以后判断
		if (strtolower($request->action) == 'yar') {
			$this->autoYar($request,$response);
		}
	}

	/**
	 * 利用类反射,执行时动态生成yar所需类
	 */

	private function autoYar($request,$response)
	{
		$rparam = var_export($request->getParams(),true);
		//反射当前请求类
		$ref = new ReflectionClass($request->getControllerName().'Controller');
		$class_methods = $ref->getMethods();
		$class_name = $ref->getName();
		$yar_class_name = explode('Controller',$class_name);
		foreach ($class_methods as $method) {
			$yar_method_name = explode('Action',$method->name);
			if($method->class == $class_name && isset($yar_method_name[1]) && $yar_method_name[1]=='' && $yar_method_name[0] != 'yar'){
				$yar_com = $ref->getMethod($method->name)->getDocComment();
				$params = array();
				$_params = array();
				foreach ($ref->getMethod($method->name)->getParameters() as $param) {
					if ($param->isOptional()) {
        				$default = $param->getDefaultValue();
        				$params[] = "\${$param->getName()} = ".var_export($default,true);
    				}else{
    					$params[] = "\${$param->getName()}";
    				}
    				$_params[$param->getName()] = "\${$param->getName()}";
				}
				$params_str = implode(',',$params);
				$_params = array_merge($_params,$request->getParams());
				$_params_str = '';
				foreach ($_params as $key => $value) {
					$_params_str .= "'".$key."'".'=>'.$value.',';
				}
				$methods_func =
				"public function {$yar_method_name[0]}({$params_str})
				{
					Yaf_Application::app()->getDispatcher()->dispatch(new Yaf_Request_Simple('POST',{$request->getModuleName()},{$yar_class_name[0]},{$yar_method_name[0]},array($_params_str)));
					return Yaf_Registry::get('return');
				}";
				$com .= $yar_com."\r\n\r\n".$methods_func."\r\n\r\n";
			}
		}
		if ($com) {
			$class = "{$yar_class_name[0]}";
			$code =
			"class $class
			{
				{$com}
			}";
			eval($code);
			//echo "<pre>";print_r($code);die;
			 //实例传入类
			$ser = new Yar_Server(new $class());
			$ser->handle();
		}
	}
}