<?php
try {
    // Connexion à la base de données
    $conn = new PDO('mysql:host=localhost;dbname=coordonnees;charset=utf8', 'root', '');

    // Récupération des données de la table historique
    $sql = "SELECT ville_id, date, temperature_max, temperature_min, heure FROM historique";
    $result = $conn->query($sql);

    // Affichage des données dans un tableau HTML
    if ($result->rowCount() > 0) {
        echo "<table>";
        echo "<tr><th>IDENTIFIANT</th><th>Ville</th><th>Température</th><th>Date</th><th>heure</th></tr>";
        foreach ($result as $row) {
            // Récupération des données de chaque ligne
            $ville_id = $row['ville_id'];
            $date = $row['date'];
            $heure = $row['heure'];
            $temperature_max = $row['temperature_max'];
            $temperature_min = $row['temperature_min'];

            // Récupération du nom de la ville à partir de l'identifiant
            $sql_ville = "SELECT nom FROM villes WHERE id = :ville_id";
            $stmt_ville = $conn->prepare($sql_ville);
            $stmt_ville->bindParam(':ville_id', $ville_id);
            $stmt_ville->execute();
            $ville = $stmt_ville->fetchColumn();

            // Formatage de la température
            $temperature = $temperature_max . "°C / " . $temperature_min . "°C";

            // Affichage des données dans une ligne de tableau
            echo "<tr><td>" . $ville_id . "</td><td>" . $ville . "</td><td>" . $temperature . "</td><td>" . $date . "</td></tr>" . $heure . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "Aucune donnée trouvée.";
    }
} catch (PDOException $e) {
    // Gestion des erreurs
    die('Erreur : ' . $e->getMessage());
}
?>
