<?php

$function = getRequestParameter('function', 'accueil');

switch ($function) {
    
    case 'accueil':
        $vue = "accueil";
        $title = "Accueil";
        break;

    case 'liste':
        $vue = "liste";
        $title = "Liste des fichiers";
        $entete = "Voici la liste :";
        
        $liste = recupereTousFichiers();
        
        if(empty($liste)) {
            $alerte = "Aucun fichier pour le moment";
        }
        
        break;
        
    default:
        $vue = "erreur404";
        $title = "error404";
        $message = "Erreur 404 : la page recherchée n'existe pas.";
}

include ('vue/' . $vue . '.php');

?>