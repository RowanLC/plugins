<?php
try {
    $mysqlClient = new PDO ('mysql:host=localhost;dbname=formulaire;charset=utf8', 'root', '');
    } catch (PDOException $e) {
        die('Erreur : ' . $e->getMessage());
    }
?>