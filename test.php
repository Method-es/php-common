<?php

require_once('vendor/autoload.php');

//this file's sole purpose is to test all things within; comment or uncomment that which you don't want to test...

use Method\Common\Cache\Cache;


function TestCache(){

    // to test the cache we should do a few things
    // 1. a. Loading the cache that we know doesn't exist
    //    b. Loading the cache that we know does exist
    //    
    // 2. a. Access data we know is not there
    //    b. Access data we know is there
    // 
    // 3. Check the remove works
    // 4. Check the clear works

    $cacheOne = new Cache("cacheOne");
    $cacheTwo = new Cache("cacheTwo");

    $cacheOneFile = $cacheOne->GetCacheLocation();
    $cacheTwoFile = $cacheTwo->GetCacheLocation();
    if(file_exists($cacheTwoFile)){
        unlink($cacheTwoFile);
        // cache two is now gone.
    }
    
    // force the loading
    $cacheOne->Load();
    $cacheTwo->Load();

    echo "Caches Loaded:\n<br />";
    echo "<pre>\n";
    var_dump($cacheOne->IsLoaded(),$cacheTwo->IsLoaded());
    echo "</pre>\n";

    //access data
    $dataOneOne = $cacheOne->Get('data_one');
    $dataOneTwo = $cacheOne->Get('data_two');
    $dataTwoOne = $cacheTwo->Get('data_one');
    $dataTwoTwo = $cacheTwo->Get('data_two');

    echo "Data Retrieved:\n<br />";
    echo "<pre>\n";
    var_dump($dataOneOne,$dataOneTwo,$dataTwoOne,$dataTwoTwo);
    echo "</pre>\n";

    //now try storing the data
    
    $cacheOne->Store('data_one',1.1);
    $cacheOne->Store('data_two',1.2);
    $cacheTwo->Store('data_one',2.1);
    $cacheTwo->Store('data_two',2.2);
    echo "Data Stored.\n<br />\n<br />";

    echo "Caches Loaded:\n<br />";
    echo "<pre>\n";
    var_dump($cacheOne->IsCachable(),$cacheTwo->IsCachable());
    echo "</pre>\n";

    //now quickly retrieve what we stored and we can see if the cache worked by reloading
    
    //access data
    $dataOneOne = $cacheOne->Get('data_one');
    $dataOneTwo = $cacheOne->Get('data_two');
    $dataTwoOne = $cacheTwo->Get('data_one');
    $dataTwoTwo = $cacheTwo->Get('data_two');

    echo "Data Retrieved (again):\n<br />";
    echo "<pre>\n";
    var_dump($dataOneOne,$dataOneTwo,$dataTwoOne,$dataTwoTwo);
    echo "</pre>\n";


}



TestCache();

echo "<br/>\nTests Complete\n<br/>";
