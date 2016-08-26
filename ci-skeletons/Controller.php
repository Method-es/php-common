<?php


class Controller extends BaseController
{
    const SKELETON_VERSION = 0.1;
    
    const DEFAULT_LIMIT = 25;

    const PATH_TO_VIEWS = "Path/To/Views/";
    
    const VIEW_SINGULAR_OBJECT_NAME = "Object";
    const VIEW_PLURAL_OBJECT_NAME = "Objects";

    const MAIN_MODEL = "Model";
    const MAIN_MODEL_DIRECTORY = ""; //no trailing slash

    function __construct()
    {
        parent::__construct();
    }

    /**
     * @return \OBJModel
     */
    protected function GetModel()
    {
        static $_model = false;
        if($_model === false){
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

    function index($ID = 0, $type = self::VIEW_TYPE_FULL)
    {
        if (empty($ID)) {
            $this->Listing();
            return;
        }
        if (!is_numeric($ID)) {
            throw new Exception(OBJ::INVALID_ID, OBJ::INVALID_ID_CODE);
        }

        /** @var OBJ $obj */
        $obj = OBJ::GetByID($ID);
        $obj->Get();

        if ($type == self::VIEW_TYPE_JSON) {
            $this->JsonEcho($obj);
            return;
        }

        $view = self::PATH_TO_VIEWS;
        $embedded = true;
        switch ($type) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case self::VIEW_TYPE_FULL:
                $embedded = false;
            case self::VIEW_TYPE_NORMAL:
            case self::VIEW_TYPE_STANDARD:
                $view .= self::VIEW_DISPLAY;
                break;
            case self::VIEW_TYPE_MINI:
                $view .= self::VIEW_MINI_DISPLAY;
                break;
        }

        $viewKeys = [
          self::VIEW_SINGULAR_OBJECT_NAME => $obj
        ];

        if (!$embedded) {
            $this->LoadView($view, $viewKeys);
        } else {
            $this->load->view($view, $viewKeys);
        }
    }

    function Listing($type = self::VIEW_TYPE_FULL, $subType = "")
    {
        $search = new Insynergi\Search\StateSearch();

        $this->load->library("FilterLib");
        $this->FilterLib->AddRule(FilterRule::CleanString('keyword', false));

        $this->FilterLib->AddRule(FilterRule::NumberRule('limit', MUST_BE_A_NUMBER, false));
        $this->FilterLib->AddRule(FilterRule::NumberRule('offset', MUST_BE_A_NUMBER, false));
        $this->FilterLib->AddRule(FilterRule::InArrayRule('orderby', OBJ::$Fields, MUST_BE . "one of " . implode(", ", OBJ::$Fields), false));
        $this->FilterLib->AddRule(FilterRule::InArrayRule('orderdir', ['asc', 'desc'], MUST_BE . "one of " . implode(", ", ['asc', 'desc']), false));
        $this->FilterLib->AddRule(FilterRule::InArrayRule('states', OBJ::$States, MUST_BE . " one of " . implode(",", OBJ::$States), false, ','));

        if (!$this->FilterLib->RunRules()) {
            $this->JsonEcho(["Errors" => $this->FilterLib->Errors]);
            return;
        }

        if (($keyword = $this->FilterLib->GetData('keyword')) !== false) {
            $search->Keyword = $keyword;
        }

        if (($limit = $this->FilterLib->GetData('limit')) !== false) {
            $search->Limit = $limit;
        } else {
            $search->Limit = self::DEFAULT_LIMIT;
            $limit = self::DEFAULT_LIMIT;
        }

        if (($offset = $this->FilterLib->GetData('offset')) !== false) {
            $search->Offset = $offset;
        }

        if (($orderby = $this->FilterLib->GetData('orderby')) !== false) {
            $search->OrderBy = $orderby;
        }

        if (($orderdir = $this->FilterLib->GetData('orderdir')) !== false) {
            $search->OrderDir = $orderdir;
        }

        if (($states = $this->FilterLib->GetData('states')) !== false) {
            $search->AllowedStates = $states;
        }

        /** @var OBJ[] $obj */
        $objs = $this->GetModel()->Search($search);
        $total = count($objs);
        if ($offset !== false || $limit !== false) {
            $total = $this->GetModel()->TotalRows($search);
        }

        $asJSON = false;
        if ($type == self::VIEW_TYPE_JSON) {
            if (empty($subType)) {
                //no view subtypes, so bail
                $this->JsonEcho($objs);
                return;
            }
            //we have a view subtype
            $asJSON = true;
            $type = $subType;
        }

        $view = self::PATH_TO_VIEWS;
        $embedded = true;
        switch ($type) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case self::VIEW_TYPE_FULL:
                $embedded = false;
            case self::VIEW_TYPE_NORMAL:
            case self::VIEW_TYPE_STANDARD:
                $view .= self::VIEW_LIST;
                break;

            // Table view is typically alwasy created, therefor is the default
            case self::VIEW_TYPE_TABLE:
                $view .= self::VIEW_TABLE_LIST;
                break;
            case self::VIEW_TYPE_ROWS: //for BC
            case self::VIEW_TYPE_TABLE_ROWS:
                $view .= self::VIEW_TABLE_LIST_ROWS;
                break;

            // These are all OPTIONAL views if the design has chosen to implement. 
            case self::VIEW_TYPE_TILED:
                $view .= self::VIEW_TILED_LIST;
                break;
            case self::VIEW_TYPE_STACKED:
                $view .= self::VIEW_STACKED_LIST;
                break;
            case self::VIEW_TYPE_COMBINED:
                $view .= self::VIEW_COMBINED_ALL;
                break;
            case self::VIEW_TYPE_STACKED_TILED:
            case self::VIEW_TYPE_TILED_STACKED:
                $view .= self::VIEW_COMBINED_TILED_STACKED;
                break;
            case self::VIEW_TYPE_TABLE_STACKED:
            case self::VIEW_TYPE_STACKED_TABLE:
                $view .= self::VIEW_COMBINED_STACKED_TABLE;
                break;
            case self::VIEW_TYPE_TABLE_TILED:
            case self::VIEW_TYPE_TILED_TABLE:
                $view .= self::VIEW_COMBINED_TABLE_TILED;
                break;

            //this is where you can add other view types

        }

        $viewKeys = [
          'pageTitle' => self::VIEW_SINGULAR_OBJECT_NAME." List",
          self::VIEW_PLURAL_OBJECT_NAME => $objs,
          'totalObjs' => (int)$total
        ];

        if (!$embedded) {
            $viewData = $this->LoadView($view, $viewKeys, $asJSON);
        } else {
            $viewData = $this->load->view($view, $viewKeys, $asJSON);
        }

        if ($asJSON) {
            $this->JsonEcho([
              "view" => $viewData,
              "offset" => $offset,
              "limit" => $limit,
              "total" => (int)$total
            ]);
        }
    }

    function Save($ID = 0)
    {
        $this->load->library("FilterLib");
        $this->FilterLib->AddRule(FilterRule::CleanString('field1', false));
        $this->FilterLib->AddRule(FilterRule::CleanString('field2', false));


        if (!$this->FilterLib->RunRules()) {
            $this->JsonEcho(["Errors" => $this->FilterLib->Errors]);
            /** @noinspection PhpInconsistentReturnPointsInspection */
            return;
        }

        /** @var OBJ $obj */
        try {
            $obj = OBJ::GetByID($ID);
            $obj->Get();
            $obj->CacheOriginal();
        } catch (Exception $e) {
            //we want to re-throw if we get any exception other then ID
            //and we only rethrow on client id, when the id is not 0
            if ($e->getCode() !== OBJ::INVALID_ID_CODE || $ID != 0) {
                throw $e;
            }
        }

        //if we have gotten here, either clientID === 0 OR the client exists

        //create a mapping to map posted param names, to object field names
        $map = [
          'field1' => 'Field1',
          'field2' => 'Field2'
        ];

        //assign anything provided by the post to the object
        foreach ($map as $key => $val) {
            if (($$key = $this->FilterLib->GetData($key)) !== false) {
                $obj->SetProperty($val,$$key);
            }
        }

        //by here client now has everything it neeeeeds...
        $obj->Save();
        $obj->ReleaseOriginal();

        if ($this->_isAjax) {
            return $this->JsonEcho($obj);
        } else {
            throw new Exception('No view for this');
        }
    }

    function Delete($ID)
    {
        /** @var OBJ $obj */
        $obj = OBJ::GetByID($ID);
        $obj->Get();
        $obj->CacheOriginal();
        $obj->Delete();
        $obj->ReleaseOriginal();
        if ($this->_isAjax) {
            return $this->JsonEcho($obj);
        } else {
            throw new Exception('No view for this');
        }
    }

    function Editor($ID = 0, $type = self::VIEW_TYPE_FULL)
    {
        if (!is_numeric($ID)) {
            throw new Exception(OBJ::INVALID_ID, OBJ::INVALID_ID_CODE);
        }
        $ID = (int)$ID;
        /** @var OBJ $obj */
        try {
            $obj = OBJ::GetByID($ID);
            $obj->Get();
        } catch (Exception $e) {
            //we want to re-throw if we get any exception other then client ID
            //and we only rethrow on client id, when the clientID is not 0
            if ($e->getCode() !== OBJ::INVALID_ID_CODE || $ID != 0) {
                throw $e;
            }
        }
        //if the client ID doesn't exist and it's zero, this is a "new client" editor

        $view = self::PATH_TO_VIEWS;
        $embedded = true;
        switch ($type) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case self::VIEW_TYPE_FULL:
                $embedded = false;
            case self::VIEW_TYPE_NORMAL:
            case self::VIEW_TYPE_STANDARD:
                $view .= self::VIEW_EDITOR;
                break;
            case self::VIEW_TYPE_MINI:
                $view .= self::VIEW_MINI_EDITOR;
                break;
        }

        $viewKeys = [
            self::VIEW_SINGULAR_OBJECT_NAME => $obj
        ];

        if (!$embedded) {
            $this->LoadView($view, $viewKeys);
        } else {
            $this->load->view($view, $viewKeys);
        }
    }


}