<?php
// Include the database configuration file
include("config.php");

// Set header to return JSON content
header('Content-Type: application/json');

// Prepare the SQL statement
$sql = "SELECT iic_qrcode_id, iic_qrcode_data,UNIX_TIMESTAMP(create_date) as create_date FROM iic_qrcode WHERE processed = 0";

// Execute the query and fetch the results
if($result = mysqli_query($connection, $sql)) {
    $data = array();
    while($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    // Free result set
    mysqli_free_result($result);

    // Encode data in JSON format and wrap it in a "results" key
    echo json_encode(['results' => $data]);
} else {
    echo json_encode(["results" => "ERROR", "reason" => "Query execution failed: " . mysqli_error($connection)]);
}

// Close connection
mysqli_close($connection);
?>
