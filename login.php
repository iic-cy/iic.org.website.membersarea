<?php

session_name('membersArea');
@session_start();

include("config.php");

$data = [ 'result' => 0, 'data' => '' ];
$loginsystem = 1;

if (isset($_REQUEST['loginsystem'])) {
    $loginsystem = $_REQUEST['loginsystem'];
}

$sql = "SELECT * FROM v_users_login WHERE cleanPhoneNumber( mobile_phone) = cleanPhoneNumber('".mysql_real_escape_string($_REQUEST['mobileNumber'])."') AND `id_number` = '".mysql_real_escape_string($_REQUEST['idnumber'])."'";
$sql_result = mysql_query ($sql, $connection ) or die ('request "Could not execute SQL query" '.$sql);
$user = mysql_fetch_assoc($sql_result);

if(!empty($user)){
	
	$_SESSION['user_info'] = $user; // set user in session
	
	$query = " UPDATE `student_online` SET last_login = NOW() WHERE student_web_id=".$user['student_web_id'];
	$query2 = "INSERT INTO `student_login` (login_date,login_system, student_id)VALUES(CURRENT_TIMESTAMP,".$loginsystem.",".$user['student_id'].")";
	
	if (mysql_query ($query, $connection )) {
	    
	    if (mysql_query ($query2, $connection )) {
		    $data['result'] = 1; // success
		    $data['data'] = $user;
		    $data['error'] = '';
		} else {
		    $data['result'] = 0;
		    $data['error'] ='request failed, could not insert member login '.$query2;
		    $data['data'] = '';
	    }
	
	} else {
	    $data['result'] = 0;
	    $data['error'] ='request failed, could not update member last login';
	    $data['data'] = '';
	}
	
	
} else {
    $data['result'] = 0;
	$data['error'] = 'Λάθος Αρ. Τηλεφώνου η Αρ. Ταυτότητας.';
	$data['data'] = '';
}

header('Content-Type: application/json');
echo json_encode( $data );

?>
