<?php
namespace Method\Common\Search;

class InvertSearch extends StateSearch
{
    public $SearchObj;
    public $FQNamespace = "Insynergi\\";
    // public 

    public function __construct($searchObj = null){
        if(is_object($searchObj)){
            $this->SearchObj = $searchObj;
        }
    }

    protected function _GetInnerWhere($table, $keywordField)
    {
        $fqClassName = $this->FQNamespace.$table;
        // var_dump($fqClassName::$SearchableFields);
        $nestedWhere = $this->SearchObj->GetWhere($table,$fqClassName::$SearchableFields);
        $fieldPhrase = GetFieldList(['ID'],$table);
        $query = <<<SQL
        SELECT {$fieldPhrase}
        FROM `{$table}`
        {$nestedWhere}
SQL;
        $where = parent::_GetInnerWhere($table, $keywordField);

        return $where." AND `{$table}`.`ID` NOT IN( {$query} )";
        
    }

}