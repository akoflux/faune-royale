<?php
session_start();
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/roles.php';
require_once __DIR__ . '/../../connexion.php';

// Vérifier que l'utilisateur est Directeur ou Chef d'équipe
require_role(['Directeur', 'Chef_Equipe'], "Accès non autorisé");

$prenom = get_prenom();
$role = get_role();

$success = '';
$error = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_enclos = trim($_POST['nom_enclos'] ?? '');

    if (empty($nom_enclos)) {
        $error = "Le nom de l'enclos est requis";
    } else {
        // Vérifier si l'enclos existe déjà dans la table enclos
        $check = $conn->prepare("SELECT COUNT(*) as count FROM enclos WHERE Nom = ?");
        $check->bind_param("s", $nom_enclos);
        $check->execute();
        $result = $check->get_result()->fetch_assoc();

        if ($result['count'] > 0) {
            $error = "Un enclos avec ce nom existe déjà";
        } else {
            // Insérer dans la table enclos
            $insert = $conn->prepare("INSERT INTO enclos (Nom) VALUES (?)");
            $insert->bind_param("s", $nom_enclos);
            if ($insert->execute()) {
                $success = "Enclos '$nom_enclos' créé avec succès ! Vous pouvez maintenant y assigner des animaux.";
            } else {
                $error = "Erreur lors de la création de l'enclos.";
            }
            $insert->close();
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Enclos - Zoo Paradis</title>
    <link rel="stylesheet" href="../../global-nature-zoo.css">
    <link rel="stylesheet" href="../../dashboard-nature.css">
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
            <li><a href="../main.php"><i class="fas fa-home"></i> Tableau de bord</a></li>
            <li><a href="../animaux/dashboard/dashboard.php"><i class="fas fa-paw"></i> Animaux</a></li>
            <li><a href="../especes/Dashboard/dashboard.php"><i class="fas fa-dna"></i> Espèces</a></li>
            <li><a href="dashboard.php" class="active"><i class="fas fa-warehouse"></i> Enclos</a></li>
            <li><a href="../user/dashboard/dashboard.php"><i class="fas fa-user-cog"></i> Gestion Comptes</a></li>

            <?php if ($role === 'Directeur'): ?>
            <li><a href="../reservations/dashboard.php"><i class="fas fa-calendar-check"></i> Réservations</a></li>
            <?php endif; ?>

            <?php if ($role === 'Directeur' || $role === 'Chef_Equipe'): ?>
            <li><a href="../gestion_comptes/creer_compte.php"><i class="fas fa-user-plus"></i> Créer un compte</a></li>
            <?php endif; ?>

            <li><a href="../../Connexion/deconnexion.php" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1>
                    <i class="fas fa-warehouse"></i>
                    Créer un Enclos
                </h1>
                <p>Ajoutez un nouvel enclos pour vos animaux</p>
            </div>
        </div>

        <div class="form-container">
            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <div>
                    <?php echo htmlspecialchars($success); ?>
                    <br>
                    <a href="dashboard.php" style="color: var(--success); text-decoration: underline;">Retour aux enclos</a>
                    ou
                    <a href="../animaux/dashboard/dashboard.php" style="color: var(--success); text-decoration: underline;">Assigner des animaux</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <div><?php echo htmlspecialchars($error); ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="nom_enclos">
                    <i class="fas fa-tag"></i>
                    Nom de l'enclos
                </label>
                <input
                    type="text"
                    id="nom_enclos"
                    name="nom_enclos"
                    placeholder="Ex: Enclos des Lions, Savane Africaine..."
                    required
                    autofocus
                >
                <div class="help-text">
                    <i class="fas fa-info-circle"></i>
                    Choisissez un nom descriptif et unique pour cet enclos
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-check"></i>
                    Créer l'enclos
                </button>
                <a href="dashboard.php" class="btn-cancel">
                    <i class="fas fa-times"></i>
                    Annuler
                </a>
            </div>
        </form>

        <div style="margin-top: 2rem; padding: 1.5rem; background: rgba(0, 212, 255, 0.05); border: 1px solid rgba(0, 212, 255, 0.2); border-radius: 12px;">
            <h3 style="font-size: 1.1rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-lightbulb" style="color: var(--primary);"></i>
                Conseil
            </h3>
            <p style="color: var(--text-muted); line-height: 1.6;">
                Après avoir créé l'enclos, vous pourrez y assigner des animaux depuis la page de gestion des animaux.
                L'enclos apparaîtra dans la liste dès que vous aurez assigné au moins un animal.
            </p>
        </div>
        </div>
    </main>
</body>
</html>

<?php mysqli_close($conn); ?>
