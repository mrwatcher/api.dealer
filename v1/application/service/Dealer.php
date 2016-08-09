<?php
/**
 * @name dealer-service
 * @auth chens
 * @desc 经销商服务
 */
class DealerService extends Service_Super
{

	public function searchDealers($fields,$dealer_terms,$limit = 999999,$offset = 0,$sort = array(),$group = array())
	{
		$dealer_dao = FUNC::DAO('Dealer');
		$dealer_dao->w_field = $fields;
		$res = $dealer_dao->searchByTerms($dealer_terms,$limit,$offset,$sort,$group);
		return $res;
	}

}