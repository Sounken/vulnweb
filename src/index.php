<?php
$host = getenv('DB_HOST');
$db = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Erreur BDD : " . $e->getMessage());
    die("Service temporairement indisponible.");
}

$search_raw = $_GET['search'] ?? '';
$search = htmlspecialchars($search_raw, ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Annuaire Interne</title>
    <style>body { font-family: sans-serif; padding: 20px; }</style>
</head>
<body>
    <h1>Annuaire de l'entreprise</h1>

    <p>Résultats de recherche pour : <b><?php echo $search; ?></b></p>

    <form method="GET">
        <input type="text" name="search" placeholder="Rechercher un collègue..." value="<?php echo $search; ?>">
        <button type="submit">Rechercher</button>
    </form>
    <hr>

    <?php
    if ($search) {
        $sql = "SELECT username, role, password FROM users WHERE username = :username";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['username' => $search]);

        try {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($results) {
                echo "<ul>";
                foreach ($results as $row) {
                    echo "<li>";
                    echo "<strong>" . htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') . "</strong> ";
                    echo "</li>";
                }
                echo "</ul>";
            } else {
                echo "Aucun utilisateur trouvé.";
            }
        } catch (PDOException $e) {
            error_log("Erreur SQL : " . $e->getMessage());
            die("Service temporairement indisponible.");
        }
    }
    ?>

    <hr>
    <div style="background-color: #f8d7da; padding: 10px; border: 1px solid #f5c6cb;">
        <h3>Zone Admin : Diagnostic Réseau</h3>
        <p>Vérifier la connectivité d'un serveur interne.</p>

        <form method="GET">
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">

            <label>IP à tester :</label>
            <input type="text" name="ip" placeholder="ex: 8.8.8.8" value="<?php echo htmlspecialchars($_GET['ip'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit">Pinger</button>
        </form>

        <?php
        if (isset($_GET['ip']) && !empty($_GET['ip'])) {
            $ip = $_GET['ip'];
            $clean_ip = escapeshellarg($ip);

            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                echo "<pre>";
                echo "Test de ping sur : " . htmlspecialchars($ip, ENT_QUOTES, 'UTF-8') . "\n";
                echo "-------------------------\n";

                system("ping -c 2 " . $clean_ip);

                echo "</pre>";
            } else {
                echo "<p style='color:red'>Format IP invalide (tentative d'intrusion détectée ?)</p>";
            }
        }
        ?>
    </div>
</body>
</html>
