<?php
session_start();
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/roles.php';
require_once __DIR__ . '/../../../connexion.php';

// Vérifier que l'utilisateur est Directeur ou Chef d'équipe
require_role(['Directeur', 'Chef_Equipe'], "Accès non autorisé");

$prenom = get_prenom();
$role = get_role();

// Récupération du nombre total d'utilisateurs
$requeteTotal = "SELECT COUNT(*) as total FROM users";
$resultTotal = mysqli_query($conn, $requeteTotal);
$TotalUsers = mysqli_fetch_assoc($resultTotal)['total'];

// Récupération des statistiques par rôle
$directeurs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'Directeur'"))['total'];
$chefs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'Chef_Equipe'"))['total'];
$veterinaires = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'Veterinaire'"))['total'];
$employes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'Employe'"))['total'];
$benevoles = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'Benevole'"))['total'];
$clients = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'Client'"))['total'];

// Récupération des utilisateurs pour affichage
$requete = "SELECT * FROM users ORDER BY date_creation DESC LIMIT 0, 30;";
$resultat = mysqli_query($conn, $requete);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Utilisateurs - Zoo Paradis</title>
    <link rel="stylesheet" href="../../../global-futuriste.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */
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

        /* MAIN CONTENT */
        .main-content {
            margin-left: 280px;
            flex: 1;
            padding: 2rem;
            width: calc(100% - 280px);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .btn-create {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 30px rgba(0, 212, 255, 0.3);
        }

        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(0, 212, 255, 0.5);
        }

        /* ACTION BUTTONS */
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .action-buttons a {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
            padding: 1.2rem 2rem;
            background: rgba(22, 33, 62, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 15px;
            color: var(--text);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .action-buttons a:hover {
            background: rgba(0, 212, 255, 0.1);
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 212, 255, 0.3);
        }

        .action-buttons a i {
            font-size: 1.3rem;
            color: var(--primary);
        }

        /* STATS */
        .stat-box {
            background: rgba(22, 33, 62, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: inline-block;
        }

        .stat-box h3 {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .stat-box .value {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* TABLE */
        .table-container {
            background: rgba(22, 33, 62, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        th {
            color: var(--primary);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        td {
            color: var(--text);
        }

        tr:hover {
            background: rgba(0, 212, 255, 0.05);
        }

        .role-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .role-directeur {
            background: rgba(255, 215, 0, 0.2);
            color: #ffd700;
            border: 1px solid rgba(255, 215, 0, 0.3);
        }

        .role-chef {
            background: rgba(0, 212, 255, 0.2);
            color: var(--primary);
            border: 1px solid rgba(0, 212, 255, 0.3);
        }

        .role-veterinaire {
            background: rgba(6, 255, 165, 0.2);
            color: var(--success);
            border: 1px solid rgba(6, 255, 165, 0.3);
        }

        .role-employe {
            background: rgba(123, 44, 191, 0.2);
            color: var(--secondary);
            border: 1px solid rgba(123, 44, 191, 0.3);
        }

        .role-benevole {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-muted);
            border: 1px solid var(--border);
        }

        .role-client {
            background: rgba(255, 0, 110, 0.2);
            color: var(--accent);
            border: 1px solid rgba(255, 0, 110, 0.3);
        }

        .btn-delete {
            background: rgba(255, 0, 110, 0.1);
            border: 1px solid var(--danger);
            color: var(--danger);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .btn-delete:hover {
            background: rgba(255, 0, 110, 0.2);
            transform: scale(1.05);
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

            .page-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            table {
                font-size: 0.9rem;
            }

            th, td {
                padding: 0.7rem;
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
            <li><a href="../../enclos/dashboard.php"><i class="fas fa-warehouse"></i> Enclos</a></li>
            <li><a href="dashboard.php" class="active"><i class="fas fa-user-cog"></i> Gestion Comptes</a></li>

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
            <div>
                <h1>
                    <i class="fas fa-user-cog"></i>
                    Gestion des Comptes
                </h1>
                <p>Gestion complète de tous les utilisateurs et du personnel</p>
            </div>
            <?php if ($role === 'Directeur' || $role === 'Chef_Equipe'): ?>
            <a href="../../gestion_comptes/creer_compte.php" class="btn-create">
                <i class="fas fa-user-plus"></i>
                Créer un compte
            </a>
            <?php endif; ?>
        </div>

        <div class="action-buttons">
            <a href="../modifier/modifier.php">
                <i class="fas fa-edit"></i>
                Modifier
            </a>
            <a href="../recherche/recherche_new.php">
                <i class="fas fa-search"></i>
                Rechercher
            </a>
        </div>

        <!-- STATISTIQUES -->
        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="stat-box" style="margin-bottom: 0;">
                <h3>Total Comptes</h3>
                <div class="value"><?php echo $TotalUsers; ?></div>
            </div>

            <div class="stat-box" style="margin-bottom: 0;">
                <h3><i class="fas fa-crown"></i> Directeurs</h3>
                <div class="value" style="background: linear-gradient(135deg, #ffd700, #ffed4e); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?php echo $directeurs; ?></div>
            </div>

            <div class="stat-box" style="margin-bottom: 0;">
                <h3><i class="fas fa-user-tie"></i> Chefs d'équipe</h3>
                <div class="value"><?php echo $chefs; ?></div>
            </div>

            <div class="stat-box" style="margin-bottom: 0;">
                <h3><i class="fas fa-stethoscope"></i> Vétérinaires</h3>
                <div class="value" style="background: linear-gradient(135deg, var(--success), #00ff88); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?php echo $veterinaires; ?></div>
            </div>

            <div class="stat-box" style="margin-bottom: 0;">
                <h3><i class="fas fa-user"></i> Employés</h3>
                <div class="value"><?php echo $employes; ?></div>
            </div>

            <div class="stat-box" style="margin-bottom: 0;">
                <h3><i class="fas fa-hands-helping"></i> Bénévoles</h3>
                <div class="value"><?php echo $benevoles; ?></div>
            </div>

            <div class="stat-box" style="margin-bottom: 0;">
                <h3><i class="fas fa-user-circle"></i> Clients</h3>
                <div class="value" style="background: linear-gradient(135deg, var(--accent), #ff2266); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?php echo $clients; ?></div>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Rôle</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($enreg = mysqli_fetch_array($resultat)) { ?>
                        <tr>
                            <td><?php echo $enreg["id"]; ?></td>
                            <td>
                                <span class="role-badge role-<?php
                                    echo match($enreg["role"]) {
                                        'Directeur' => 'directeur',
                                        'Chef_Equipe' => 'chef',
                                        'Veterinaire' => 'veterinaire',
                                        'Employe' => 'employe',
                                        'Benevole' => 'benevole',
                                        'Client' => 'client',
                                        default => 'employe'
                                    };
                                ?>">
                                    <?php
                                        echo match($enreg["role"]) {
                                            'Directeur' => '<i class="fas fa-crown"></i> Directeur',
                                            'Chef_Equipe' => '<i class="fas fa-user-tie"></i> Chef d\'équipe',
                                            'Veterinaire' => '<i class="fas fa-stethoscope"></i> Vétérinaire',
                                            'Employe' => '<i class="fas fa-user"></i> Employé',
                                            'Benevole' => '<i class="fas fa-hands-helping"></i> Bénévole',
                                            'Client' => '<i class="fas fa-user-circle"></i> Client',
                                            default => $enreg["role"]
                                        };
                                    ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($enreg["nom"] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($enreg["prenom"] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($enreg["email"]); ?></td>
                            <td><?php echo htmlspecialchars($enreg["telephone"] ?? ''); ?></td>
                            <td>
                                <form method="POST" action="supprimer.php" style="display: inline;">
                                    <input type="hidden" name="id" value="<?php echo $enreg['id']; ?>">
                                    <button type="submit" class="btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>

<?php mysqli_close($conn); ?>
