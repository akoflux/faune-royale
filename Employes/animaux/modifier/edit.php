<?php
session_start();
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/roles.php';
require_once __DIR__ . '/../../../connexion.php';

require_role(['Employe', 'Veterinaire', 'Benevole'], "Accès non autorisé");

$prenom = get_prenom();
$role = get_role();

// Bénévoles ne peuvent pas modifier
if ($role === 'Benevole') {
    header('Location: ../dashboard/dashboard.php');
    exit();
}

// Récupérer l'ID de l'animal à modifier
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    header('Location: modifier.php');
    exit();
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = mysqli_real_escape_string($conn, $_POST['Nom']);
    $espece = mysqli_real_escape_string($conn, $_POST['Espece']);
    $date_naissance = mysqli_real_escape_string($conn, $_POST['date_naissance']);
    $sexe = mysqli_real_escape_string($conn, $_POST['Sexe']);
    $enclos = mysqli_real_escape_string($conn, $_POST['Enclos']);

    $sql = "UPDATE animaux SET
            Nom = '$nom',
            Espece = '$espece',
            date_naissance = '$date_naissance',
            Sexe = '$sexe',
            Enclos = '$enclos'
            WHERE id = $id";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Animal modifié avec succès!'); window.location.href='modifier.php';</script>";
        exit();
    } else {
        $error = "Erreur lors de la modification: " . mysqli_error($conn);
    }
}

// Récupérer les données de l'animal
$sql = "SELECT * FROM animaux WHERE id = $id";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    header('Location: modifier.php');
    exit();
}

$animal = $result->fetch_assoc();

// Récupérer les espèces et enclos pour les selects
$especes_query = mysqli_query($conn, "SELECT DISTINCT nom_race FROM especes");
$enclos_query = mysqli_query($conn, "SELECT DISTINCT Nom FROM enclos");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un animal - Zoo Paradis</title>
    <link rel="stylesheet" href="../../../global-nature-zoo.css">
    <link rel="stylesheet" href="../../../dashboard-nature.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .user-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-green), var(--accent-orange));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 4px 15px rgba(74, 124, 44, 0.3);
        }

        .user-avatar i {
            font-size: 2.5rem;
            color: white;
        }

        .sidebar-header {
            text-align: center;
        }

        .nav-menu {
            list-style: none;
            padding: 0;
        }

        .nav-menu li {
            margin-bottom: 0.5rem;
        }

        .nav-menu a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 2rem;
            color: #2d2d2d;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 600;
            border-left: 4px solid transparent;
        }

        .nav-menu a i {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
            color: var(--accent-orange);
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            background: rgba(74, 124, 44, 0.15);
            border-left-color: var(--accent-orange);
            color: var(--primary-green);
        }

        .nav-menu a.logout {
            background: linear-gradient(135deg, #8b0000, #a52a2a);
            color: white;
            margin: 2rem 1rem 0 1rem;
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 15px rgba(139, 0, 0, 0.2);
        }

        .nav-menu a.logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="user-avatar">
                <i class="fas fa-<?php
                    echo match($role) {
                        'Veterinaire' => 'stethoscope',
                        'Benevole' => 'hands-helping',
                        default => 'user'
                    };
                ?>"></i>
            </div>
            <h2><?php echo htmlspecialchars($prenom); ?></h2>
            <p><?php
                echo match($role) {
                    'Veterinaire' => 'Vétérinaire',
                    'Benevole' => 'Bénévole',
                    default => 'Employé'
                };
            ?></p>
        </div>

        <ul class="nav-menu">
            <li><a href="../../main.php"><i class="fas fa-home"></i> Tableau de bord</a></li>
            <li><a href="../dashboard/dashboard.php" class="active"><i class="fas fa-paw"></i> Animaux</a></li>
            <li><a href="../../especes/dashboard/dashboard.php"><i class="fas fa-dna"></i> Espèces</a></li>

            <li><a href="../../../Connexion/deconnexion.php" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="page-header">
            <h1>
                <i class="fas fa-edit"></i>
                Modifier l'animal: <?php echo htmlspecialchars($animal['Nom']); ?>
            </h1>
            <p>Modifiez les informations de l'animal</p>
        </div>

        <?php if (isset($error)): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label for="Nom"><i class="fas fa-signature"></i> Nom de l'animal</label>
                    <input type="text" id="Nom" name="Nom" value="<?php echo htmlspecialchars($animal['Nom']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="Espece"><i class="fas fa-dna"></i> Espèce</label>
                    <select id="Espece" name="Espece" required>
                        <?php while ($esp = mysqli_fetch_assoc($especes_query)): ?>
                            <option value="<?php echo htmlspecialchars($esp['nom_race']); ?>"
                                <?php echo ($animal['Espece'] === $esp['nom_race']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($esp['nom_race']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="date_naissance"><i class="fas fa-calendar"></i> Date de naissance</label>
                    <input type="date" id="date_naissance" name="date_naissance" value="<?php echo htmlspecialchars($animal['date_naissance']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="Sexe"><i class="fas fa-venus-mars"></i> Sexe</label>
                    <select id="Sexe" name="Sexe" required>
                        <option value="Mâle" <?php echo ($animal['Sexe'] === 'Mâle') ? 'selected' : ''; ?>>Mâle</option>
                        <option value="Femelle" <?php echo ($animal['Sexe'] === 'Femelle') ? 'selected' : ''; ?>>Femelle</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="Enclos"><i class="fas fa-home"></i> Enclos</label>
                    <select id="Enclos" name="Enclos" required>
                        <?php while ($enc = mysqli_fetch_assoc($enclos_query)): ?>
                            <option value="<?php echo htmlspecialchars($enc['Nom']); ?>"
                                <?php echo ($animal['Enclos'] === $enc['Nom']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($enc['Nom']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i>
                        Enregistrer les modifications
                    </button>
                    <a href="modifier.php" class="btn-cancel">
                        <i class="fas fa-times"></i>
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>

<?php $conn->close(); ?>
