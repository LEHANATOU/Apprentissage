<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>selection une ville</title>
</head>
<body>

<?php
try {
    // Connexion à la base de données
    $conn = new PDO('mysql:host=localhost;dbname=coordonnees;charset=utf8', 'root', '');
} catch (PDOException $e) {
    // Gestion des erreurs
    die('Erreur : ' . $e->getMessage());
    exit;
}

// Récupérer les villes depuis la base de données
$sql = "SELECT * FROM villes";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
  <form method="post" action="METEO.php">
    <label for="city">Choisissez une ville :</label>
    <select name="city" id="city">
        <option value="" disabled>Sélectionnez une ville</option>
        <?php foreach ($result as $row) { ?>
            <option value="<?php echo $row["id"]; ?>"><?php echo $row["nom"]; ?></option>
        <?php } ?>
    </select>
    <input type="submit" name="submit" value="Afficher la météo">
</form>
</body>
</html>