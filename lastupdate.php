<?php
// Get current time in milliseconds
$timestamp = round(microtime(true) * 1000);

// Define the filename
$filename = 'lastupdate.json';

// Check if the file exists
if (!file_exists($filename)) {
    // If not, create the file and write the current timestamp
    file_put_contents($filename, json_encode($timestamp));
}
?>