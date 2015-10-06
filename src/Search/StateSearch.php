<?php
namespace Method\Common\Search;

class StateSearch extends Search
{
	public $AllowedStates = ['Active','Inactive','Deleted'];

	protected function _GetInnerWhere($table, $keywordField)
	{
		$where = parent::_GetInnerWhere($table, $keywordField);
		return $where . " AND `{$table}`.`State` IN('".implode("','", $this->AllowedStates)."')";
	}

}