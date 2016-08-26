<?php
namespace Method\Common\Search;

class StateSearch extends Search
{
	public $AllowedStates = ['Active','Inactive','Deleted'];

	protected function _GetInnerWhere($table, $keywordField)
	{
		$wheres = [];
		$wheres[] = parent::_GetInnerWhere($table, $keywordField);

		if(!empty($this->AllowedStates)){
			$wheres[] = "`{$table}`.`State` {$this->GetComparator($this->AllowedStates)}";
		}

		return implode(" AND ", $wheres);
	}

}