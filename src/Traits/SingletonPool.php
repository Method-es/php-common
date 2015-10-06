<?php
namespace Method\Common\Traits;

trait SingletonPool
{
	use QuickBuilder;
	public $ID;
	public static $_Instances = [];
	public static function GetByData($data, $pk = "ID")
	{

		$id = NULL;
		if(is_array($data) && array_key_exists($pk, $data))
			$id = $data[$pk];
		else if(is_object($data) && property_exists($data, $pk))
			$id = $data->$pk;
		if($id === NULL)
			throw new \Exception("Cannot use SingletonPool without an {$pk}");

		$class = get_called_class();

		if(!array_key_exists($class, $class::$_Instances))
			$class::$_Instances[$class] = [];

		if(array_key_exists($id, $class::$_Instances[$class]))
		{	
			$tmp = $class::$_Instances[$class][$id];
			$tmp->Init($data); //update object with new data
			return $tmp;
		}
		
		$tmp = new $class();
		$tmp->Init($data);
		$class::$_Instances[$class][$id] = $tmp;
		return $tmp;
	}
	public static function GetByID($id, $pk = "ID")
	{
		$class = get_called_class();

		if($id === false || !is_numeric($id)){
			throw new \Exception("Invalid {$pk} Provided");
		}

		if(!array_key_exists($class, $class::$_Instances))
			$class::$_Instances[$class] = [];

		if(array_key_exists($id, $class::$_Instances[$class]))
			return $class::$_Instances[$class][$id];
		
		$tmp = new $class();
		$tmp->Init(array($pk=>$id));
		$class::$_Instances[$class][$id] = $tmp;
		return $tmp;
	}
	public static function Store($obj, $pk = "ID")
	{
		$class = get_called_class();
		if($obj instanceof $class)
		{
			if(!empty($obj) && !empty($obj->$pk))
			{
				if(!array_key_exists($class, $class::$_Instances))
					$class::$_Instances[$class] = [];

				$class::$_Instances[$class][$obj->$pk] = $obj;
				return;
			}
			throw new \Exception("Unable to store object into SingletonPool without an {$pk}");
		}
		throw new \Exception("Unable to store object into SingletonPool that is of the wrong type");
	}
}
