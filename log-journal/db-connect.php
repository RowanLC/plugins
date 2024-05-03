<?php
try {
    $servername = 'localhost';
    $username = 'root';
    $password = '';
    $dbname = 'log-journal';

    $mysqlClient = new PDO ("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $resultat = $mysqlClient->query('SELECT * FROM log_journal_data');
    
    if ($resultat) {
        echo "Connexion à la base de données réussie !";
    } else {
        echo "Erreur lors de l'exécution de la requête.";
    }
} catch (PDOException $e) {
        die('Erreur : ' . $e->getMessage());
    }
?>