<?php
namespace Insynergi;

use Base_Controller;
use Exception;
use Insynergi\Interfaces\Auditable;
use Insynergi\Traits\Auditor;
use Insynergi\Traits\JsonModes;
use Insynergi\Traits\SingletonPool;
use JsonSerializable;

/**
 * Instructions:
 *  1. Rename the class / Set the namespace
 *  2. Set the constants
 *  3. Set the fields & properties to match the DB
 *  4. Setup the jsonSerialize function
 *  5. Done.
 */
class OBJ implements JsonSerializable, Auditable
{
    use SingletonPool {
        SingletonPool::Init as _Init;
    }
    use Auditor;
    use JsonModes;

    const SKELETON_VERSION = 0.1;

    const INVALID_ID = "Invalid ID";
    const INVALID_ID_CODE = 999;

    /**  == You can delete this ==
     * Exception Code Ranges
     * Engine Reserved = 0-100
     * Project Specific = 200+
     * Module Specific = 500+
     */

    const STATE_ACTIVE = "Active";
    const STATE_DELETED = "Deleted";

    const MAIN_MODEL = "Model";
    const MAIN_MODEL_DIRECTORY = ""; //no trailing slash
    const MAIN_TABLE = "ObjTable";

    const AUDIT_CREATE = "OBJ_CREATE";
    const AUDIT_EDIT = "OBJ_EDIT";
    const AUDIT_DELETE = "OBJ_DELETE";

    public static $Fields = [
      'ID',
      'Field1',
      'Field2',
      'Field3',
      'Field4',
      'State',
      'Created',
      'CreatedBy',
      'Modified',
      'ModifiedBy'
    ];

    public static $SearchableFields = ['Field1', 'Field2', 'Field3', 'Field4'];

    public static $States = [self::STATE_ACTIVE, self::STATE_DELETED];

    public $Field1;
    public $Field2;
    public $Field3;
    public $Field4;
    public $State = self::STATE_ACTIVE; //default to active
    public $Created;
    public $CreatedBy;
    public $Modified;
    public $ModifiedBy;

    public function __construct($data = [])
    {
        $this->Init($data);
    }

    public function Init($data)
    {
        if (empty($data)) {
            return;
        }

        $this->_Init($data);
    }

    public static function GetAuditTable()
    {
        return self::MAIN_TABLE;
    }

    public function Get()
    {
        $this->GetModel()->Get($this);
    }

    /**
     * @return \OBJModel
     */
    protected function GetModel()
    {
        static $_model = false;
        if ($_model === false) {
            /** @var Base_Controller $CI */
            $CI = GetController();
            $modelLocation = self::MAIN_MODEL;
            if(!empty(self::MAIN_MODEL_DIRECTORY)){
                $modelLocation = self::MAIN_MODEL_DIRECTORY . "/" . self::MAIN_MODEL;
            }
            $CI->load->model($modelLocation);
            $_model = $CI->{self::MAIN_MODEL};
        }
        return $_model;
    }

    public function Save($useID = false)
    {
        $auditType = self::AUDIT_EDIT;
        if (empty($this->ID)) {
            $auditType = self::AUDIT_CREATE;
        }

        $this->GetModel()->Save($this, $useID);

        $this->Audit($auditType);
    }

    public function Delete()
    {
        $this->SetProperty('State', self::STATE_DELETED);
        $this->GetModel()->Save($this);

        $this->Audit(self::AUDIT_DELETE);
    }

    public function SetProperty($name, $value)
    {
        if (!property_exists($this, $name)) {
            $className = self::class;
            throw new Exception("Property {$name} does not exist within {$className}");
        }
        $this->$name = $value;
    }

    public function GetProperty($name)
    {
        if (!property_exists($this, $name)) {
            $className = self::class;
            throw new Exception("Property {$name} does not exist within {$className}");
        }
        return $this->$name;
    }

    public function jsonSerialize()
    {
        $arr = [];
        if (static::IsJsonModeSet(JSON_MINIMAL) ||
          static::IsJsonModeSet(JSON_COMPACT)
        ) {
            $arr = [
              "ID" => (int)$this->ID,
              "Field1" => (int)$this->Field1,
              "Field2" => $this->Field2,
              "Field3" => $this->Field3
            ];
        }
        if (self::IsJsonModeSet(JSON_COMPACT)) {
            $arr += [
              "Created" => $this->Created,
              "Modified" => $this->Modified
            ];
        }
        if (self::IsJsonModeSet(JSON_FULL)) {
            $arr = $this;
        }

        //this strips out private and protected members from a JSON_FULL
        foreach ($arr as $key => $val) {
            if (startsWith($key, "\0")) {
                unset($arr[$key]);
            }
        }

        return $arr;
    }
}