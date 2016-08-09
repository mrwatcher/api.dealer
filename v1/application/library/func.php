<?
/**
 * @name 经销商公用函数
 * @author chens
 */
class FUNC
{
	/**
	 * 简单的es—search方法,基于curl
	 */
	static function ESEARCH($index,$data,$version = '')
	{
		$es_client_path = Yaf_Registry::get('config')->lib->directory.'dealer_search/es_client.php';
		$es_host = Yaf_Registry::get('config')->es->host;
		$result = array();
		if (is_file($es_client_path)) {
			Yaf_loader::import($es_client_path);
			es::get(array(
				'url' =>$es_host,
				'index'=>$index.$version,
				'type'=>$index,
				'data' => $data,
			));
	        $res = es::res();
	        list($head,$body) = explode("\r\n\r\n",$res[0],2);
	        $rs = explode("\r\n",$head);
			$http_status = explode(" ",$rs[0]);
			$es_list = json_decode($body,true);
			if ($http_status[1] == 200) {
	        	$result['hits'] = $es_list['hits']['hits'];
	        	$result['hits_total'] = $es_list['hits']['total'];
	        	$result['aggs'] = $es_list['aggregations'];
	        	$result['facets'] = $es_list['facets'];
			}else{
				Yaf_Registry::set('ESEARCH_ERR',$result);
			}
		}
		return $result;
	}

	static function EREST($uri,$method = 'GET',$data = array())
	{
		$es_client_path = Yaf_Registry::get('config')->lib->directory.'dealer_search/es_client.php';
		$es_host = Yaf_Registry::get('config')->es->host;
		$result = array();
		if (is_file($es_client_path)) {
			Yaf_loader::import($es_client_path);
			$res = restful::curl($method,$es_host.$uri,json_encode($data));
	        list($head,$body) = explode("\r\n\r\n",$res[0],2);
	        $rs = explode("\r\n",$head);
			$http_status = explode(" ",$rs[0]);
			$es_list = json_decode($body,true);
			if ($http_status[1] == 200) {
				$result = $es_list;
			}else{
				Yaf_Registry::set('EREST_ERR',$result);
			}
		}
		return $result;
	}

	/**
	 * 简单的gbk or utf8 转换成你想要的编码
	 */
	public static function WANTCODE($data,$want = 'GBK')
	{
        if(is_array($data))
        {
            foreach($data as $key=>$value)
            {
            	$self = __FUNCTION__;
                $data[$key] =  self::$self($value,$want);
            }
            return $data;
        }else{
        	$lar_arr = array('ASCII','UTF8','GB2312','GBK');
        	$somelar = mb_detect_encoding($data,$lar_arr);
            if($want == $somelar){
             	return $data;
             }else{
             	return iconv($somelar,$want,$data);
             }
        }
	}

	/**
	 * 简单的memcache方法封装,非单例
	 */
	public static function MMC($k,$v=null,$t=600)
	{
		$res = false;
		if ($k) {
			$memcache = new Memcache;
			$mc_conf = Yaf_Registry::get('config')->mmc->toArray();
			foreach ($mc_conf as $mc) {
				$memcache->addServer($mc['host'],$mc['port']);
			}
			if($v !== null && $t > 0){
				$res = $memcache->set($k,$v,MEMCACHE_COMPRESSED,$t);
			}elseif($t == 0){
				$res = $memcache->delete($k,$t);
			}else{
				$res = $memcache->get($k);
			}
			$memcache->close();
		}
		return $res;
	}

	/**
	 * 简单的实例化一个service
	 */

	public static function S($service_name,$params = array())
	{
		$ser = (object) array();
		$service_path = Yaf_Registry::get('config')->service->directory.ucfirst(strtolower(trim($service_name))).'.php';
		$service_name = $service_name.'Service';
		if(is_file($service_path)){
			Yaf_loader::import($service_path);
			if (!empty($params)) {
				$ser = new $service_name($params);
			}else{
				$ser = new $service_name();
			}
		}
		return $ser;
	}

	/**
	 * 简单的实例化一个dao
	 */
	public static function DAO($dao_name,$params = array())
	{
		$dao = (object) array();
		$dao_name = ucfirst(strtolower(trim($dao_name))).'Model';
		if (!empty($params)) {
			$dao = new $dao_name($params);
		}else{
			$dao = new $dao_name();
		}
		return $dao;
	}
	/**
	 * 简单的实例化一个dao
	 */
	public static function M($dao_name,$params = array())
	{
		return self::DAO($dao_name,$params = array());
	}
	/**
	 * 简单的复杂运算
	 */
	public static function UNTIAL()
	{

	}

	/**
	 * 调用接口发送信息
	 */
	public static function SMS($data)
	{

	}
}
