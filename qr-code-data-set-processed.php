<?php
// Include the database configuration file
include("config.php");

// Set header to return JSON content
header('Content-Type: application/json');

// Get the full URI
$uri = $_SERVER['REQUEST_URI'];

// Split the URI into segments
$segments = explode('/', $uri);

// Check if segments array has at least two items for the two parameters
if(count($segments) >= 3) {
    // Extract parameters from the URL
    $iic_qrcode_data_id = $segments[count($segments) - 2];
    $processed_flag = $segments[count($segments) - 1];

    // Validate that both parameters are numbers and processed_flag is either 1 or 2
    if(is_numeric($iic_qrcode_data_id) && ($processed_flag == 1 || $processed_flag == 2)) {
        // Convert parameters to integers
        $iic_qrcode_data_id = intval($iic_qrcode_data_id);
        $processed_flag = intval($processed_flag);

        // Prepare the SQL statement
        $sql = "UPDATE iic_qrcode SET processed = ?, update_date = NOW() WHERE iic_qrcode_id = ?";

        // Prepare the statement
        if($stmt = mysqli_prepare($connection, $sql)) {
            // Bind parameters to the statement
            mysqli_stmt_bind_param($stmt, "ii", $processed_flag, $iic_qrcode_data_id);

            // Execute the statement
            if(mysqli_stmt_execute($stmt)) {
                echo json_encode(["result" => "SUCCESS"]);
            } else {
                echo json_encode(["result" => "ERROR", "reason" => "Could not execute query: " . mysqli_error($connection)]);
            }

            // Close statement
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(["result" => "ERROR", "reason" => "Could not prepare query: " . mysqli_error($connection)]);
        }
    } else {
        echo json_encode(["result" => "ERROR", "reason" => "Invalid parameters. iic_qrcode_data_id must be a number and processed_flag must be 1 or 2."]);
    }
} else {
    echo json_encode(["result" => "ERROR", "reason" => "Invalid URL structure. Two parameters expected."]);
}

// Close connection
mysqli_close($connection);
?>
