<?php
$viewsFolder = 'vue/';

// Initialize variables
$vue = null;
$title = null;

// Get the list of all PHP files in the views folder
$availableViews = [];
foreach (scandir($viewsFolder) as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        // Strip the ".php" extension to get the view name
        $availableViews[] = pathinfo($file, PATHINFO_FILENAME);
    }
}

// Determine the function from the URL parameter
if (!isset($_GET['function']) || empty($_GET['function'])) {
    $function = "login"; // Default function
} else {
    $function = $_GET['function'];
}

// Check if the requested function corresponds to an available view
if (in_array($function, $availableViews)) {
    $vue = $function; // Assign the view dynamically
} else {
    $vue = "erreur404";
}

include ('vue/' . $vue . '.php');
?>
