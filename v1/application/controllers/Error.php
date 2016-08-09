<?php
/**
 * @name ErrorController
 * @desc 错误控制器, 在发生未捕获的异常时刻被调用
 * @author chens
 */
class ErrorController extends Yaf_Controller_Abstract
{
	public function errorAction($exception)
	{
		$exc = (array) $exception;
		echo json_encode(array('status'=>500,'msg'=>(defined('RUN_MODE') && RUN_MODE == 'DEV')?FUNC::WANTCODE($exc,'UTF-8'):'this is error' ));die;
	}
}
