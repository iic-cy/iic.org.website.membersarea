<?php

include("../config.php");

$sql = "SELECT a.* FROM alert a where a.expiry_date >= CURDATE() order by alert_id desc";
$sql_result = mysql_query ($sql, $connection ) or die ('request "Could not execute SQL query" '.$sql);

$sql_resulkts_arr = array();

while(($row = mysql_fetch_assoc($sql_result))) {
    $sql_resulkts_arr[] = $row;
}

mysql_free_result($sql_result);

$data = [ 'result' => 1, 'data' => $sql_resulkts_arr, 'numResults' => count($sql_resulkts_arr)];
header('Content-Type: application/json');
echo json_encode( $data );

?>
