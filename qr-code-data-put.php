<?php
// Include the database configuration file
include("config.php");

// Set header to return JSON content
header('Content-Type: application/json');

// Get the full URI
$uri = $_SERVER['REQUEST_URI'];

// Split the URI into segments
$segments = explode('/', $uri);

// Check if segments array has at least one item
if(count($segments) > 0) {
    // Assuming the data follows 'qrcodedata.php' in the URL
    $dataIndex = array_search('qr-code-data-put.php', $segments) + 1;
    if(isset($segments[$dataIndex])) {
        // Trim the path data to remove any leading/trailing spaces
        $iic_qrcode_data = trim($segments[$dataIndex]);

        // Check if the data is not empty
        if(!empty($iic_qrcode_data)) {
            // Sanitize the input to prevent SQL Injection
            $iic_qrcode_datad = urldecode($iic_qrcode_data);
            $iic_qrcode_datas = str_replace('\"', '"', $iic_qrcode_datad); //stripslashes(stripslashes($iic_qrcode_datad));
            $iic_qrcode_data_es = mysqli_real_escape_string($connection, $iic_qrcode_datas);

            // SQL to insert data
            $sql = "INSERT INTO iic_qrcode (iic_qrcode_data, create_date) VALUES (?, NOW())";

            // Prepare the statement
            if($stmt = mysqli_prepare($connection, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $iic_qrcode_data_es);
                if(mysqli_stmt_execute($stmt)) {
                    echo json_encode(["result" => "SUCCESS", "iic_qrcode_data_es"=>$iic_qrcode_data_es , "iic_qrcode_datas"=>$iic_qrcode_datas ,"iic_qrcode_datad"=>$iic_qrcode_datad]);
                } else {
                    echo json_encode(["result" => "ERROR", "reason" => "Could not execute query: $sql. " . mysqli_error($connection)]);
                }
                mysqli_stmt_close($stmt);
            } else {
                echo json_encode(["result" => "ERROR", "reason" => "Could not prepare query: $sql. " . mysqli_error($connection)]);
            }
        } else {
            echo json_encode(["result" => "ERROR", "reason" => "Empty data provided in the URL."]);
        }
    } else {
        echo json_encode(["result" => "ERROR", "reason" => "No data provided in the URL."]);
    }
} else {
    echo json_encode(["result" => "ERROR", "reason" => "Invalid URL: " . $uri]);
}

// Close connection
mysqli_close($connection);
?>
