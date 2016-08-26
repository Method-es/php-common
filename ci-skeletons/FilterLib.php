<?php

//this class will take input variables, and make sure they are clean and in the expected format
//this will extend/replace/augment the code igniter form validation class because it can be used to proces sinput instead of JUST form stuff
// the return values will be a collection of strings that identify which fields had the problem, and the reason

/*
valid filters: from http://php.net/manual/en/book.filter.php

FILTER_VALIDATE_BOOLEAN
FILTER_VALIDATE_EMAIL
FILTER_VALIDATE_FLOAT
FILTER_VALIDATE_INT
FILTER_VALIDATE_IP
FILTER_VALIDATE_REGEXP
FILTER_VALIDATE_URL

FILTER_SANITIZE_EMAIL
FILTER_SANITIZE_ENCODED
FILTER_SANITIZE_MAGIC_QUOTES
FILTER_SANITIZE_NUMBER_FLOAT
FILTER_SANITIZE_NUMBER_INT
FILTER_SANITIZE_SPECIAL_CHARS
FILTER_SANITIZE_FULL_SPECIAL_CHARS
FILTER_SANITIZE_STRING
FILTER_SANITIZE_STRIPPED
FILTER_SANITIZE_URL
FILTER_UNSAFE_RAW
FILTER_CALLBACK

*/
if(!DEFINED('DATE_REGEX'))
	DEFINE('DATE_REGEX',"((?:19|20)[0-9][0-9])-(0[1-9]|1[0-2])-([0-2][0-9]|3[01])");
if(!DEFINED('TIME_REGEX'))
	DEFINE('TIME_REGEX',"(2[0-3]|[01][0-9]):([0-5][0-9])");
if(!DEFINED('DATE_TIME_REGEX'))
	DEFINE('DATE_TIME_REGEX',DATE_REGEX." ".TIME_REGEX);

if(!DEFINED('MUST_BE'))
	DEFINE('MUST_BE','Must be ');
if(!DEFINED('MUST_BE_A_NUMBER'))
	DEFINE('MUST_BE_A_NUMBER',MUST_BE.'a number');
if(!DEFINED('MUST_BE_A_BOOL'))
	DEFINE('MUST_BE_A_BOOL',MUST_BE.'a yes/no|true/false|on/off|1/0');
if(!DEFINED('MUST_BE_A_DATE'))
	DEFINE('MUST_BE_A_DATE',MUST_BE.'a date YYYY-MM-DD');
if(!DEFINED('MUST_BE_A_TIME'))
	DEFINE('MUST_BE_A_TIME',MUST_BE.'a time HH:MM');
if(!DEFINED('MUST_BE_A_DATETIME'))
	DEFINE('MUST_BE_A_DATETIME',MUST_BE.'a date time YYYY-MM-DD HH:MM');
if(!DEFINED('MUST_BE_A_EMAIL'))
	DEFINE('MUST_BE_A_EMAIL',MUST_BE.'a valid email');
if(!DEFINED('MUST_BE_A_PHONE'))
	DEFINE('MUST_BE_A_PHONE',MUST_BE.'a valid phone number');

/* 
* things we need to process a value is:
*	- a key (name of the variable)
*	- a filter (or collection of)
*	- any options
*/

if(!function_exists("FilterOutNonNumeric"))
{
	function FilterOutNonNumeric($data)
	{
		return preg_replace("/\D/", "", $data); // \D stands for any non-digit
	}
}
if(!function_exists("FilterForPhoneLengths"))
{
	function FilterForPhoneLengths($data)
	{
		if(strlen($data) < 10)
			return FALSE;
		if(strlen($data) > 25)
			return FALSE;
		return $data;
	}
}

class FilterForInArray
{
	public $array;
	public function __construct($array)
	{
		$this->array = $array;
	}
	public function InArray($data)
	{
		if(!in_array($data, $this->array))
			return FALSE;
		return $data;
	}
}

class FilterRule
{
	public $FieldName;
	public $Required;
	public $Filters = [];
	public $Options = [];
	public $ErrorMsgs = [];
	public $AcceptEmptyStrings = FALSE;
	public $isArray = FALSE; //this will actually hold the seperator to use on the array
	public function __construct($name, $filters, $options, $msgs, $required = TRUE, $isArray = FALSE)
	{
		$this->FieldName = $name;
		$this->Filters = $filters;
		$this->Options = $options;
		$this->ErrorMsgs = $msgs;
		$this->Required = $required;
		$this->isArray = $isArray;
	}
	//uses a sanitize if it is called CLEAN
	//uses only filters if it is a RULE
	public static function CleanPhone($name,$required = TRUE, $isArray = FALSE, $acceptEmptyStrings = FALSE)
	{
		$rule = new FilterRule($name,[FILTER_CALLBACK,FILTER_CALLBACK],[["options"=>"FilterOutNonNumeric"],["options"=>"FilterForPhoneLengths"]],["",MUST_BE_A_PHONE." length (min 10 - max 25)"],$required,$isArray);
		$rule->AcceptEmptyStrings = $acceptEmptyStrings;
		return $rule;
	}
	public static function CleanString($name,$required = TRUE, $isArray = FALSE, $acceptEmptyStrings = FALSE)
	{
		$rule = new FilterRule($name,[FILTER_SANITIZE_FULL_SPECIAL_CHARS],[NULL],[""],$required,$isArray);
		$rule->AcceptEmptyStrings = $acceptEmptyStrings;
		return $rule;
	}
	public static function CleanHTML($name, $required = TRUE, $isArray = FALSE)
	{
		return new FilterRule($name,[FILTER_UNSAFE_RAW],[FILTER_FLAG_ENCODE_LOW | FILTER_FLAG_ENCODE_HIGH],[""],$required,$isArray);
	}
	public static function CleanURL($name,$msg,$required = TRUE, $isArray = FALSE)
	{
		return new FilterRule($name,[FILTER_SANITIZE_URL,FILTER_VALIDATE_URL],[NULL,NULL],["",$msg],$required,$isArray);
	}
	public static function CleanEmail($name,$msg = MUST_BE_A_EMAIL,$required = TRUE, $isArray = FALSE)
	{
		return new FilterRule($name,[FILTER_SANITIZE_EMAIL,FILTER_VALIDATE_EMAIL],[NULL,NULL],["",$msg],$required,$isArray);
	}
	public static function EmailRule($name,$msg = MUST_BE_A_EMAIL,$required = TRUE, $isArray = FALSE)
	{
		return new FilterRule($name,[FILTER_VALIDATE_EMAIL],[NULL],[$msg],$required,$isArray);
	}
	public static function RegexRule($name,$regex,$msg,$required = TRUE, $isArray = FALSE)
	{
		return new FilterRule($name,[FILTER_VALIDATE_REGEXP],[["options"=>["regexp"=>$regex]]],[$msg],$required,$isArray);
	}
	public static function NumberRule($name,$msg = MUST_BE_A_NUMBER,$required = TRUE, $isArray = FALSE)
	{
		return new FilterRule($name,[FILTER_VALIDATE_INT],[NULL],[$msg],$required,$isArray);
	}
	public static function NumberRangeRule($name,$range, $msg = MUST_BE_A_NUMBER,$required = TRUE, $isArray = FALSE)
	{
		if(array_key_exists('min_range', $range))
			$msg .= " greater then ".$range['min_range'];
		if(array_key_exists('max_range', $range))
			$msg .= " less then ".$range['max_range'];
		return new FilterRule($name,[FILTER_VALIDATE_INT],[["options"=>$range]],[$msg],$required,$isArray);
	}
	public static function FloatRule($name,$msg = MUST_BE_A_NUMBER,$required = TRUE, $isArray = FALSE)
	{
		return new FilterRule($name,[FILTER_VALIDATE_FLOAT],[NULL],[$msg],$required,$isArray);
	}
	public static function TimeRule($name,$msg = MUST_BE_A_TIME,$required = TRUE, $isArray = FALSE)
	{
		return STATIC::RegexRule($name,"/".TIME_REGEX."/",$msg,$required, $isArray = FALSE,$isArray);
	}
	public static function DateRule($name,$msg = MUST_BE_A_DATE,$required = TRUE, $isArray = FALSE)
	{
		return STATIC::RegexRule($name,"/".DATE_REGEX."/",$msg,$required,$isArray);
	}
	public static function DateTimeRule($name,$msg = MUST_BE_A_DATETIME,$required = TRUE, $isArray = FALSE)
	{
		return STATIC::RegexRule($name,"/".DATE_TIME_REGEX."/",$msg,$required,$isArray);
	}
	public static function InArrayRule($name,$array,$msg,$required = TRUE, $isArray = FALSE)
	{
		return new FilterRule($name,[FILTER_CALLBACK],[["options"=>[new FilterForInArray($array),"InArray"]]],[$msg],$required,$isArray);
	}
	public static function BoolRule($name,$msg = MUST_BE_A_BOOL,$required = TRUE, $isArray = FALSE)
	{
		return new FilterRule($name,[FILTER_VALIDATE_BOOLEAN],[FILTER_NULL_ON_FAILURE],[$msg],$required,$isArray);
	}
}

/*
Typical Usage:

        //**
        //* Given POST prarms $sort (string) and  $offset (int)
        //*
        //*
        $this->load->library("FilterLib");
        $filterLib = new FilterLib();
        $filterLib->AddRule(FilterRule::CleanString('sort'));
        $filterLib->AddRule(FilterRule::NumberRule('offset'));
        if($filterLib->RunRules()) {
            //POST IS CLEAN
        }

		//start it up
		$this->load->library("FilterLib");
		$filterLib = new FilterLib(array($this->input,"post"));
		//add rules
		$filterLib->AddRule(FilterRule::NumberRule("location",MUST_BE_A_NUMBER,TRUE));
		//run it
		if(!$filterLib->RunRules())
		{
			echo json_encode(["Errors"=>$filterLib->Errors]);
			return;
		}
		//fetch it
		$locationID = $filterLib->GetData("location");

*/

class FilterLib
{

	//protected $Ran = false;
	protected $Rules = [];

	protected $innerErrors;

	protected $inputCallback = NULL;

	public $Errors = [];
	public $Data = [];

	public function __construct( callable $inputRetriever = NULL)
	{
		if($inputRetriever !== NULL)
			$this->RegisterInputCallback($inputRetriever);
		else
		{
			$CI = GetController();
			$this->RegisterInputCallback([$CI->input,"post"]);
		}
	}

	public function ResetRules()
	{
		$this->Rules = [];
	}

	public function Reset()
	{
		$this->ResetRules();
		$this->Errors = [];
		$this->Data = [];
		$this->RegisterInputCallback(NULL);
	}

	public function AddRule(FilterRule $rule)
	{
		$this->Rules[] = $rule;
	}

	public function RegisterInputCallback( callable $inputRetriever = NULL)
	{
		$this->inputCallback = $inputRetriever;
	}

	public function GetData($name)
	{
		if(array_key_exists($name, $this->Data))
			return $this->Data[$name];
		return FALSE;
	}

	public function RunRules($input = NULL)
	{
		$errors = [];
		$data = [];
		foreach($this->Rules as $rule)
		{
			$this->innerErrors = [];
			$value = FALSE;
			if($this->inputCallback == NULL)
			{
				if(array_key_exists($rule->FieldName, $input))
					$value = $input[$rule->FieldName];
			}else
			{
				$value = call_user_func($this->inputCallback,$rule->FieldName);
			}

			//check for boolean nulls
			for($i=0;$i<count($rule->Filters);$i++){
				$filter = $rule->Filters[$i];
				if($filter == FILTER_VALIDATE_BOOLEAN && $value === FALSE && !$rule->Required && count($rule->Filters) === 1){
					$data[$rule->FieldName] = NULL;
					continue;
				}
			}

			if($value === FALSE)
			{
				//log error if item is required
				if($rule->Required)
					$errors[$rule->FieldName] = [$rule->FieldName." is required"];
				continue;
			}

			//if the value is not required, but provided, as an empty string
			if($value == '' && !$rule->Required && !$rule->AcceptEmptyStrings)
				continue;

			if($rule->isArray !== FALSE)
			{
				// if($value == '' && !$rule->Required){
				// 	$value = [''];
				// }else{
					if($rule->isArray === TRUE){
						$values = $value;
					}else{
						$values = explode($rule->isArray, $value);	
					}
					if(is_string($values)){
						$values = [];
					}
					if($rule->Required && empty($values)){
						$errors[$rule->FieldName] = [$rule->FieldName." is required"];
						continue;
					}
					$tmpVals = [];
					foreach($values as $key => $tmpVal)
					{
						$tmpVals[$key] = $this->_RunFilters($rule,$tmpVal);
					}
					$value = $tmpVals;	
				// }
			}
			else
			{
				$value = $this->_RunFilters($rule,$value);
			}

			if(empty($this->innerErrors))
				$data[$rule->FieldName] = $value;
			else
				$errors[$rule->FieldName] = $this->innerErrors;
		}
		$this->Data = $data;
		$this->Errors = $errors;
		$this->innerErrors = [];
		return (count($this->Errors) == 0);
	}
	protected function _RunFilters($rule,$value)
	{
		$value = trim($value);
		//process saved rules
		for($i=0;$i<count($rule->Filters);$i++)
		{
			//if we allow empty strings, and they provided an empty string, then we can skip all our other filters
			if($i === 0 && $value == '' && $rule->AcceptEmptyStrings)
				break;
			$filter = $rule->Filters[$i];
			$option = NULL;
			if(array_key_exists($i, $rule->Options))
				$option = $rule->Options[$i];
			$msg = "";
			if(array_key_exists($i, $rule->ErrorMsgs))
				$msg = $rule->ErrorMsgs[$i];
			$value = filter_var($value, $filter,$option);
			if(($filter == FILTER_VALIDATE_BOOLEAN && $value === NULL) ||
				($filter != FILTER_VALIDATE_BOOLEAN && $value === FALSE))
				$this->innerErrors[] = $msg;
			if(($filter == FILTER_SANITIZE_FULL_SPECIAL_CHARS || $filter == FILTER_UNSAFE_RAW) &&
				$rule->Required && $value === '')
				$this->innerErrors[] = $rule->FieldName . " is required";
		}
		return $value;
	}
}



//eof