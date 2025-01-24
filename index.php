<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);


session_start();

include('controleur/functions.php');
include('vue/functions.php');

// Dynamically generate the list of valid controllers from the 'controleur' directory
$valid_controllers = array_filter(scandir('controleur'), function($file) {
    // Only include PHP files (excluding . and ..)
    return strpos($file, '.php') !== false && $file != 'functions.php';
});

// Remove the .php extension from each file name
$valid_controllers = array_map(function($file) {
    return basename($file, '.php');
}, $valid_controllers);

// Sanitize input and prevent directory traversal
if (isset($_GET['cible']) && !empty($_GET['cible'])) {
    $url = basename($_GET['cible']);  // Strip any directory path elements
    if (!in_array($url, $valid_controllers)) {
        $url = 'utilisateurs';  // Default fallback if the controller is not valid
    }
} else {
    $url = 'utilisateurs';  // Default value if 'cible' is not set
}

// Verify the file exists before including
$controller_file = 'controleur/' . $url . '.php';
if (file_exists($controller_file)) {
    include($controller_file);
} else {
    // Handle the case where the file doesn't exist, perhaps redirect or show a 404 page
    header("HTTP/1.0 404 Not Found");
    echo "Page not found!";
}
?>