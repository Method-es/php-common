<?php
//Search.php
namespace Method\Common\Search;

use Exception;

class Search
{
	public $Offset = 0;
	public $Limit = 0;
	public $Keyword = "";
	public $OrderBy = "ID";
	public $OrderDir = "ASC";
	Public $GroupBy = "";
	public $IDs = [];

	const ORDER_COUNT_MISMATCH = "Provided Order Bys and Order Dirs are mismatched";

	public function GetLimit()
	{
		if($this->Limit == 0)
			return "";
		return " LIMIT {$this->Limit} OFFSET {$this->Offset} ";
	}

	public function GetOrder($table)
	{
		$isDirArray = is_array($this->OrderDir);
		if(is_array($this->OrderBy)){
			$orders = [];
			
			if($isDirArray && count($this->OrderDir) != count($this->OrderBy)){
				throw new Eception(self::ORDER_COUNT_MISMATCH);
			}
			for($i=0;$i<count($this->OrderBy);$i++){
				$order = $this->OrderBy[$i];
				if($isDirArray){
					$order .= " ".$this->OrderDir[$i];
				}else{
					$order .= " ".$this->OrderDir;
				}
				$orders[] = $order;
			}
			$orderBy = implode(", ", $orders);
		}else if($isDirArray){
			throw new Eception(self::ORDER_COUNT_MISMATCH); // more dirs then bys which makes no sense
		}else{
			$orderBy = "`{$table}`.`{$this->OrderBy}` {$this->OrderDir}";
		}
		return " ORDER BY {$orderBy}";
	}

	protected function _GetInnerKeywordWheres($table, $keywordField, $like)
	{
		$wheres = [];
		foreach($keywordField as $field){
			$wheres[] = "`{$table}`.`{$field}` LIKE {$like}";
		}
		return $wheres;
	}

	protected function _GetInnerWhere($table, $keywordField)
	{
		$db = GetController()->db;
		$like = $db->escape("%".$this->Keyword."%");
		if(is_array($keywordField)){
			// $wheres = [];
			// foreach($keywordField as $field){
			// 	$wheres[] = "`{$table}`.`{$field}` LIKE {$like}";
			// }
			$wheres = $this->_GetInnerKeywordWheres($table,$keywordField,$like);
			if(empty($wheres) || $this->Keyword === "") //optmize when there is no search
				$where = " WHERE 1";
			else
				$where = " WHERE (" . implode(' OR ', $wheres) . ")";
		}else{
			if($this->Keyword === "") //optmize when there is no search
				$where = " WHERE 1";
			else
				$where = " WHERE `{$table}`.`{$keywordField}` LIKE {$like}";	
		}
		if(!empty($this->IDs)){
			$where .= " AND `{$table}`.`ID` IN (".implode(',', $this->IDs).")";
		}
		return $where;
	}

	public function GetWhere($table, $keywordField, $extras = TRUE)
	{
		$where = $this->GetJoins();
		$where .= $this->_GetInnerWhere($table, $keywordField);
		$where .= $this->GetGroupBy();
		if($extras)
		{
			$where .= $this->GetOrder($table);
			$where .= $this->GetLimit();
		}

		return $where;
	}

	public function GetGroupBy()
	{
		if(empty($this->GroupBy))
			return "";
		return " GROUP BY {$this->GroupBy}\n";
	}

	public function GetJoins()
	{
		return "";
	}

	protected function GetComparator($value)
    {
    	$db = GetController()->db;
        if(is_array($value)){
        	array_walk($value, function(&$item,$idx,$db){
        		$item = $db->escape($item);
        	},$db);
        	if(count($value) > 1)
            	return "IN(".implode(",", $value).")";
            reset($value);
            return "= ".current($value);
        }else{
            return "= {$db->escape($value)}";
        }
    }
}