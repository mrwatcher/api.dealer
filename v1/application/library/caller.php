<?php
/**
 * @desc Caller 调用者管理
 * @version 1.0
 * @author chens
 * @date 2016-07-18
 */
class Caller
{
    static $caller_list = array(
    	'XCAR'=>0,
        'DEALER'=>0,
        'DEALERWEB'=>0,
        'DEALERTOUCH'=>0,
		'DEALERAPP'=>0,
		'NEWCAR'=>0,
		'BBS'=>0,
		'CMS'=>0,
    	'ESTEST'=>0,
    	'ALL'=>0,
    	);

        static $now;
        static $pre;

   	private static function __init__()
    {
    	foreach (self::$caller_list as $key => &$value)
    	{
    	 	$value = md5('dh*klg');
    	}
    }

    public static function get($k = 0)
    {
    	self::__init__();
    	return !$k?self::$caller_list:self::$caller_list[$k];
    }


}







