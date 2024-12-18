<?php

// on récupère les requêtes génériques
include('requetes.generiques.php');

//on définit le nom de la table
$table = "users";

/**
 * Recherche un utilisateur en fonction du nom passé en paramètre
 * @param PDO $db
 * @param string $mail
 * @return array
 */
function rechercheParMail(PDO $db, string $mail): array {
    $statement = $db->prepare('SELECT * FROM  users WHERE mail = :mail');
    $statement->bindParam(":mail", $mail);
    $statement->execute();
    return $statement->fetchAll();
}

/**
 * Récupère tous les enregistrements de la table users
 * @param PDO $db
 * @return array
 */
function recupereTousUtilisateurs(PDO $db): array {
    $query = 'SELECT * FROM users';
    return $db->query($query)->fetchAll();
}

/**
 * Ajoute un nouvel utilisateur dans la base de données
 * @param PDO $db
 * @param array $utilisateur
 * @return bool
 */
function ajouteUtilisateur(PDO $db, array $utilisateur): bool {
    $query = 'INSERT INTO `User` (Nom, Prenom, Mail, Mot_de_passe, Classe) 
              VALUES (:nom, :prenom, :mail, :mot_de_passe, :classe)';
    $statement = $db->prepare($query);
    
    $statement->bindParam(":nom", $utilisateur['nom'], PDO::PARAM_STR);
    $statement->bindParam(":prenom", $utilisateur['prenom'], PDO::PARAM_STR);
    $statement->bindParam(":mail", $utilisateur['mail'], PDO::PARAM_STR);
    $statement->bindParam(":mot_de_passe", $utilisateur['mot_de_passe'], PDO::PARAM_STR);
    $statement->bindParam(":classe", $utilisateur['classe'], PDO::PARAM_STR);
    
    return $statement->execute();
}

/**
 * Recherche les utilisateurs par prénom et nom
 * @param PDO $db
 * @param string $search_input
 * @return array
 */
function rechercheUtilisateur(PDO $db, string $search_input): array {
    $query = 'SELECT idUser, Prenom, Nom, Photo_de_Profil
              FROM User
              WHERE CONCAT(Prenom, ' ', Nom) Like :search_request';
    $statement = $db->prepare($query);
    $search_request = '%' . $search_input . '%';
    $statement->bindParam(":search_request", $search_request, PDO::PARAM_STR);
    $statement->execute();
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

?>