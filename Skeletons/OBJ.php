<?php
namespace Insynergi;

use Exception;
use JsonSerializable;

use Insynergi\Interfaces\Auditable;
use Insynergi\Traits\Auditor;

use Insynergi\Traits\SingletonPool;
use Insynergi\Traits\QuickBuilder;

use Insynergi\Traits\JsonModes;

class OBJ implements JsonSerializable, Auditable
{
	use SingletonPool;
	use Auditor;

	use JsonModes;

	public static $Fields = ['ID', 'Field1', 'Field2', 'Field3', 'Field4',
								'State', 'Created', 'CreatedBy', 'Modified', 'ModifiedBy'];

	public static $SearchableFields = ['Field1','Field2','Field3','Field4'];

	public static $States = ['Active','Deleted'];

    const INVALID_ID = "Invalid ID";
    const INVALID_ID_CODE = 999;

	public $Field1;
	public $Field2;
	public $Field3;
	public $Field4;
    public $State;
    public $Created;
    public $CreatedBy;
    public $Modified;
    public $ModifiedBy;

    public function SetProperty($name, $value)
    {
        $this->$name = $value;
    }

	public function Get()
	{
		$CI = GetController();
		$CI->load->model('Model');
		$CI->Model->Get($this);
	}

	public function Save($useID = FALSE)
	{
		$auditType = 'OBJ_EDIT';
        if(empty($this->ID))
            $auditType = 'OBJ_CREATE';

		$CI = GetController();
		$CI->load->model('Model');
		$CI->Model->Save($this,$useID);

		$this->Audit($auditType);
	}

	public function Delete()
	{
		$CI = GetController();
		$CI->load->model('Model');
		$this->State = 'Deleted';
		$CI->Model->Save($this);

		$this->Audit('OBJ_DELETE');
	}

	public function jsonSerialize()
	{
		if(self::IsJsonModeSet(JSON_MINIMAL)){
             $arr = [
                "ID" => (int)$this->ID,
                "Field1" => (int)$this->Field1,
                "FIeld2" => $this->FIeld2,
                "Field3" => $this->Field3
             ];
        }
        if(self::IsJsonModeSet(JSON_COMPACT)){
            $arr += [
                "Created" => $this->Created,
                "Modified" => $this->Modified
            ];
        }
        if(self::IsJsonModeSet(JSON_FULL)){
            $arr = $this;
        }
		return $arr;
	}

	public static function GetAuditTable()
    {
        return 'Obj';
    }
}