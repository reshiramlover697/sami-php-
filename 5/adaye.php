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
    die("Erreur : " . $e->getMessage());
}


$sth = $dbh->prepare("SELECT DISTINCT course FROM `100` ORDER BY course ASC");
$sth->execute();
$coursesList = $sth->fetchAll(PDO::FETCH_COLUMN);


$errorMessage = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $pays = strtoupper(trim($_POST['pays']));
    $course = $_POST['course'];
    $temps = $_POST['temps'];

    
    if (!preg_match('/^[A-Z]{3}$/', $pays)) {
        $errorMessage = "Le pays doit être 3 lettres majuscules (ex : FRA, USA).";
    }
   
    else if (!is_numeric($temps)) {
        $errorMessage = "Le temps doit être un nombre.";
    } else {
    
        $sth = $dbh->prepare("
            INSERT INTO `100` (nom, pays, course, temps)
            VALUES (:nom, :pays, :course, :temps)
        ");

        $sth->execute([
            ':nom' => $nom,
            ':pays' => $pays,
            ':course' => $course,
            ':temps' => $temps
        ]);

        $success = true;
    }
}


$validColumns = ['nom', 'pays', 'course', 'temps'];
$validOrders  = ['ASC', 'DESC'];

$sortColumn = $_GET['sort']  ?? 'nom';
$sortOrder  = $_GET['order'] ?? 'ASC';

if (!in_array($sortColumn, $validColumns)) $sortColumn = 'nom';
if (!in_array($sortOrder, $validOrders))  $sortOrder = 'ASC';

$search = $_GET['search'] ?? "";


$resultsPerPage = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $resultsPerPage;

$sql = "
    SELECT * 
    FROM `100`
    WHERE nom LIKE :search
    ORDER BY $sortColumn $sortOrder
    LIMIT :offset, :limit
";

$sth = $dbh->prepare($sql);
$sth->bindValue(':search', "%$search%", PDO::PARAM_STR);
$sth->bindValue(':offset', $offset, PDO::PARAM_INT);
$sth->bindValue(':limit', $resultsPerPage, PDO::PARAM_INT);
$sth->execute();
$data = $sth->fetchAll(PDO::FETCH_ASSOC);


foreach ($data as &$row) {
    $sth = $dbh->prepare("
        SELECT COUNT(*) + 1 AS rang
        FROM `100`
        WHERE course = :course
          AND temps < :temps
    ");
    $sth->execute([
        ':course' => $row['course'],
        ':temps'  => $row['temps']
    ]);
    $row['rang'] = $sth->fetchColumn();
}

?>
<!DOCTYPE html>
<html>
<head>
<title>Résultats</title>
</head>
<body>

<h2>Ajouter un résultat</h2>

<?php if ($errorMessage): ?>
    <p style="color:red;"><?php echo $errorMessage; ?></p>
<?php endif; ?>
<?php if ($success): ?>
    <p style="color:green;">Résultat ajouté avec succès !</p>
<?php endif; ?>

<form method="POST">
    Nom : <input type="text" name="nom" required><br><br>

    Pays (3 lettres) :
    <input type="text" name="pays" maxlength="3" required><br><br>

    Course :
    <select name="course" required>
        <?php foreach ($coursesList as $c): ?>
            <option value="<?php echo $c; ?>"><?php echo $c; ?></option>
        <?php endforeach; ?>
    </select><br><br>

    Temps (nombre) :
    <input type="number" step="0.01" name="temps" required><br><br>

    <button type="submit">Ajouter</button>
</form>

<hr>

<h2>Recherche</h2>
<form method="GET">
    <input type="text" name="search" placeholder="Nom..." value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit">Rechercher</button>
</form>

<hr>

<table border="1" cellpadding="8">
    <thead>
        <tr>
            <th>Classement</th>
            <th><a href="?sort=nom&order=<?php echo ($sortOrder === 'ASC') ? 'DESC' : 'ASC'; ?>">Nom</a></th>
            <th>Pays</th>
            <th>Course</th>
            <th>Temps</th>
            <td><a href="adaye2.php?id=<?php echo $row['id']; ?>">Modifier</a></td>

        </tr>
    </thead>
    <tbody>
    <?php foreach ($data as $row): ?>
        <tr>
            <td><?php echo $row['rang']; ?></td>
            <td><?php echo $row['nom']; ?></td>
            <td><?php echo $row['pays']; ?></td>
            <td><?php echo $row['course']; ?></td>
            <td><?php echo $row['temps']; ?>s</td>
            <td><a href="edit.php?id=<?php echo $row['id']; ?>">Modifier</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
