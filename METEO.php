<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>la meteo du jour</title>
</head>
<body>
<?php
    try {
      // Connexion à la base de données
      $conn = new PDO('mysql:host=localhost;dbname=coordonnees;charset=utf8', 'root', '');
    } catch (PDOException $e) {
      // Gestion des erreurs
      echo '<p class="error">Erreur : ' . $e->getMessage() . '</p>';
      exit;
    }

    // Récupérer les villes depuis la base de données
    $sql = "SELECT * FROM villes";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les informations météorologiques
    if (isset($_POST['city']) && !empty($_POST['city'])) {
        $cityId = $_POST['city'];
        $sql = "SELECT * FROM villes WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $cityId, PDO::PARAM_INT);
        $stmt->execute();
        $city = $stmt->fetch(PDO::FETCH_ASSOC);

        $apiUrl = "https://api.tutiempo.net/json/?lan=fr&apid=zwDX4azaz4X4Xqs&ll=" . $city['latitude'] . "," . $city['longitude'];
        $apiResponse = file_get_contents($apiUrl);

        if ($apiResponse !== false) {
            $weatherData = json_decode($apiResponse, true);

            // Récupérer les informations nécessaires
            $date = $weatherData['day1']['date'];
            $temperature_max = $weatherData['day1']['temperature_max'];
            $temperature_min = $weatherData['day1']['temperature_min'];

            // Récupérer l'heure de consultation
            $consultationTime = date('H:i:s');

            // Afficher les données météorologiques
            echo "Données Météorologiques" .'<br>';
            echo 'Date : ' . $date . '<br>';
            echo 'Température maximale : ' . $temperature_max . '°C<br>';
            echo 'Température minimale : ' . $temperature_min . '°C<br>';
            echo "Heure de consultation : " . $consultationTime;

            // Enregistrer les données dans la base de données
            if (isset($_POST['save'])) {
                $saveCity = $_POST["save"];

                // Récupérer la date d'aujourd'hui
                $currentDate = date('Y-m-d');

                // Vérifier si les données pour la date d'aujourd'hui existent déjà dans la base de données
                $sql = "SELECT COUNT(*) FROM historique WHERE ville_id = :ville_id AND date = :date";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':ville_id', $saveCity);
                $stmt->bindValue(':date', $currentDate);
                $stmt->execute();
                $count = $stmt->fetchColumn();

                if ($count == 0) {
                    // Insérer les données dans la table "historique"
                    $sql = "INSERT INTO historique (ville_id, date, temperature_max, temperature_min, heure) VALUES (:ville_id, :date, :temperature_max, :temperature_min, :heure)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindValue(':ville_id', $saveCity);
                    $stmt->bindValue(':date', $currentDate);

                    // Vérifier si $consultationTime est défini avant de l'insérer
                    if (isset($consultationTime) && !empty($consultationTime)) {
                        $stmt->bindValue(':heure', $consultationTime);
                    } else {
                        $stmt->bindValue(':heure', null);
                    }

                    $stmt->bindValue(':temperature_max', $temperature_max);
                    $stmt->bindValue(':temperature_min', $temperature_min);

                    try {
                        if ($stmt->execute()) {
                            echo "Les données d'aujourd'hui ont été enregistrées dans la base de données.";
                        } else {
                            echo "Une erreur s'est produite lors de l'enregistrement des données.";
                        }
                    } catch (PDOException $e) {
                        echo "Une erreur s'est produite lors de l'enregistrement des données : " . $e->getMessage();
                    }
                } else {
                    echo "Les données pour cette ville et la date d'aujourd'hui existent déjà dans la base de données.";
                }
            }
        } else {
            echo 'La requête a échoué.';
        }
    }
?>

<form method="post" action="historique.php">
    <label for="save">Enregistrer les données :</label>
    <input type="submit" name="save" value="Enregistrer">
</form>
</body>
</html>