<?php
/**
 * @name 引导层
 * @author chens
 * @desc 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf_Bootstrap_Abstract{

    public function _initConfig(Yaf_Dispatcher $dispatcher) {
		//把配置保存起来
		$arrConfig = Yaf_Application::app()->getConfig();
		Yaf_Registry::set('config', $arrConfig);
		//关闭自动加载模板
		Yaf_Dispatcher::getInstance()->autoRender(FALSE);
		$dispatcher->setDefaultModule('Index')->setDefaultController('Index')->setDefaultAction('index');
		if ( $_GET['APID'] == '') {
			define('RUN_MODE','DEV');
		}

	}
	//初始化全局自动加载
    public function _initAutoload() {
    	//composer自动加载
    	require APPLICATION_PATH.'/application/library/vendor/autoload.php';
	}

	//初始化公用函数
	public function _initDealerFunc(Yaf_Dispatcher $dispatcher) {
		$lib_path = Yaf_Registry::get('config')->lib->directory;
		$func_path = $lib_path.'func.php';
		$caller_path = $lib_path.'caller.php';
		$es_event_path = $lib_path.'dealer_search/EsEvent.php';
		Yaf_loader::import($func_path);
		Yaf_loader::import($es_event_path);
		Yaf_loader::import($caller_path);
	}

	public function _initPlugin(Yaf_Dispatcher $dispatcher) {
		//注册一个插件

		//数据库连接
		$dispatcher->registerPlugin(new MysqlPlugin());		
		//组件加载
		$dispatcher->registerPlugin(new ComponentsPlugin());
		//请求加工设置
		$dispatcher->registerPlugin(new RequestPlugin());
		//动态生成rpc
		$dispatcher->registerPlugin(new YarPlugin());
		//返回数据加工
		$dispatcher->registerPlugin(new ReturnPlugin());

	}

}
