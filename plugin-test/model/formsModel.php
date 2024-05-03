<?php

require('../model/db_connect');

class FormsModel {
    private $db;
    public function __construct($db){
        $this->db = $db;
    }

    // Méthode pour insérer les données du formulaire dans la base de données
    public function insertFormData($nom, $prenom, $email, $message) {
        try {
            // Préparation de la requête SQL
            $stmt = $this->db->prepare("INSERT INTO formulaire (nom, prenom, email, message) VALUES (:nom, :prenom, :email, :message)");

            // Liaison des paramètres
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':message', $message);

            // Exécution de la requête
            $stmt->execute();

            // Retourne l'ID du nuvel enregistrement
            return $this->db->lastInsertId();
        } catch(PDOException $e) {
            // Gestion des erreurs
            die('Erreur : ' . $e->getmessage());
        }
    }

    // Méthode pour récupérer tous les enregistrements du formulaire depuis la base de données
    public function getAllFormData() {
        try {
            // Préparation de la requête SQL
            $stmt = $this->db->prepare("SELECT * FROM formulaire");

            // Exécution de la requête
            $stmt-> execute();

            // Récupération des résultas
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Gestion des erreurs
            die('Erreur : ' . $e->getMessage());
        }
    }
}