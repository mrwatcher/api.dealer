<?php
/**
 * @name DealerModel
 * @desc 经销商模型
 * @author chens
 */
class DealerModel extends Model_Super
{
    public function __construct()
    {
        parent::__construct();
    }

    static function Index($t)
    {
        $res = array();
        switch ($t) {
            case 'es':
                $res = array('dealer','dealer');
                break;
        }
        return $res;
    }

    public function searchById($did)
    {
        return $this->searchByIdForEs('dealer',$did);
    }

    /**
     * 根据条件获取did列表
     */
    public function searchByTerms($terms,$limit = 9999999,$offset = 0,$sort = array(),$group = array())
    {
        return $this->searchByTermsForEs('dealer',$terms,$limit,$offset,$sort,$group);
    }

}
