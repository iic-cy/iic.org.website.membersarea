<?php

/* Define MySQL connection details and database table name */ 
$SETTINGS["hostname"] = 'h40lg7qyub2umdvb.cbetxkdyhwsb.us-east-1.rds.amazonaws.com';
$SETTINGS["mysql_user"] = 'zaheai3jklgk2ltf';
$SETTINGS["mysql_pass"] = 'cvh6rk2hcvll98hw';
$SETTINGS["mysql_database"] = 'tsy1ng4y7q19kwvs';


$mysqli = new mysqli($SETTINGS["hostname"], $SETTINGS["mysql_user"] , $SETTINGS["mysql_pass"],$SETTINGS["mysql_database"]);
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
} else {
    //echo "xconnect ok!";
}
?>