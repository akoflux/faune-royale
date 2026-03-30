<?php
// Connexion à la base de données
@include("../../../connexion.php");

session_start();
$prenom = $_SESSION['prenom'];
$role = $_SESSION['role'];

// Récupération des données si une recherche est effectuée
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT * FROM users WHERE 
        role LIKE '%$search%' OR 
        prenom LIKE '%$search%' OR 
        email LIKE '%$search%'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche Espèces</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
</head>
<body>

<nav>

<center><h2> <?php echo ($role); echo" "; echo ($prenom) ?></h2></center>
        <hr>
        <ul>
            <li><a href="../Dashboard/dashboard.php"><i class="fa-solid fa-server"></i> Dashboard</a></li>
            <li><a href="../Recherche/recherche.php"><i class="fa-solid fa-magnifying-glass"></i> Rechercher</a></li>
            <li><a href="../../../Direction/main.php"><i class="fa-solid fa-home"></i> Accueil</a></li>
        </ul>
    </nav>

<h1>Rechercher un user</h1>
<center><input type="text" id="search" placeholder="Tapez un prenom, role, email..." onkeyup="searchData()">
    </center>
<table>
    <tr>
        <th>ID</th>
        <th>Role</th>
        <th>Prenom</th>
        <th>Email</th>
        <th>Action</th>
    </tr>
    <tbody id="table-body">
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row["id"]; ?></td>
            <td><?php echo $row["role"]; ?></td>
            <td><?php echo $row["prenom"]; ?></td>
            <td><?php echo $row["email"]; ?></td>
            <td>
                <form method="POST" action="supprimer.php">
                    <input type="hidden" name="id" value="<?php echo $enreg['id']; ?>">
                    <button type="submit" class="delete-btn">Supprimer</button>
                </form>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<script>
    function searchData() {
        let search = document.getElementById("search").value;
        let xhr = new XMLHttpRequest();
        xhr.open("GET", "recherche.php?search=" + search, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                let parser = new DOMParser();
                let doc = parser.parseFromString(xhr.responseText, 'text/html');
                document.getElementById("table-body").innerHTML = doc.getElementById("table-body").innerHTML;
            }
        };
        xhr.send();
    }
</script>

</body>
</html>

<?php
$conn->close();
?>
