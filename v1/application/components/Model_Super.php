<?php
/**
 * @name Model_Super
 * @desc model的超类
 * @author chens
 */
class Model_Super
{
    public $gcarsdb;
    public $gcarmdb;

    public $w_field = array();//字段白名单

    public $b_field = array();//字段黑名单
    
    protected $_b_field = array('sign');//默认字段黑名单

    public function __construct()
    {
        $this->db = Yaf_Registry::get('sdb');
        $this->mdb = Yaf_Registry::get('mdb');
    }

    public function fields($t = 'es')
    {
        $res = array();
        list($index,$type) = static::Index($t);
        $mapping = FUNC::EREST("/{$index}/{$type}/_mapping");
        $mapping = current(current($mapping));
        $res = array_keys($mapping[$index]['properties']);
        $this->b_field = array_merge($this->b_field,$this->_b_field);
        $res = array_diff($res,$this->b_field);
        if (!empty($this->w_field)) {
           $res = array_intersect($this->w_field,$res);
        }
        return array_values($res);
    }

    //几个公用方法
    public function searchByIdForEs($index,$id,$version = '')
    {
        $res = array();
        if (is_string($index) && ($id > 0) ) {
            $res = FUNC::ESEARCH($index,array(
                'query'=>array('term'=>array('_id'=>$id),),'size'=>'1','from'=>'0',
            ),$version);
            $res = $res['hits'][0]['_source'];
        }
        return $res;
    }

    public function searchByTermsForEs($index,$terms,$limit = 10,$offset = 0,$sort = array(),$group = array(),$version = '')
    {
        $fields = $this->fields();
        $terms_arr = array();
        $is_group = false;
        $list = array();
        $list['data'] = array();
        if (is_string($index) && is_array($terms) && !empty($terms)) {
            $search_arr = array(
                'query'=>array(
                    "filtered"=>array(
                        'query'=>array("match_all"=>array()),
                    ),
                ),
                'size'=>$limit,'from'=>$offset,
            );
            if (isset($terms['match'])) {
                unset($search_arr['query']['filtered']['query']['match_all']);
                $search_arr['query']['filtered']['query']['match'] = $terms['match'];
            }
            if (isset($terms['must'])) {
                foreach ($terms['must'] as $key => $value) {
                    if ($key != 'path') {
                        if (is_array($value)) {
                            if (isset($value['gte']) || isset($value['lte'])) {
                                $must_terms_arr[] = array('range'=>array($key=>$value));
                            }else{
                                $must_terms_arr[] = array('terms'=>array($key=>$value));
                            }
                        }else{
                                $must_terms_arr[] = array('term'=>array($key=>$value));
                        }
                    }
                }
                $search_arr['query']['filtered']['filter']['bool']['must'][0] = $must_terms_arr;
                if (isset($terms['must']['path'])) {
                    foreach ($terms['must']['path'] as $key => $val) {
                        $path = $key;
                        $path_terms_arr = array();
                        foreach ($val as $method => $value) {
                            foreach ($value as $k => $v) {
                                if (is_array($v)) {
                                    if (isset($v['gte']) || isset($v['lte'])) {
                                        $path_terms_arr[$method][] = array('range'=>array($key.'.'.$k=>$v));
                                    }else{
                                        $path_terms_arr[$method][] = array('terms'=>array($key.'.'.$k=>$v));
                                    }
                                }else{
                                        $path_terms_arr[$method][] = array('term'=>array($key.'.'.$k=>$v));
                                }
                            }
                        }
                        $search_arr['query']['filtered']['filter']['bool']['must'][] = array('nested'=>array(
                            'path'=>$path,
                            'query'=>array('bool'=>$path_terms_arr),
                        ));
                    }
                }
            }
            if (isset($terms['must_not'])) {
                foreach ($terms['must_not'] as $key => $value) {
                    if (is_array($value)) {
                        if (isset($value['gte']) || isset($value['lte'])) {
                            $must_not_terms_arr[] = array('range'=>array($key=>$value));
                        }else{
                            $must_not_terms_arr[] = array('terms'=>array($key=>$value));
                        }
                    }else{
                            $must_not_terms_arr[] = array('term'=>array($key=>$value));
                    }
                }
                $search_arr['query']['filtered']['filter']['bool']['must_not'][0] = $must_not_terms_arr;
            }
            if (isset($terms['should'])) {
                foreach ($terms['should'] as $key => $value) {
                    if (is_array($value)) {
                        if (isset($value['gte']) || isset($value['lte'])) {
                            $should_terms_arr[] = array('range'=>array($key=>$value));
                        }else{
                            $should_terms_arr[] = array('terms'=>array($key=>$value));
                        }
                    }else{
                            $should_terms_arr[] = array('term'=>array($key=>$value));
                    }
                }
                $search_arr['query']['filtered']['filter']['bool']['should'][0] = $should_terms_arr;
            }
            if (is_array($sort) && !empty($sort)) {
                foreach ($sort as $k => $value) {
                    if(in_array($k,$fields)){
                        $search_arr['sort'][0][$k] = $value;
                    }
                }
            }
            if (isset($terms['script_dive'])) {
                $script_key = key($terms['script_dive']);
                $list = current($terms['script_dive'][$script_key]);
                foreach($list as $k=>$v){
                    /**$search_arr['script_fields'][$k] = array(
                            'script'=>"list[_source.{$script_key}]['{$k}']",
                            'params'=>array('list'=>$terms['script_dive'][$script_key]),
                        );**/
                    if(!in_array($k,$fields)){
                        $fields[] = $k;
                    }
                }
                if (is_array($sort) && !empty($sort)) {
                    foreach ($sort as $k => $value) {
                        if (isset($list[$k])) {
                            $search_arr['sort'][]['_script'] = array(
                                'script'=>"list[_source.{$script_key}]['{$k}'];",
                                'params'=>array('list'=>$terms['script_dive'][$script_key]),
                                'type'=>'string',
                                'order'=>$value['order'],
                            );
                            unset($search_arr['sort'][0][$k]);
                        }
                    }
                }
            }
            $search_arr['partial_fields'] = array('fields'=>array('include'=>$fields));
            //$search_arr['fields'] = $fields;
            if (!empty($group) && isset($group['groupby']) && isset($group['orderby']) && isset($group['order']) && isset($group['size'])) {
                $is_group = true;
                $search_arr['aggs'] = array(
                        $group['groupby']=>array(
                                'terms'=>array(
                                        'field'=>$group['groupby'],
                                        'size'=>3000,
                                    ),
                                'aggs'=>array(
                                   // "comment_to_issue"=>array('reverse_nested'=>array(),'aggs'=>array(
                                    $group['orderby']=>array(
                                        'top_hits'=>array(
                                            "size"=>$group['size'],
                                            "from"=>0,
                                            "_source"=>$fields,
                                            'sort'=>array($group['orderby']=>array("order"=>$group['order'])),
                                        ),
                                    ),

                                ),
                               // )),
                            ),
                        );
                list($path,$groupby) = explode('.',$group['groupby']);
                if (!empty($groupby)) {
                    $search_arr['aggs'] = array('path_gp'=>array('nested'=>array('path'=>$path),'aggs'=>$search_arr['aggs']));
                }
            }
            //echo "<pre>";print_r($search_arr);die;
            $e_res = FUNC::ESEARCH($index,$search_arr,$version);
            //echo "<pre>";print_r($e_res);die;
            if ($is_group) {
                if (!empty($groupby)) {
                    $e_res['aggs'][$group['groupby']]['buckets'] = $e_res['aggs']['path_gp'][$group['groupby']]['buckets'];
                }
                array_walk($e_res['aggs'][$group['groupby']]['buckets'],function($value,$key)use($group,&$list) {
                    $buckets_key = $value['key'];
                    $buckets_total = $value[$group['orderby']]['hits']['total'];
                    $list['other']['buckets_total'][$buckets_key] = $buckets_total;
                    array_walk($value[$group['orderby']]['hits']['hits'],function($value,$key)use($buckets_key,&$list) {
                        if (empty($value['_source'])) {
                            $value['_source'] = array('_id'=>$value['_id']);
                        }
                        $list['data'][$buckets_key][$key] = $value['_source'];
                    });
                });
            }else{
                array_walk($e_res['hits'],function($value,$key)use(&$list) {
                    //$list['other']['sort'][$value['_id']] = $value['sort'];
                    $list['data'][$value['_id']] = $value['fields']['fields'][0];
                });
            }
            $list['other']['hits_total'] = $e_res['hits_total']?:0;
        }
        return $list;
    }
}