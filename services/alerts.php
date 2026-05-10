<?php
/**
 * PHP JSON API Service to fetch and transform JSON data from IIC Cyprus API
 * https://iic.org.cy/members-area/services/transformIICNews.php
 */

// Set JSON content type header
header('Content-Type: application/json');

// Function to fetch JSON data from URL
function fetchJsonData($url) {
    // Initialize cURL
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    // Execute request
    $response = curl_exec($ch);
    
    // Check for errors
    if (curl_error($ch)) {
        throw new Exception('cURL Error: ' . curl_error($ch));
    }
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception('HTTP Error: ' . $httpCode);
    }
    
    return $response;
}

// Function to transform the JSON data
function transformJsonData($jsonData) {
    $data = json_decode($jsonData, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON Decode Error: ' . json_last_error_msg());
    }
    
    if (!is_array($data)) {
        throw new Exception('Expected JSON array but got: ' . gettype($data));
    }
    
    $transformedData = [];
    
    foreach ($data as $result) {
         if (isset($result['excerpt']['rendered'])) {
            $alertExcerpt = html_entity_decode($result['excerpt']['rendered'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $alertExcerpt = strip_tags($alertExcerpt); // Remove all HTML tags including <p>
            $alertExcerpt = str_replace('Διαβάστε Περισσότερα', '', $alertExcerpt);
            $alertExcerpt = trim($alertExcerpt); // Remove any trailing whitespace
        }
        
        // Process alert_date and determine if it has passed
        $alertDate = $result['date'] ?? null;
        $alertPassed = false;
        if ($alertDate) {
            try {
                $dateTime = new DateTime($alertDate);
                $currentTime = new DateTime();
                $alertPassed = $dateTime < $currentTime;
            } catch (Exception $e) {
                // If date parsing fails, default to false
                $alertPassed = false;
            }
        }
        
        $transformedItem = [
            'alert_id' => $result['id'] ?? null,
            'alert_image_url' => $result['yoast_head_json']['og_image'][0]['url'] ?? null,
            'alert_title' => isset($result['title']['rendered']) ? html_entity_decode($result['title']['rendered'], ENT_QUOTES | ENT_HTML5, 'UTF-8') : null,
            'alert_excerpt' => $alertExcerpt,
            'alert_link_url' => $result['link'] ?? null,
            'alert_date' => $result['date'] ?? null,
            'alert_event_date' => $result['acf']['event_date'] ?? null,
            'alert_passed' => $alertPassed
        ];
        
        $transformedData[] = $transformedItem;
    }
    
    return $transformedData;
}

// Main execution
try {
    // Get per_page parameter from URL, default to 25
    $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 25;
    
    // Validate per_page parameter (reasonable limits)
    if ($perPage < 1) {
        $perPage = 1;
    } elseif ($perPage > 100) {
        $perPage = 100; // Limit to prevent excessive requests
    }
    
    // API URL with dynamic per_page parameter
    $apiUrl = 'https://iic.org.cy/wp-json/wp/v2/posts?per_page=' . $perPage;
    
    // Fetch JSON data
    $jsonResponse = fetchJsonData($apiUrl);
    
    // Transform the data
    $transformedData = transformJsonData($jsonResponse);
    
    // Create response structure with data and numResults
    $response = [
        'data' => $transformedData,
        'numResults' => count($transformedData)
    ];
    
    // Output JSON response
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Return error as JSON
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}

?>
