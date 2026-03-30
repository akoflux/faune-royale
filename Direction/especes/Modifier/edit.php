<?php
session_start();
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/roles.php';
require_once __DIR__ . '/../../../connexion.php';

require_role(['Directeur', 'Chef_Equipe'], "Accès non autorisé");

$prenom = get_prenom();
$role = get_role();

// Récupérer l'ID de l'espèce à modifier
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    header('Location: modifier.php');
    exit();
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_race = mysqli_real_escape_string($conn, $_POST['nom_race']);
    $type_nourriture = mysqli_real_escape_string($conn, $_POST['type_nourriture']);
    $duree_vie = mysqli_real_escape_string($conn, $_POST['duree_vie']);
    $animal_aquatique = mysqli_real_escape_string($conn, $_POST['animal_aquatique']);

    $sql = "UPDATE especes SET
            nom_race = '$nom_race',
            type_nourriture = '$type_nourriture',
            duree_vie = '$duree_vie',
            animal_aquatique = '$animal_aquatique'
            WHERE id = $id";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Espèce modifiée avec succès!'); window.location.href='modifier.php';</script>";
        exit();
    } else {
        $error = "Erreur lors de la modification: " . mysqli_error($conn);
    }
}

// Récupérer les données de l'espèce
$sql = "SELECT * FROM especes WHERE id = $id";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    header('Location: modifier.php');
    exit();
}

$espece = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une espèce - Zoo Paradis</title>
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
                <i class="fas fa-<?php echo $role === 'Directeur' ? 'crown' : 'user-tie'; ?>"></i>
            </div>
            <h2><?php echo htmlspecialchars($prenom); ?></h2>
            <p><?php echo $role === 'Directeur' ? 'Directeur' : "Chef d'équipe"; ?></p>
        </div>

        <ul class="nav-menu">
            <li><a href="../../main.php"><i class="fas fa-home"></i> Tableau de bord</a></li>
            <li><a href="../../animaux/dashboard/dashboard.php"><i class="fas fa-paw"></i> Animaux</a></li>
            <li><a href="../Dashboard/dashboard.php" class="active"><i class="fas fa-dna"></i> Espèces</a></li>
            <li><a href="../../enclos/dashboard.php"><i class="fas fa-warehouse"></i> Enclos</a></li>
            <li><a href="../../user/dashboard/dashboard.php"><i class="fas fa-user-cog"></i> Gestion Comptes</a></li>

            <?php if ($role === 'Directeur'): ?>
            <li><a href="../../reservations/dashboard.php"><i class="fas fa-calendar-check"></i> Réservations</a></li>
            <?php endif; ?>

            <?php if ($role === 'Directeur' || $role === 'Chef_Equipe'): ?>
            <li><a href="../../gestion_comptes/creer_compte.php"><i class="fas fa-user-plus"></i> Créer un compte</a></li>
            <?php endif; ?>

            <li><a href="../../../Connexion/deconnexion.php" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="page-header">
            <h1>
                <i class="fas fa-edit"></i>
                Modifier l'espèce: <?php echo htmlspecialchars($espece['nom_race']); ?>
            </h1>
            <p>Modifiez les informations de l'espèce</p>
        </div>

        <?php if (isset($error)): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label for="nom_race"><i class="fas fa-signature"></i> Nom de race</label>
                    <input type="text" id="nom_race" name="nom_race" value="<?php echo htmlspecialchars($espece['nom_race']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="type_nourriture"><i class="fas fa-utensils"></i> Type de nourriture</label>
                    <input type="text" id="type_nourriture" name="type_nourriture" value="<?php echo htmlspecialchars($espece['type_nourriture'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="duree_vie"><i class="fas fa-clock"></i> Durée de vie (années)</label>
                    <input type="text" id="duree_vie" name="duree_vie" value="<?php echo htmlspecialchars($espece['duree_vie'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="animal_aquatique"><i class="fas fa-water"></i> Animal aquatique</label>
                    <select id="animal_aquatique" name="animal_aquatique" required>
                        <option value="Oui" <?php echo ($espece['animal_aquatique'] === 'Oui') ? 'selected' : ''; ?>>Oui</option>
                        <option value="Non" <?php echo ($espece['animal_aquatique'] === 'Non') ? 'selected' : ''; ?>>Non</option>
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
