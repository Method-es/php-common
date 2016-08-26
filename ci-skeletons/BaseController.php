<?php

use Insynergi\Setting\Setting;
use Insynergi\User\User;

/**
 * @property CI_Session Session
 * @property CI_Config config
 * @property CI_Loader load
 * @property FilterLib FilterLib
 * @property CI_URI uri
 * @property CI_Input input
 */
class BaseController extends CI_Controller
{
	public $_isAjax = false;

	public $CurrentUser;
    
    const VIEW_PAGE_TEMPLATE = "Templates/PageTemplate";

	const VIEW_TYPE_FULL = "full";
	const VIEW_TYPE_NORMAL = "normal";
	const VIEW_TYPE_STANDARD = "standard";
	const VIEW_TYPE_MINI = "mini";
	const VIEW_TYPE_JSON = "json";
	const VIEW_TYPE_TABLE = "table";
	const VIEW_TYPE_ROWS = "rows";
	const VIEW_TYPE_TABLE_ROWS = "table-rows";
	const VIEW_TYPE_TILED = "tiled";
	const VIEW_TYPE_STACKED = "stacked";
	const VIEW_TYPE_COMBINED = "combined";
	const VIEW_TYPE_STACKED_TILED = "stacked-tiled";
	const VIEW_TYPE_TILED_STACKED = "tiled-stacked";
	const VIEW_TYPE_TABLE_STACKED = "table-stacked";
	const VIEW_TYPE_STACKED_TABLE = "stacked-table";
	const VIEW_TYPE_TABLE_TILED = "table-tiled";
	const VIEW_TYPE_TILED_TABLE = "tiled-table";

	const VIEW_LIST = "list";
	const VIEW_DISPLAY = "display";
	const VIEW_MINI_DISPLAY = "mini-display";
	const VIEW_TABLE_LIST = "table-list";
	const VIEW_TABLE_LIST_ROWS = "table-list-rows";
	const VIEW_TILED_LIST = "tiled-list";
	const VIEW_STACKED_LIST = "stacked-list";
	const VIEW_COMBINED_ALL = "combined-all";
	const VIEW_COMBINED_TILED_STACKED = "combined-tiled-stacked";
	const VIEW_COMBINED_STACKED_TABLE = "combined-stacked-table";
	const VIEW_COMBINED_TABLE_TILED = "combined-table-tiled";
	const VIEW_EDITOR = 'editor';
	const VIEW_MINI_EDITOR = 'mini-editor';

	function __construct()
	{
		parent::__construct();
		$this->load->helper('url');

		$this->_isAjax = $this->input->is_ajax_request();

//		$this->load->library('UserLib');
//		try{
//			$this->CurrentUser = User::GetBySessionData();
//		}catch(Exception $e)
//		{
//		}
	}
//
//	public function isLoggedIn()
//	{
//		return !empty($this->CurrentUser);
//	}

	function buffered_echo($string,$_ci_return = false,$headers = array()) {
		$this->BufferedEcho($string,$_ci_return,$headers);
	}

	function BufferedEcho($string, $return = false, $headers = array())
	{
		$this->output->append_output($string);
		foreach($headers as $current)
			$this->output->set_header($current);	
	}

	function JsonEcho($data,$opts=0)
	{
		$this->buffered_echo(json_encode($data,$opts),FALSE,['Content-Type: application/json']);
	}

	function load_view($view, $data = array(),$return = false){
		$this->LoadView($view,$data,$return);
	}
	
	function LoadView($view, $data = array(),$return = false)
	{
        $data['templates'] = array();
        $data['templates'][] = $this->load->view($view, $data, true);

        $source = APPPATH."views/Templates/Components";
        if(file_exists($source)){
	        $it = new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS);
	        $ri = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::SELF_FIRST);

	        foreach ($ri as $file) {
	            $filename = $file->getFilename();
	            if (!$file->isDir() && $filename[0] != '.') {
	            	$path = $file->getPathname();
	            	//now strip APPPATH."views/" from it
	            	$path = str_replace(APPPATH."views/", "", $path);
	                $data['templates'][] = $this->load->view($path, $data, true);
	            }
	        }
	    }
        
		return $this->load->view(static::VIEW_PAGE_TEMPLATE, $data, $return);
	}

	public function _remap($method, $params = array())
	{
		if (method_exists($this, $method))
		{
			return call_user_func_array(array($this, $method), $params);
		}else
		{
			array_unshift($params,$method);
			if(method_exists($this, 'index'))
			{
				$reflection = new ReflectionMethod($this, 'index');
				$paramCount = count($params);
				if($reflection->getNumberOfRequiredParameters() <= $paramCount &&
					$reflection->getNumberOfParameters() >= $paramCount)
					return call_user_func_array(array($this, 'index'), $params);
			}
			$className = get_class($this);
			show_404("{$className}/{$method}");
		}
	}
	public function SmoothRedirect($location,$data=[])
	{
		if($location == USER_LOGIN_INTERCEPT || $location == ADMIN_LOGIN_INTERCEPT)
			$this->Session->set_userdata('referer',$this->uri->uri_string());
		if($this->input->is_ajax_request())
			$this->JsonEcho(array_merge(["Location"=>$location],$data));
		else
			redirect($location,'location');
		$this->output->_display();
		exit; //this is what redirect does...
	}
}
