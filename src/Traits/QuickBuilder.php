<?php
namespace Method\Common\Traits;

trait QuickBuilder
{
	// public function __construct($data = NULL)
	// {
	// 	$this->Init($data);
	// }
	public function Init($data)
	{
		if(!empty($data))
		{
			foreach ($data as $key => $value) 
			{
				if(property_exists($this, $key)){
					$this->SetProperty($key, $value);
				}
			}
		}
	}

	public function SetProperty($name, $value)
	{
		$this->$name = $value;
	}
}

