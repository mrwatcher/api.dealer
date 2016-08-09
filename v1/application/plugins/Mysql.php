<?php
/**
 * @name SamplePlugin
 * @desc Yaf定义了如下的6个Hook,插件之间的执行顺序是先进先Call
 * @see http://www.php.net/manual/en/class.yaf-plugin-abstract.php
 * @author root
 */
class MysqlPlugin extends Yaf_Plugin_Abstract {

	public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
	}

	public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
	}

	public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
	}

	public function preDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
		$config = Yaf_Registry::get('config');
		$gcar_database_m = new medoo(array(
		    'database_type' => $config->mysql->gcar_m->type,
		    'database_name' => $config->mysql->gcar_m->name,
		    'server' => $config->mysql->gcar_m->ip,
		    'username' => $config->mysql->gcar_m->username,
		    'password' => $config->mysql->gcar_m->password,
		    'charset' => $config->mysql->gcar_m->charset,
    		'port' => $config->mysql->gcar_m->port,
    		'prefix' => $config->mysql->gcar_m->prefix,
		));
		$gcar_database_s = new medoo(array(
		    'database_type' => $config->mysql->gcar_s->type,
		    'database_name' => $config->mysql->gcar_s->name,
		    'server' => $config->mysql->gcar_s->ip,
		    'username' => $config->mysql->gcar_s->username,
		    'password' => $config->mysql->gcar_s->password,
		    'charset' => $config->mysql->gcar_s->charset,
    		'port' => $config->mysql->gcar_s->port,
    		'prefix' => $config->mysql->gcar_s->prefix,
		));
		Yaf_Registry::set('gcarmdb',$gcar_database_m);
		Yaf_Registry::set('gcarsdb',$gcar_database_s);
	}

	public function postDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {

	}

	public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
	}
}
