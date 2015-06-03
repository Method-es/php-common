<?php
namespace Method\Common\Events;
/*
    Event.php
    Author: Keaton J (April 1, 2014)

    Adds the ability for a function to allow another function to perform events

    This means during normal execution of code, another function may override 
    the function call with its own activities. 

    Functions registered for an Event, must expect one parameter, which is 
    an array of data to be passed through during the event call

    Typical Usage:
        somefile.php
        ...
            Event::Register('some.event.name',[$this,'SomeFunctionName']);

        anotherfile.php
            Event::Dispatch('some.event.name', $data);
 */

DEFINE('EVENT_FAILURE','There was an issue while performing an event');
DEFINE('EVENT_FAILURE_CODE',52);

DEFINE('EVENT_REGISTER','There was an issue while registering an event');
DEFINE('EVENT_REGISTER_CODE',53);


abstract class Event {

    private static $_Events = [];

    public static function Register($eventName, $callback)
    {
        if(!is_callable($callback,true))
            throw new \Exception(EVENT_REGISTER,EVENT_REGISTER_CODE);

        if(!array_key_exists($eventName, Event::$_Events))
            Event::$_Events[$eventName] = [];

        Event::$_Events[$eventName][] = $callback;
    }

    public static function Dispatch($eventName, $params = [])
    {
        if(array_key_exists($eventName, Event::$_Events))
        {
            foreach(Event::$_Events[$eventName] as $hook)
            {
                call_user_func($hook, $params);
            }
                
        }
    }
}