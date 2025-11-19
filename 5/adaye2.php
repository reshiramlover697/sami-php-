<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


try {
    $dbh = new PDO(
        'mysql:host=localhost;port=3306;dbname=sys;charset=utf8',
        'root',
        ''
    );
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}


$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("Identifiant invalide.");
}


$sth = $dbh->prepare("SELECT * FROM `100` WHERE id = :id");
$sth->execute([':id' => $id]);
$row = $sth->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die("Enregistrement introuvable.");
}


$sth = $dbh->prepare("SELECT DISTINCT course FROM `100` ORDER BY course ASC");
$sth->execute();
$coursesList = $sth->fetchAll(PDO::FETCH_COLUMN);


$errorMessage = "";
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $pays = strtoupper(trim($_POST['pays'] ?? ''));
    $course = trim($_POST['course'] ?? '');
    $temps = $_POST['temps'] ?? '';

 
    if ($nom === '') {
        $errorMessage = "Le nom est requis.";
    } elseif (!preg_match('/^[A-Z]{3}$/', $pays)) {
        $errorMessage = "Le pays doit être 3 lettres majuscules (ex : FRA).";
    } elseif ($course === '') {
        $errorMessage = "Le nom de la course est requis.";
    } elseif (!is_numeric($temps)) {
        $errorMessage = "Le temps doit être un nombre.";
    } else {
       
        $sth = $dbh->prepare("
            UPDATE `100`
            SET nom = :nom, pays = :pays, course = :course, temps = :temps
            WHERE id = :id
        ");

        $sth->execute([
            ':nom' => $nom,
            ':pays' => $pays,
            ':course' => $course,
            ':temps' => $temps,
            ':id' => $id
        ]);

        
        if ($sth->rowCount() >= 0) {
            
            $sth = $dbh->prepare("SELECT * FROM `100` WHERE id = :id");
            $sth->execute([':id' => $id]);
            $row = $sth->fetch(PDO::FETCH_ASSOC);

            
            header('Location: adaye.php?updated=1');

            exit;
        } else {
            $errorMessage = "Aucune modification effectuée.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Modifier le résultat #<?php echo htmlspecialchars($id); ?></title>
</head>
<body>
    <h1>Modifier le résultat #<?php echo htmlspecialchars($id); ?></h1>

    <?php if ($errorMessage): ?>
        <p style="color:red;"><?php echo htmlspecialchars($errorMessage); ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>
            Nom:<br>
            <input type="text" name="nom" required value="<?php echo htmlspecialchars($row['nom']); ?>">
        </label>
        <br><br>

        <label>
            Pays (3 lettres):<br>
            <input type="text" name="pays" maxlength="3" required value="<?php echo htmlspecialchars($row['pays']); ?>">
        </label>
        <br><br>

        <label>
            Course:<br>
            <select name="course" required>
                <?php

                if (!empty($coursesList)) {
                    foreach ($coursesList as $c) {
                        $sel = ($c === $row['course']) ? 'selected' : '';
                        echo '<option value="'.htmlspecialchars($c).'" '.$sel.'>'.htmlspecialchars($c).'</option>';
                    }
                    // si la course actuelle n'est pas dans la liste, l'ajouter en option
                    if (!in_array($row['course'], $coursesList, true) && $row['course'] !== '') {
                        echo '<option value="'.htmlspecialchars($row['course']).'" selected>'.htmlspecialchars($row['course']).'</option>';
                    }
                } else {
                    echo '<option value="'.htmlspecialchars($row['course']).'">'.htmlspecialchars($row['course']).'</option>';
                }
                ?>
            </select>
        </label>
        <br><br>

        <label>
            Temps:<br>
            <input type="number" step="0.01" name="temps" required value="<?php echo htmlspecialchars($row['temps']); ?>">
        </label>
        <br><br>

        <button type="submit">Enregistrer</button>
        <a href="adaye.php" style="margin-left:15px;">Annuler</a>
    </form>
</body>
</html>
