<?php
function getdb(){
	$db=pg_connect('host=127.0.0.1 user=postgresql password=potgres dbname=asientos connect_timeout=5') or die('connection failed');
	return $db;
?>
