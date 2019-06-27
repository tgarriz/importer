<?php
function getdb(){
	$db=pg_connect('host=192.168.122.30 user=postgres password=postgres dbname=asientos connect_timeout=5') or die('connection failed');
	$result = pg_query($db, 'truncate asiento01');
        if(!isset($result)) {
            echo 'error truncando tabla asiento';    
        } else {
	    return $db;
	}
}
?>
