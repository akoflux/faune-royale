<?php
session_start();
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/roles.php';
require_once __DIR__ . '/../../../connexion.php';

// Vérifier que l'utilisateur est connecté avec le bon rôle
require_role(['Employe', 'Chef_Equipe'], "Accès non autorisé");

$prenom = get_prenom();
$role = get_role();

// Vérifier la connexion à la base de données
if (!$conn) {
    die("Erreur de connexion à la base de données.");
}

// Récupérer les espèces en utilisant mysqli
$query = mysqli_query($conn, "SELECT id, nom_race FROM especes ORDER BY nom_race");
$reqq = mysqli_query($conn, "SELECT Nom FROM enclos ORDER BY Nom");

if (!$query) {
    die("Erreur SQL : " . mysqli_error($conn));
}

$especes = [];
while ($row = mysqli_fetch_assoc($query)) {
    $especes[] = $row;
}

$enclos = [];
while ($row = mysqli_fetch_assoc($reqq)) {
    $enclos[] = $row;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Animal - Zoo Paradis</title>
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
                <i class="fas fa-<?php echo $role === 'Chef_Equipe' ? 'user-tie' : 'user'; ?>"></i>
            </div>
            <h2><?php echo htmlspecialchars($prenom); ?></h2>
            <p><?php echo $role === 'Chef_Equipe' ? "Chef d'équipe" : 'Employé'; ?></p>
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
            <div>
                <h1>
                    <i class="fas fa-paw"></i>
                    Ajouter un Animal
                </h1>
                <p>Enregistrez un nouvel animal dans votre zoo</p>
            </div>
        </div>

        <div class="form-container">
            <form action="ajout.php" method="post">
            <div class="form-grid">
                <div class="form-group">
                    <label for="Nom">
                        <i class="fas fa-tag"></i>
                        Nom de l'animal
                    </label>
                    <input type="text" id="Nom" name="Nom" placeholder="Ex: Simba" required>
                </div>

                <div class="form-group">
                    <label for="espece">
                        <i class="fas fa-dna"></i>
                        Espèce
                    </label>
                    <select name="espece" id="espece" required>
                        <option value="">Sélectionnez une espèce</option>
                        <?php foreach ($especes as $espece): ?>
                            <option value="<?php echo htmlspecialchars($espece['nom_race']); ?>">
                                <?php echo htmlspecialchars($espece['nom_race']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="date_naissance">
                        <i class="fas fa-calendar"></i>
                        Date de naissance
                    </label>
                    <input type="date" id="date_naissance" name="date_naissance" required>
                </div>

                <div class="form-group">
                    <label for="Sexe">
                        <i class="fas fa-venus-mars"></i>
                        Sexe
                    </label>
                    <select name="Sexe" id="Sexe" required>
                        <option value="Mâle">Mâle</option>
                        <option value="Femelle">Femelle</option>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label for="Enclos">
                        <i class="fas fa-warehouse"></i>
                        Enclos
                    </label>
                    <select name="Enclos" id="Enclos">
                        <option value="">Sélectionnez un enclos</option>
                        <?php foreach ($enclos as $enc): ?>
                            <option value="<?php echo htmlspecialchars($enc['Nom']); ?>">
                                <?php echo htmlspecialchars($enc['Nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-check"></i>
                    Enregistrer l'animal
                </button>
                <a href="../dashboard/dashboard.php" class="btn-cancel">
                    <i class="fas fa-arrow-left"></i>
                    Retour
                </a>
            </div>
            </form>
        </div>
    </main>
</body>
</html>
