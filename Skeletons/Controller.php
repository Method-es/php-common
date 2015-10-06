<?php


class Controller extends INS_Controller {

	const DEFAULT_LIMIT = 25;

	function __construct()
	{
		parent::__construct();
	}
	
	function index($ID, $type = "full")
	{
		$obj = OBJ::GetByID($ID);
		$obj->Get();

		if($type == 'json'){
			$this->JsonEcho($obj);
			return;
		}

		$view = "Path/To/Views/";
		$embedded = true;
		switch($type){
			case 'full':
				$embedded = false;
			case 'normal':
			case 'standard':
				$view .= "display";
				break;
			case 'mini':
				$view .= "mini-display";
				break;
		}
		
		if(!$embedded)
			$this->strut_cms->load_view($view,['obj'=>$obj]);
		else
			$this->load->view($view,['obj'=>$obj]);
	}

	function Save($ID = 0)
	{
		$this->load->library("FilterLib");
		$this->FilterLib->AddRule(FilterRule::CleanString('field1',FALSE));
		$this->FilterLib->AddRule(FilterRule::CleanString('field2',FALSE));


		if(!$this->FilterLib->RunRules())
		{
			$this->JsonEcho(["Errors"=>$this->FilterLib->Errors]);
			return;
		}

		try{
			$obj = OBJ::GetByID($ID);
			$obj->Get();
			$obj->CacheOriginal();
		}catch(Exception $e){
			//we want to re-throw if we get any exception other then ID
			//and we only rethrow on client id, when the id is not 0
			if($e->getCode() !== INVALID_OBJ_ID_CODE || $ID != 0)
				throw $e;
		}

		//if we have gotten here, either clientID === 0 OR the client exists
		
		//create a mapping to map posted param names, to object field names
		$map = [
			'field1' => 'Field1',
			'field2' => 'Field2'
		];

		//assign anything provided by the post to the object
		foreach($map as $key => $val){
			if(($$key = $this->FilterLib->GetData($key)) !== FALSE)
				$obj->$val = $$key;
		}

		//by here client now has everything it neeeeeds...
		
		$obj->Save();
		$obj->ReleaseOriginal();

		if($this->_isAjax)
			return $this->JsonEcho($obj);
		else
			throw new Exception('No view for this yet'); //todo

	}

	function Delete($ID)
	{
		$obj = OBJ::GetByID($ID);
		$obj->Get();
		$obj->CacheOriginal();
		$obj->Delete();
		$obj->ReleaseOriginal();
		if($this->_isAjax)
			return $this->JsonEcho($obj);
		else
			throw new Exception('No view for this yet'); //todo
	}

	function Editor($ID = 0, $type = "full")
	{
		if(!is_numeric($ID))
            throw new Exception(INVALID_OBJ_ID,INVALID_OBJ_ID_CODE);
        $ID = (int)$ID;
		try{
			$obj = OBJ::GetByID($ID);
			$obj->Get();
		}catch(Exception $e){
			//we want to re-throw if we get any exception other then client ID
			//and we only rethrow on client id, when the clientID is not 0
			if($e->getCode() !== INVALID_OBJ_ID_CODE || $ID != 0)
				throw $e;
		}
		//if the client ID doesn't exist and it's zero, this is a "new client" editor
		
		$view = "Path/To/Views/";
		$embedded = true;
		switch($type){
			case 'full':
				$embedded = false;
			case 'normal':
			case 'standard':
				$view .= "editor";
				break;
			case 'mini':
				$view .= "mini-editor";
				break;
		}
		
		if(!$embedded)
			$this->strut_cms->load_view($view,['obj'=>$obj]);
		else
			$this->load->view($view,['obj'=>$obj]);
	}

	function Listing($type = "full", $subType = "")
	{
		$search = new Insynergi\Search();

		$this->load->library("FilterLib");
		$this->FilterLib->AddRule(FilterRule::CleanString('keyword',FALSE));

		$this->FilterLib->AddRule(FilterRule::NumberRule('limit',MUST_BE_A_NUMBER,FALSE));
		$this->FilterLib->AddRule(FilterRule::NumberRule('offset',MUST_BE_A_NUMBER,FALSE));
		$this->FilterLib->AddRule(FilterRule::InArrayRule('orderby',OBJ::$Fields,MUST_BE."one of ".implode(", ",OBJ::$Fields),FALSE));
		$this->FilterLib->AddRule(FilterRule::InArrayRule('orderdir',['asc','desc'],MUST_BE."one of ".implode(", ",['asc','desc']),FALSE));

		if(!$this->FilterLib->RunRules())
		{
			$this->JsonEcho(["Errors"=>$this->FilterLib->Errors]);
			return;
		}

		if(($keyword = $this->FilterLib->GetData('keyword')) !== FALSE)
			$search->Keyword = $keyword;

		if (($limit = $this->FilterLib->GetData('limit')) !== FALSE) {
            $search->Limit = $limit;
        }else{
        	$search->Limit = self::DEFAULT_LIMIT;
        	$limit = self::DEFAULT_LIMIT;
        }

		if(($offset = $this->FilterLib->GetData('offset')) !== FALSE)
			$search->Offset = $offset;

		if(($orderby = $this->FilterLib->GetData('orderby')) !== FALSE)
			$search->OrderBy = $orderby;
		
		if(($orderdir = $this->FilterLib->GetData('orderdir')) !== FALSE)
			$search->OrderDir = $orderdir;

		$this->load->model('Model');
		$objs = $this->Model->Search($search);
		$total = count($objs);
		if($offset !== false || $limit !== false){
			$total = $this->Model->TotalRows($search);
		}

		$asJSON = false;
		if($type == 'json'){
			if(empty($subType)){
				//no view subtypes, so bail
				$this->JsonEcho($objs);
				return;
			}
			//we have a view subtype
			$asJSON = true;
			$type = $subType;
		}

		$view = "Path/To/Views/";
		$embedded = true;
		switch($type){
			case 'full':
				$embedded = false;
			case 'normal':
			case 'standard':
				$view .= "list";
				break;
			case 'table':
				$view .= "table-list";
				break;
			case 'rows':
				$view .= "rows-list";
				break;
		}
		
		$viewKeys = [
        	'pageTitle' => "Obj List", 
        	'objs' => $objs,
        	'totalObjs' => (int)$total
        	];
		
		$viewData = "";
		if(!$embedded)
			$viewData = $this->strut_cms->load_view($view, $viewKeys, $asJSON);
		else
			$viewData = $this->load->view($view, $viewKeys, $asJSON);

		if($asJSON){
			$this->JsonEcho([
				"view"=>$viewData,
				"offset"=>$offset,
				"limit"=>$limit,
				"total"=>(int)$total
			]);
		}
	}


}