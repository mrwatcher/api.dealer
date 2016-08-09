<?php
/**
 * @name 抛出数据加工
 */
class ReturnPlugin extends Yaf_Plugin_Abstract {
	static $_form = array(
		'ios'=>'ios',
		'android'=>'android',
		'web'=>'web',
		'yar'=>'yar',
		);

	static $_charset = array(
		'UTF8',
		'UTF-8',
		'GBK',
		'GB2312',
		);

	static $_cache_time = array(
		'600'=>600,
		'30'=>30,
		'1h'=>3600,
		'1d'=>14400,
		);

	static $msg;

	static $return;

	public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
	
	}

	public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
	
	}

	public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
	
	}

	public function preDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {

	}

	public function postDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
		//分发结束后开始加工数据
		if(self::$msg = Yaf_Registry::get('msg')){
			//统一编码转换
			$this->_charset($request);
			//统一来源转换
			$from = $this->_from($request);
			//统一数据展示格式转换
			$this->_format($request);
			//执行结果
			Yaf_Registry::set('return',self::$msg);
			eval(self::$return);
		};
	}

	public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {

	}

	private function _charset($request)
	{
		if ($request->getParam('charset')) {
			if (strtoupper($request->getParam('charset')) == 'UTF8') {
				$charset = 'UTF-8';
			}else{
				$charset = $request->getParam('charset');
			}
		}else{
			$charset = 'UTF-8';
		}
		if (in_array(strtoupper($charset),self::$_charset)) {
			self::$msg = FUNC::WANTCODE(self::$msg,strtoupper($charset));
		}
	}

	private function _format($request)
	{
			$format = $request->getParam('format')?:'json';
			switch ($format) {
				case 'html':
					self::$return = "echo \"<pre>\";print_r(self::\$msg);echo \"</pre>\";";
					break;
				case 'serialize':
					self::$return = "echo serialize(self::\$msg);";
					break;
				case 'json':
					self::$return = "echo json_encode(self::\$msg);";
					break;
				case 'jsonp':
					$callback = (string) $request->getParam('callback')?:'callback';
					if ($callback) {
						self::$return = "echo '{$callback}'.'('.json_encode(self::\$msg).')';";
					}
					break;
				case 'yar':
						self::$return = "";
					break;
			}
	}

	private function _from($request)
	{
		$from = $request->getParam('from')?:'web';
		if (in_array($from,self::$_form)) {
			$do = '_'.$from;
			$this->$do($request);
		}
	}

	private function _web($request)
	{

	}

	private function _ios($request)
	{

	}

	private function _android($request)
	{

	}

	private function _setcache()
	{

	}

}