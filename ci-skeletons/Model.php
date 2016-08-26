<?php

die('wtf mate?');
// ** Either use aliasing to avoid having to change anything;
// ** or rename all OBJ instances to the class name

//use \Insynergi\SomeClass as OBJ;
use Method\Common\Interfaces\WhereQueryBuilder;

/**
 * @property CI_Session Session
 * @property CI_DB_driver db
 */
class OBJModel extends Model
{
    const SKELETON_VERSION = 0.1;

    public $Table = 'table';

    public function __construct()
    {
        parent::__construct();
    }

    /*
     * This is for when you wish to force a User with a particular ID.
     * Use with caution.  There is no check for duplicate ID's here.
     */

    public function Save(OBJ $obj, $useID = false)
    {
        $userID = $this->Session->userdata('UserID');
        if ($userID == false) {
            $userID = 0;
        }

        //if modified by is requried to be set, use the following if statement before setting the modifiedby field
        //if(is_null($obj->ModifiedBy))
        $obj->ModifiedBy = $userID;

        if (empty($obj->ID) || $useID) {
            if (is_null($obj->CreatedBy)) {
                $obj->CreatedBy = $userID;
            }
            $this->_Insert($obj, $useID);
        } else {
            $updatePhrase = GetUpdatePhrase(OBJ::$Fields, $obj, $this->Table,
              ['Created', 'Modified', 'CreatedBy']);
            $query = <<<SQL
			UPDATE `{$this->Table}` SET
				{$updatePhrase}
			WHERE
				`{$this->Table}`.`ID` = {$this->db->escape($obj->ID)}
SQL;
            $this->db->query($query);
        }
        return $obj;
    }

    public function _Insert(OBJ $obj, $useID = false)
    {
        $ignoreFields = [];
        if (empty($obj->Created)) {
            $ignoreFields[] = 'Created';
        }
        if (empty($obj->Modified)) {
            $ignoreFields[] = 'Modified';
        }
        if (!$useID) {
            $ignoreFields[] = 'ID';
        }
        $fieldPhrase = GetFieldList(OBJ::$Fields, $this->Table, $ignoreFields);
        $insertPhrase = GetInsertPhrase(OBJ::$Fields, $obj, $ignoreFields);
        $query = <<<SQL
				INSERT INTO `{$this->Table}`
					({$fieldPhrase})
				VALUES
					({$insertPhrase})
SQL;
        $this->db->query($query);

        /** @noinspection PhpUndefinedMethodInspection */
        if ($this->db->affected_rows() > 0) {
            /** @noinspection PhpUndefinedMethodInspection */
            $obj->ID = $this->db->insert_id();
        }
    }

    public function Get(OBJ $obj)
    {
        if (empty($obj) || empty($obj->ID)) {
            throw new Exception(OBJ::INVALID_ID, OBJ::INVALID_ID_CODE);
        }

        $fieldPhrase = GetFieldList(OBJ::$Fields, $this->Table);
        $query = <<<SQL
		SELECT {$fieldPhrase}
		FROM `{$this->Table}`
		WHERE `{$this->Table}`.`ID` = {$this->db->escape($obj->ID)}
SQL;
        $results = $this->db->query($query);
        $result = GetResult($results);
        if ($result === false) {
            throw new Exception(OBJ::INVALID_ID, OBJ::INVALID_ID_CODE);
        }
        $obj->Init($result);
        return $obj;
    }

    public function TotalRows(WhereQueryBuilder $search)
    {
        $where = $search->GetWhere($this->Table, OBJ::$SearchableFields, false);

        $groupBy = $search->GetGroupBy();
        if (empty($groupBy)) {
            $query = <<<SQL
        SELECT COUNT(DISTINCT `ID`) as `RowCount`
        FROM `{$this->Table}`
        {$where}
SQL;
        } else {
            $query = <<<SQL
        SELECT COUNT(*) as `RowCount`
        FROM (SELECT DISTINCT `{$this->Table}`.`ID`
                FROM `{$this->Table}` {$where} ) as `tmp`
SQL;
        }

        $results = $this->db->query($query);
        return $results->row()->RowCount;
    }

    public function Search(WhereQueryBuilder $search)
    {
        $where = $search->GetWhere($this->Table, OBJ::$SearchableFields);
        return $this->GetAll($where);
    }

    public function GetAll($where = "")
    {
        $fieldPhrase = GetFieldList(OBJ::$Fields, $this->Table);
        $query = <<<SQL
		SELECT {$fieldPhrase}
		FROM `{$this->Table}`
		{$where}
SQL;
        $results = $this->db->query($query);
        $objs = GetResults($results, OBJ::class);

        return $objs;
    }

}

