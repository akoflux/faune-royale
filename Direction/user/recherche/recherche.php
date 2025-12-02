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
    <style>

                
        body {
            font-family: 'Poppins', sans-serif;
        }

        h2 {
            color: white;
        }

        nav {
            width: 220px;
            height: 100vh;
            background-color: #2c3e50;
            padding-top: 20px;
            position: fixed;
            left: 0;
            top: 0;
        }

        nav hr {
            border: none;
            height: 1px;
            background-color: #ffffff33;
            margin: 10px auto;
            width: 80%;
        }

        nav ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        /* Style des éléments de la liste */
        nav ul li {
            text-align: center;
            margin-bottom: 40px;
            margin-left: 20px; /* Augmente l'espacement */
        }

        /* Style des liens sous forme de boutons */
        nav ul li a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 80%;
            padding: 14px 0; /* Augmente légèrement la hauteur */
            background-color: #34495e;
            color: white;
            text-decoration: none;
            font-size: 18px; /* Augmente la taille du texte */
            font-weight: 600; /* Rend le texte plus visible */
            border-radius: 5px;
            transition: background 0.3s, transform 0.2s;
        }

        /* Changement de couleur au survol */
        nav ul li a:hover {
            background-color: #1abc9c;
            transform: scale(1.05);
        }

        /* Correction des icônes */
        nav ul li a i {
            margin-right: 10px;
            font-size: 20px; /* Augmente légèrement la taille des icônes */
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            text-align: center;
        }
        input {
            width: 30%;
            padding: 10px;
            font-size: 16px;
            margin: 20px 0;
            margin-left: 150px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        table {
            width: 70%;
            margin: auto;
            margin-right: 140px;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
        }
        th {
            background: #34495e;
            color: white;
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }
    </style>
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
