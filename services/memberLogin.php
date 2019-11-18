<?php

include("./servicesConfig.php");

$loginsystem = 1;

if (!isset($_SERVER['PATH_INFO'])){
	$pathbits= array('');
}else{
	$pathbits = explode("/",  $_SERVER['PATH_INFO']);
}


$mobileNumber = $pathbits[1];
$idnumber     = $pathbits[2];
$loginsystem  = $pathbits[3];

$input = ['$mobileNumber'=>$mobileNumber, '$idnumber'=>$idnumber,'$loginsystem'=>$loginsystem, 'PATH_INFO'=>$_SERVER['PATH_INFO']];

$data = [ 'result' => 0, 'data' => ['$input'=>$input] ];

$sql = "SELECT * FROM v_users_login WHERE cleanPhoneNumber( mobile_phone) = cleanPhoneNumber('".mysqli_real_escape_string($mysqli,$mobileNumber)."') AND `id_number` = '".mysqli_real_escape_string($mysqli,$idnumber)."'";
$sql_result = mysqli_query($mysqli, $sql, $connection ) or die ('request "Could not execute SQL query" '.$sql);
$user = mysqli_fetch_assoc($sql_result);

if(!empty($user)){
	
	$query = " UPDATE `student_online` SET last_login = NOW() WHERE student_web_id=".$user['student_web_id'];
	$query2 = "INSERT INTO `student_login` (login_date,login_system, student_id)VALUES(CURRENT_TIMESTAMP,".$loginsystem.",".$user['student_id'].")";
	
	if (mysqli_query ($mysqli, $query, $connection )) {
	    
	    if (mysqli_query ($mysqli, $query2, $connection )) {
		    $data['result'] = 1; // success
		    $data['data']['user'] = $user;
		    
		    
		} else {
		    $data['result'] = 0;
		    $data['error'] ='request failed, could not insert member login '.$query2;
		    
	    }
	
	} else {
	    $data['result'] = 0;
	    $data['error'] ='request failed, could not update member last login';
	    
	}
	
	
} else {
    $data['result'] = 0;
	$data['error'] = 'Λάθος Αρ. Τηλεφώνου η Αρ. Ταυτότητας.';
	
}

header('Content-Type: application/json');
echo json_encode( $data );

?>
