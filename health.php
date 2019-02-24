<?php

$servername = getenv(strtoupper(getenv("DATABASE_SERVICE_NAME"))."_SERVICE_HOST");
$dbname = getenv("DATABASE_NAME");
$username = getenv("DATABASE_USER");
$password = getenv("DATABASE_PASSWORD");

// // Create connection
$conn_string = "host=".$servername." dbname=".$dbname." user=".$username." password=".$password."";
$connection = pg_connect($conn_string);

// Check connection
if($connection) {   
  	echo "OK \n";
  	echo $conn_string;
}else{
	header("HTTP/1.1 503 Service Unavailable");
  	die("Connection failed: " . $connection);
}
?>
