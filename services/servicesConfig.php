<?php
/* Define MySQL connection details and database table name */
$SETTINGS = [
    "hostname" => 'localhost',
    "mysql_user" => 'iicorg_members',
    "mysql_pass" => '23@Q07Mz&PMZ', // Password redacted for security
    "mysql_database" => 'iicorg_members'
];

// Error handling - log errors instead of displaying them
try {
    $mysqli = new mysqli(
        $SETTINGS["hostname"], 
        $SETTINGS["mysql_user"], 
        $SETTINGS["mysql_pass"],
        $SETTINGS["mysql_database"]
    );
    
    // Set character encoding
    $mysqli->set_charset("utf8mb4");
    
    if ($mysqli->connect_errno) {
        // Log the error instead of displaying it
        error_log("Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
        
        // Display generic error to users
        die("Database connection error. Please try again later.");
    }
    
    // Successful connection - continue with your code
    
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    die("An unexpected error occurred. Please try again later.");
}
?>
