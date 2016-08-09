<?php
/**
 * @name Dealer_Super
 * @desc Dealer的超类
 * @author chens
 */
class Dealer_Super extends Yaf_Controller_Abstract {

	public $gcarsdb;
	
	public $gcarmdb;

	public function init()
	{
		$this->sdb = Yaf_Registry::get('sdb');
		$this->mdb = Yaf_Registry::get('mdb');
	}

	public function __destruct()
	{
		Yaf_Registry::set('controllerobj',$this);
	}

	//yar调用触发
	public function yarAction()
	{

	}

	public function msg($msg)
	{
		$arr = array();
		if(Yaf_Registry::has('msg')){
			$arr = Yaf_Registry::get('msg');
		}
		$arr[] = $msg;
		if (!empty($msg)) {
			$arr['status'] = 200;
			$arr['msg'] = 'JUST DO IT!';
		}
		Yaf_Registry::set('msg',$arr);
	}
}