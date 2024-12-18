<?php

// on récupère les requêtes génériques
include('requetes.generiques.php');

//on définit le nom de la table
$table = "fichiers";

// Function to get all files (including PHP/HTML) in the current directory
function recupereTousFichiers(): array {
    $directory = __DIR__.'/../';
    $fichiers = scandir($directory);
    
    // Filter out '.' and '..' to avoid showing these as links
    $fichiers = array_diff($fichiers, ['.', '..']);
    
    // Optional: Filter only PHP and HTML files
    $fichiers = array_filter($fichiers, function ($file) {
        return (pathinfo($file, PATHINFO_EXTENSION) === 'php' || pathinfo($file, PATHINFO_EXTENSION) === 'html');
    });
    
    // Convert the result to an array and return it
    return array_values($fichiers);
}

?>