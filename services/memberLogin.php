<?php

/**
 * Finds a user by mobile phone number and ID number
 * 
 * @param mysqli $mysqli The database connection object
 * @param string $mobileNumber The user's mobile phone number
 * @param string $idNumber The user's ID number
 * @return array|null Returns the user record as an associative array or null if not found
 * @throws Exception If a database error occurs
 */
function findUserByPhoneAndId($mysqli, $mobileNumber, $idNumber) {
    $user = null;
    $stmt = null;
    
    try {
        
        error_log("findUserByPhoneAndId,request? **" . $mobileNumber."**, **".$idNumber."**");
        
        // Prepare the statement
        $stmt = $mysqli->prepare("SELECT * FROM v_users_login WHERE cleanPhoneNumber(mobile_phone) = cleanPhoneNumber(?) AND id_number = ?");
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $mysqli->error);
        }
        
        // Bind parameters
        $stmt->bind_param("ss", $mobileNumber, $idNumber);
        
        // Execute
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        
        // Get result
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        error_log("findUserByPhoneAndId,found user? **" . $user."**");
        
        return $user;
        
    } catch (Exception $e) {
        // Log the error
        error_log("Database error in findUserByPhoneAndId: " . $e->getMessage());
        throw $e; // Re-throw the exception for the caller to handle
    } finally {
        // Close statement in the finally block to ensure it's always closed
        if ($stmt !== null) {
            $stmt->close();
        }
    }
}

/**
 * Records a student login by updating last login time and adding a login record
 * 
 * @param mysqli $mysqli The database connection object
 * @param array $user User data array containing student_web_id and student_id
 * @param int $loginSystem The login system identifier
 * @return array Response array with result status and data/error message
 */
function recordStudentLogin($mysqli, $user, $loginSystem) {
    $data = ['result' => 0, 'data' => [], 'error' => ''];
    $stmt1 = null;
    $stmt2 = null;
    
    // Validate required user data
    if (!isset($user['student_web_id']) || !isset($user['student_id'])) {
        $data['error'] = 'Invalid user data provided';
        return $data;
    }
    
    // Start a transaction for atomicity (both updates succeed or both fail)
    $mysqli->begin_transaction();
    
    try {
        // Prepare first statement - update last login
        $stmt1 = $mysqli->prepare("UPDATE `student_online` SET last_login = NOW() WHERE student_web_id = ?");
        if (!$stmt1) {
            throw new Exception("Prepare failed for update: " . $mysqli->error);
        }
        
        // Bind parameters
        $stmt1->bind_param("i", $user['student_web_id']); // 'i' for integer
        
        // Execute the statement
        if (!$stmt1->execute()) {
            throw new Exception("Execute failed for update: " . $stmt1->error);
        }
        
        // Check if the update affected any rows
        if ($stmt1->affected_rows == 0) {
            // Optional: You might want to know if no rows were updated
            error_log("No rows updated for student_web_id: " . $user['student_web_id']);
        }
        
        // Prepare second statement - insert login record
        $stmt2 = $mysqli->prepare("INSERT INTO `student_login` (login_date, login_system, student_id) VALUES (CURRENT_TIMESTAMP, ?, ?)");
        if (!$stmt2) {
            throw new Exception("Prepare failed for insert: " . $mysqli->error);
        }
        
        // Bind parameters
        $stmt2->bind_param("ii", $loginSystem, $user['student_id']); // 'ii' for two integers
        
        // Execute the statement
        if (!$stmt2->execute()) {
            throw new Exception("Execute failed for insert: " . $stmt2->error);
        }
        
        // Get the ID of the inserted login record
        $loginId = $mysqli->insert_id;
        
        // If we got here, everything succeeded, so commit the transaction
        $mysqli->commit();
        error_log("all good, setting data $user" . $user);
       
        
    } catch (Exception $e) {
        // An error occurred, roll back the transaction
        $mysqli->rollback();
        
        // Set error response
        $data['result'] = 0;
        $data['error'] = 'Request failed: ' . $e->getMessage();
        
        // Log the error for debugging
        error_log("Database error in recordStudentLogin: " . $e->getMessage());
    } finally {
        // Close statements in the finally block to ensure they're always closed
        if ($stmt1 !== null) {
            $stmt1->close();
        }
        if ($stmt2 !== null) {
            $stmt2->close();
        }
    }
    
    return $data;
}
    
include("servicesConfig.php");

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
    
$user = findUserByPhoneAndId($mysqli, $mobileNumber, $idnumber);

if (!empty($user)) {
    $result = recordStudentLogin($mysqli, $user, $loginsystem);
    
    // all good, Set success response
    $data['result'] = 1; // success
    $data['data']['user'] = $user;
    $data['data']['login_id'] = $loginId;
    
} else {
    $data['result'] = 0;
    $data['error'] = 'Λάθος Αρ. Τηλεφώνου η Αρ. Ταυτότητας.';
}

header('Content-Type: application/json');
echo json_encode( $data );
    
?>
