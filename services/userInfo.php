<?php

include("./servicesConfig.php");


if (!isset($_SERVER['PATH_INFO'])){
	$student_id = 0;
}else{
	$student_id = explode("/",  $_SERVER['PATH_INFO'])[1];
}

$sql = "SELECT * FROM v_users_login WHERE student_id=?";

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i",$student_id);
    if ($stmt->execute()) {
        
        if (!($res = $stmt->get_result())) {
            echo "Getting result set failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        if ($res->num_rows == 0) {
            $data = [ 'result' => 1, 'data' => ['$student_id'=>$student_id], 'numResults' => 0];
        } else {
            $res->data_seek(0);//goto first row
            $data = [ 'result' => 1, 'data' => [ 'userinfo'=>$res->fetch_assoc()], 'numResults' => 1];
        }
        
    } else {
        $data = [ 'result' => 0,  'numResults' => 0, 'error' => "mysqli execute failed: (" . $mysqli->errno . ") " . $mysqli->error];
    }
} else {
    $data = [ 'result' => 0,  'numResults' => 0, 'error' => "mysqli Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error];
}

header('Content-Type: application/json');
echo json_encode( $data );

?>
