<?php
session_start();
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/roles.php';
require_once __DIR__ . '/../../../connexion.php';

require_role(['Directeur', 'Chef_Equipe'], "Accès non autorisé");

$prenom = get_prenom();
$role = get_role();

// Récupérer l'ID du compte à modifier
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    header('Location: modifier.php');
    exit();
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = mysqli_real_escape_string($conn, $_POST['nom']);
    $prenom_user = mysqli_real_escape_string($conn, $_POST['prenom']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $telephone = mysqli_real_escape_string($conn, $_POST['telephone']);
    $role_user = mysqli_real_escape_string($conn, $_POST['role']);

    // SÉCURITÉ: Empêcher les Chefs d'équipe de créer ou modifier des Directeurs et des Chefs d'équipe
    if ($role === 'Chef_Equipe' && ($role_user === 'Directeur' || $role_user === 'Chef_Equipe')) {
        $error = "Seul un Directeur peut attribuer les rôles de Directeur ou Chef d'équipe.";
    } else {
        $sql = "UPDATE users SET
                nom = '$nom',
                prenom = '$prenom_user',
                email = '$email',
                telephone = '$telephone',
                role = '$role_user'
                WHERE id = $id";

        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Compte modifié avec succès!'); window.location.href='modifier.php';</script>";
            exit();
        } else {
            $error = "Erreur lors de la modification: " . mysqli_error($conn);
        }
    }
}

// Récupérer les données du compte
$sql = "SELECT * FROM users WHERE id = $id";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    header('Location: modifier.php');
    exit();
}

$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un compte - Zoo Paradis</title>
    <link rel="stylesheet" href="../../../global-futuriste.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: rgba(22, 33, 62, 0.8);
            backdrop-filter: blur(20px);
            border-right: 1px solid var(--border);
            padding: 2rem 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
        }

        .sidebar-header {
            text-align: center;
            padding: 0 1.5rem 2rem;
            border-bottom: 1px solid var(--border);
            margin-bottom: 2rem;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 8px 30px rgba(0, 212, 255, 0.4);
        }

        .user-avatar i {
            font-size: 2.5rem;
            color: white;
        }

        .sidebar-header h2 {
            font-size: 1.3rem;
            margin-bottom: 0.3rem;
        }

        .sidebar-header p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .nav-menu {
            list-style: none;
            padding: 0 1rem;
        }

        .nav-menu li {
            margin-bottom: 0.5rem;
        }

        .nav-menu a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.2rem;
            color: var(--text);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-menu a i {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
            color: var(--primary);
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            background: rgba(0, 212, 255, 0.1);
            border-left: 3px solid var(--primary);
            padding-left: calc(1.2rem - 3px);
        }

        .nav-menu a.logout {
            background: rgba(255, 0, 110, 0.1);
            border: 1px solid var(--danger);
            margin-top: 2rem;
        }

        .nav-menu a.logout:hover {
            background: rgba(255, 0, 110, 0.2);
        }

        .main-content {
            margin-left: 280px;
            flex: 1;
            padding: 2rem;
            width: calc(100% - 280px);
        }

        .page-header {
            margin-bottom: 2rem;
            background: rgba(22, 33, 62, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem;
        }

        .page-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-header h1 i {
            color: var(--primary);
        }

        .page-header p {
            color: var(--text-muted);
        }

        .form-container {
            background: rgba(22, 33, 62, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--primary);
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 1rem 1.5rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text);
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 20px rgba(0, 212, 255, 0.3);
        }

        .form-group select {
            cursor: pointer;
        }

        .form-group select option {
            background: rgba(22, 33, 62, 0.95);
            color: var(--text);
            padding: 0.5rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-submit {
            flex: 1;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--success), var(--primary));
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 30px rgba(6, 255, 165, 0.3);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(6, 255, 165, 0.5);
        }

        .btn-cancel {
            flex: 1;
            padding: 1rem 2rem;
            background: rgba(255, 0, 110, 0.1);
            border: 1px solid var(--danger);
            border-radius: 12px;
            color: var(--danger);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-cancel:hover {
            background: rgba(255, 0, 110, 0.2);
            transform: translateY(-2px);
        }

        .error-message {
            background: rgba(255, 0, 110, 0.1);
            border: 1px solid var(--danger);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            color: var(--danger);
        }

        @media (max-width: 968px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }
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
            <li><a href="../../especes/Dashboard/dashboard.php"><i class="fas fa-dna"></i> Espèces</a></li>
            <li><a href="../dashboard/dashboard.php" class="active"><i class="fas fa-user-cog"></i> Gestion Comptes</a></li>

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
                Modifier le compte: <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?>
            </h1>
            <p>Modifiez les informations du compte</p>
        </div>

        <?php if (isset($error)): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nom"><i class="fas fa-user"></i> Nom</label>
                        <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="prenom"><i class="fas fa-user"></i> Prénom</label>
                        <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="telephone"><i class="fas fa-phone"></i> Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="role"><i class="fas fa-user-tag"></i> Rôle</label>
                        <select id="role" name="role" required>
                            <?php if ($role === 'Directeur'): ?>
                                <option value="Directeur" <?php echo ($user['role'] === 'Directeur') ? 'selected' : ''; ?>>Directeur</option>
                                <option value="Chef_Equipe" <?php echo ($user['role'] === 'Chef_Equipe') ? 'selected' : ''; ?>>Chef d'équipe</option>
                            <?php endif; ?>
                            <option value="Veterinaire" <?php echo ($user['role'] === 'Veterinaire') ? 'selected' : ''; ?>>Vétérinaire</option>
                            <option value="Employe" <?php echo ($user['role'] === 'Employe') ? 'selected' : ''; ?>>Employé</option>
                            <option value="Benevole" <?php echo ($user['role'] === 'Benevole') ? 'selected' : ''; ?>>Bénévole</option>
                            <option value="Client" <?php echo ($user['role'] === 'Client') ? 'selected' : ''; ?>>Client</option>
                        </select>
                        <?php if ($role === 'Chef_Equipe'): ?>
                            <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">
                                <i class="fas fa-info-circle"></i> Seul un Directeur peut attribuer les rôles de Directeur ou Chef d'équipe
                            </small>
                        <?php endif; ?>
                    </div>
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
