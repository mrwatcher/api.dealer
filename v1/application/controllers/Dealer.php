<?php
/**
 * @name DealerController
 * @author chens
 * @desc 经销商
 */
class DealerController extends Dealer_Super
{
	static $config = array(
		'search'=>array(
			'DEALERWEB'=>array('user_key'=>''),
			'DEALERTOUCH'=>array('user_key'=>''),
			'NEWCAR'=>array('user_key'=>''),
			'CMS'=>array('user_key'=>''),
			'BBS'=>array('user_key'=>''),
			),
		);
	/**
	 * －－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－
	 * 接口描述:经销商搜索
	 * －－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－
	 * 版本:2016.07.04
	 * －－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－
	 * 作者:chens
	 * －－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－
	 * 更新信息:
	 * －－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－
	 * 上行参数:
	 * [dids] 以逗号分隔的经销商ID列表
	 * [iss] 以逗号分隔的经销商类别列表
	 * [premission] 以逗号分隔的经销商等级列表
	 * [city_id] 城市ID
	 * [province_id] 省份ID
	 * [area] 经销商售卖范围ID列表
	 * [pbids] 以逗号分隔的经销商售卖父品牌列表
	 * [bids] 以逗号分隔的经销商售卖子品牌列表
	 * [pserids] 以逗号分隔的经销商售卖父车系列表
	 * [serids] 以逗号分隔的经销商售卖子车系列表
	 * [mids] 以逗号分隔的经销商售卖车型列表
	 * [coname] 经销商名称关键字
	 * [offset] 开始位置
	 * [limit] 限定条数
	 * [fields] 以逗号分隔的约定返回字段列表
	 * [sort] 排序方式(','分割多个排序条件,'-'分割排序方式和排序字段 例:'desc-did,asc-premission')
	 * [gp] 分组取每组前n个内容(分组方式-组内排序字段-前n个内容 例:'premission-sorce-1')
	 * －－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－
	 * 下行参数:
	 *
	 * 数据结构约定: 
	 *array(
	 *----0=>array(
	 *--------'data'=>array(),
	 *--------'other'=>array(
	 *------------'hits_total'=>1,
	 *------------'buckets_total'=>array(),
	 *--------),
	 *----),
	 *----'status'=>200,
	 *----'msg'=>'',
	 *);
	 *
	 *[0][data] 数据详情:
	 *
	 * [did] 经销商ID
	 * [coname] 经销商全称
	 * [coaname] 经销商简称
	 * [tel] 经销商统一展示电话
	 * [is400] 经销商电话是否为400电话
	 * [isvip] 经销商大v标记
	 * [address] 经销商地址
	 * [zuobiao_x] 经销商x坐标
	 * [zuobiao_y] 经销商y坐标
	 * [premission] 经销商等级
	 * [iss] 经销商类型
	 * [sorce] 经销商积分
	 * [sdate] 合同开始时间
	 * [edate] 合同结束时间
	 * [pbidlist] 以报价为维度的售卖父品牌列表
	 * [bidlist] 以报价为维度的售卖子品牌列表
	 * [pseridlist] 以报价为维度的售卖父车系列表
	 * [seridlist] 以报价为维度的售卖子车系列表
	 * [midlist] 以报价为维度售卖车型列表
	 * [wait400] 400电话平均等待时长
	 * [logintime] 经销商最后登陆爱卖车最后时间
	 * [slt] 统一的经销商最后发表的促销文章的更新时间
	 * [slt_title] 统一的经销商最后发表的促销文章的标题
	 * [slt_newsid] 统一的经销商最后发表的促销文章的文章ID
	 * [area] 经销商售卖范围
	 *
	 *[0][other]其他维度结果说明:
	 * [hits_total]该次搜索去除limit影响命中数据总条数
	 * [buckets_total]该次聚合去除top-num影响每个分组内的数据总数详情
	 * －－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－
	 * search-demo:
	 *
	 * 1.http://host/v1/Dealer/search/caller/dealer/limit/25
	 *	查询全部正常运营经销商结果按照规则排序取前25个(排序规则:4s店>综合店>积分>版本>最后促销文章时间>爱卖车登陆时间)
	 *
	 * 2.http://host/v1/Dealer/search/caller/dealer/premission/0.5,1,2,3,4,5/iss/3/limit/25
	 *	查询付费综合店经销商结果按照规则排序取前25个(排序规则:积分>版本>最后促销文章时间>爱卖车登陆时间)
	 *
	 * 3.http://host/v1/Dealer/search/caller/dealer/premission/0.5,1,2,3,4,5/iss/1/province_id/1/city_id/475/pserids/1/limit/25/offset/25
	 * 	查询某城市经营某车系的付费4s店经销商结果按照规则排序从第25个开始取前25个(排序规则:积分>版本>最后促销文章时间>爱卖车登陆时间)
	 *
	 * 4.http://host/v1/Dealer/search/caller/dealer/dids/37807,228/coname/北京/province_id/1/city_id/475/pserids/1/sort/desc-sorce,desc-slt/fields/coname
	 * 	查询某些经销商中名字带有某些关键字的某城市经营某车系的经销商名称结果按照规则排序(排序规则:积分>最后促销文章时间)
	 *
	 * 5.http://host/v1/Dealer/search/caller/dealer/gp/city_id-did-1
	 * 	统计全部正常运营经销商城市覆盖范围
	 *
	 * 6.http://host/v1/Dealer/search/caller/dealer/gp/pseridlist.pserid-pserid-1
	 * 	统计全部正常运营经销商车系覆盖范围
	 * －－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－
	 * 备注:
	 * 1.数据返回形式: rest:json/jsonp/serialize/html
	 * 2.数据返回编码: gb2312/utf-8
	 * 3.调用方式: YAR/HTTP GET
	 * 4.数据更新时间: mysql->es <10分钟
	 * 5.全局公共参数: 见首页
	 * －－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－
	 */
	public function searchAction($dids,$iss = '1,3',$premission = '0,1,0.5,2,3,4,5',$status = '1,3',$city_id = 0,$province_id = 0,$area = '',$pbids = '',$bids = '',$pserids = '',$serids = '',$mids = '',$coname = '',$offset = 0,$limit = 10,$fields = '',$sort = 'asc-iss,desc-sorce,desc-premission,desc-slt,desc-logintime',$gp = 0)
	{
		if (!$dids) {
			$dealer_params['must'] = array(
		    		'premission'=>explode(',',trim($premission)),
		    		'iss'=>explode(',',trim($iss)),
		    		'status'=>explode(',',trim($status)),
		    );
			if ($city_id>0) {
				$dealer_params['must']['city_id'] = $city_id;
			}
			if ($province_id>0) {
				$dealer_params['must']['province_id'] = $province_id;
			}
		}else{
			$dealer_params['must'] = array(
		    		'did'=>explode(',',trim($dids)),
		    );
		}
		if ($coname) {
			$dealer_params['match'] = array(
				'coname'=>rawurldecode($coname),
				);
		}
		if ($area) {
			$dealer_params['must'] = array(
		    		'area'=>explode(',',trim($area)),
		    );
		}
		if ($pbids) {
			$dealer_params['must']['path']['pbidlist']['must'] = array('pbid'=>explode(',',trim($pbids)));
		}
		if ($bids) {
			$dealer_params['must']['path']['bidlist']['must'] = array('bid'=>explode(',',trim($bids)));
		}
		if ($pserids) {
			$dealer_params['must']['path']['pseridlist']['must'] = array('pserid'=>explode(',',trim($pserids)));
		}
		if ($serids) {
			$dealer_params['must']['path']['seridlist']['must'] = array('serid'=>explode(',',trim($serids)));
		}
		if ($mids) {
			$dealer_params['must']['path']['midlist']['must'] = array('mid'=>explode(',',trim($mids)));
		}
		$sort_arr = array();
		$group = array();
		$sort_list = explode(',',trim($sort));
		foreach ($sort_list as $sort) {
			$_sort = explode('-',$sort);
			if (isset($_sort[1]) && isset($_sort[0])) {
				$sort_arr[$_sort[1]] = array('order' => $_sort[0]);
			}
		}
		if ($gp) {
			$_top = explode('-',$gp);
			if (isset($_top[0]) && isset($_top[1]) && isset($_top[2])) {
				$group = array('groupby'=>$_top[0],'orderby'=>$_top[1],'order'=>'desc','size'=>$_top[2]);
			}
		}
		/**$dealer_params['must_not'] = array(
		    	'did'=>228,
		   );**/
		if ($fields) {
			$fields_arr = explode(',',trim($fields));
		}else{
			$fields_arr = array(
			'did','premission','iss','is400','tel',
			'coname','coaname','bidlist','zuobiao_x',
			'zuobiao_y','iss400','address','sorce',
			'city_id','province_id','slt','slt_title','slt_newsid','area','logintime');
		}
		$dealer_list = FUNC::S('Dealer')->searchDealers($fields_arr,$dealer_params,$limit,$offset,$sort_arr,$group);
       	$this->msg($dealer_list);
	}
}
