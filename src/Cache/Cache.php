<?php
namespace Method\Common\Cache;

class Cache 
{
    /* 
        General gist for this lib is to cache data to the local file system
        and allow retrieval 

        Features:
            - This is ONE lone cache; One file
            - It is loaded when the first item is retrieved
            - it is written each time data is stored within
            - It cares about consistancy, so if the file has changed between loading, and saving; this cache is DISCARDED

        How To use:
            1. Create the cache object
            2. a. If retrieving, call Get or GetAll
               b. if storing, call Store
            3. That's it
    */
   
    const CACHE_SERIALIZE = 0x01;
    const CACHE_JSON = 0x10;

    private $_cacheName = "default";
    private $_cacheExtension = ".cache";
    private $_cachePath = "cache/";

    private $_cacheExpiry = 0;

    private $_cacheMode = self::CACHE_JSON;

    private $_cacheData = [];
    private $_cacheModTime;
    private $_cacheLoaded = false;

    private $_cacheChanged = false;

    public function __construct($cacheName = false, $cachePath = false, $cacheExt = false, $cacheMode = false, $cacheExpiry = false)
    {
        $this->Init($cacheName, $cachePath, $cacheExt, $cacheMode, $cacheExpiry);
        $this->Clear();
    }

    public function Init($cacheName = false, $cachePath = false, $cacheExt = false, $cacheMode = false, $cacheExpiry = false)
    {
        if(!empty($cacheName))
            $this->_cacheName = $cacheName;
        if(!empty($cachePath))
            $this->_cachePath = $cachePath;
        if(!empty($cacheExt))
            $this->_cacheExtension = $cacheExt;
        if(!empty($cacheMode))
            $this->_cacheMode = $cacheMode;
        if(!empty($cacheExpiry))
            $this->_cacheExpiry = $cacheExpiry;
    }

    public function Store($key,$value)
    {
        // don't really matter much about here; just set the value, and call save
        $this->_cacheData[$key] = $value;
        $this->_cacheChanged = true;
        $this->Save();
    }

    public function Get($key, $default = false)
    {
        //if it has not been loaded, try to load it
        if(!$this->_cacheLoaded){
            $this->Load();
        }
        //now only work upon the data if the load worked
        if($this->_cacheLoaded){
            if(array_key_exists($key, $this->_cacheData)){
                //we have the key
                return $this->_cacheData[$key];
            }
        }
        return $default;
    }

    public function StoreAll($data)
    {
        // we need to accept only associative arrays here... to be fair we could just accept arrays
        // if it is; then we simple "merge" it into our existing data array, let it overwrite keys
        if(!is_array($data)){
            throw new CacheException();
        }

        $this->_cacheData = array_merge($this->_cacheData,$data);
        $this->_cacheChanged = true;
        $this->Save();
    }

    public function GetAll()
    {
        //if it has not been loaded, try to load it
        if(!$this->_cacheLoaded){
            $this->Load();
        }
        //now only work upon the data if the load worked
        if($this->_cacheLoaded){
            return $this->_cacheData;
        }
        return false;
    }

    public function Clear()
    {
        $this->_cacheData = [];
        $this->Save();
    }

    public function Remove($key)
    {
        if(array_key_exists($key, $this->_cacheData)){
            unset($this->_cacheData[$key]);
        }
        $this->Save();
    }

    public function Destroy()
    {
        $this->Clear();
        $this->_RemoveCache();
    }

    private function _RemoveCache()
    {
        $filename = $this->GetCacheLocation();
        if(file_exists($filename)){
            unlink($filename);
            $this->_cacheLoaded = false;
            $this->_cacheChanged = false;
            $this->_cacheModTime = null;
        }
    }

    public function IsCachable()
    {
        return is_writable($this->_cachePath);
    }

    public function IsLoaded()
    {
        return $this->_cacheLoaded;
    }

    private function _StoreData(){
        if($this->_cacheMode == self::CACHE_SERIALIZE){
            return serialize($this->_cacheData);
        }else if($this->_cacheMode == self::CACHE_JSON){
            return json_encode($this->_cacheData,JSON_PRETTY_PRINT);
        }
        throw new CacheException();
    }

    private function _LoadData($data){
        if($this->_cacheMode == self::CACHE_SERIALIZE){
            return unserialize($data);
        }else if($this->_cacheMode == self::CACHE_JSON){
            return json_decode($data,true);
        }
        throw new CacheException();
    }

    public function Save()
    {
        $filename = $this->GetCacheLocation();
        //things we care about:
        //  loaded state will mean we have a modtime to compare
        //  if no data in the cache has changed (ie store has never been called) don't save.
        //  if the modtime has changed since we loaded, 
        //      we don't want to overwrite the cache, so bail early
        //      [OPTIONAL] we could potentially looking at loading the cache again, 
        //                 and merging the results back together and caching the lot
        //  if the modtime has not changed, we will just write the data out to the file
        if(!$this->_cacheChanged){
            return false;
        }

        //when rechecking the mtime, or existance, we need to clear the stat cache
        clearstatcache(TRUE,$filename);

        if(file_exists($filename) && is_readable($filename)){
            $modTime = filemtime($filename);
            if($modTime > $this->_cacheModTime){
                //mod times changed, so we can't trust it
                return false;
            }
        }

        //by here we all good, so just put the contents (if we can)
        if(file_exists($filename) && is_writable($filename) ||
            is_writable($this->_cachePath)){
            //we CAN save this
            file_put_contents($filename, $this->_StoreData(), LOCK_EX);
            $this->_cacheLoaded = true;
            clearstatcache(TRUE,$filename);
            $this->_cacheModTime = filemtime($filename);
            // since we saved it, we more or less just loaded it since we have a mirror copy internally
            return true;
        }
        
        return false;
    }

    public function Load()
    {
        $filename = $this->GetCacheLocation();
        //first check if it's there
        if(file_exists($filename) && is_readable($filename)){
            //it's there!
            //try to load it!
            $modTime = filemtime($filename);

            //check expiry first
            if($this->_cacheExpiry > 0 && $modTime < (time() - $this->_cacheExpiry)){
                $this->_RemoveCache();
                return;
            }

            $data = file_get_contents($filename);
            if($data !== false){
                //we got data!
                $data = $this->_LoadData($data);
                if($data === false){
                    //we couldn't load the data (corrupt or unparsible with the current mode)
                    // TODO: potentially try to laod it with the other mode; then we can tell them 
                    //       they need to clear the cache before changing modes
                    return;
                }
                $this->_cacheData = $data;
                $this->_cacheModTime = $modTime;
                $this->_cacheLoaded = true;
                $this->_cacheChanged = false;
            }
        }
    }

    public function GetCacheLocation()
    {
        return $this->_cachePath . $this->_cacheName . $this->_cacheExtension;
    }
}