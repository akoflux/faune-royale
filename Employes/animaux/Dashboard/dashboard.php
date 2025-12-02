<?php
session_start();
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/roles.php';
require_once __DIR__ . '/../../../includes/csrf.php';
require_once __DIR__ . '/../../../connexion.php';

// Vérifier que l'utilisateur est Employé, Vétérinaire ou Bénévole
require_role(['Employe', 'Veterinaire', 'Benevole'], "Accès non autorisé");

$prenom = get_prenom();
$role = get_role();

// Récupération du nombre total d'animaux
$requeteTotal = "SELECT COUNT(*) as total FROM animaux";
$resultTotal = mysqli_query($conn, $requeteTotal);
$totalAnimaux = mysqli_fetch_assoc($resultTotal)['total'];

// Récupération des animaux pour affichage
$requete = "SELECT * FROM animaux ORDER BY id DESC LIMIT 0, 30;";
$resultat = mysqli_query($conn, $requete);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Animaux - Zoo Paradis</title>
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

        .btn-add {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--success), var(--primary));
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 30px rgba(6, 255, 165, 0.3);
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(6, 255, 165, 0.5);
        }

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

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .action-buttons a {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            background: rgba(0, 212, 255, 0.1);
            border: 1px solid var(--primary);
            border-radius: 10px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .action-buttons a:hover {
            background: rgba(0, 212, 255, 0.2);
            transform: translateY(-2px);
        }

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

            .action-buttons {
                flex-wrap: wrap;
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
            <li><a href="dashboard.php" class="active"><i class="fas fa-paw"></i> Animaux</a></li>
            <li><a href="../../especes/Dashboard/dashboard.php"><i class="fas fa-dna"></i> Espèces</a></li>

            <li><a href="../../../Connexion/deconnexion.php" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1>
                    <i class="fas fa-paw"></i>
                    Gestion des animaux
                </h1>
                <p>Liste complète des animaux du zoo</p>
            </div>
            <?php if ($role !== 'Benevole'): ?>
            <a href="../ajouter/ajouter.php" class="btn-add">
                <i class="fas fa-plus"></i>
                Ajouter un animal
            </a>
            <?php endif; ?>
        </div>

        <?php if ($role !== 'Benevole'): ?>
        <div class="action-buttons">
            <a href="../modifier/modifier.php">
                <i class="fas fa-edit"></i>
                Modifier
            </a>
            <a href="../rechercher/recherche.php">
                <i class="fas fa-search"></i>
                Rechercher
            </a>
        </div>
        <?php else: ?>
        <div class="action-buttons">
            <a href="../rechercher/recherche.php">
                <i class="fas fa-search"></i>
                Rechercher
            </a>
        </div>
        <?php endif; ?>

        <div class="stat-box">
            <h3>Total Animaux</h3>
            <div class="value"><?php echo $totalAnimaux; ?></div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Espèce</th>
                        <th>Date de naissance</th>
                        <th>Sexe</th>
                        <th>Enclos</th>
                        <?php if ($role !== 'Benevole'): ?>
                        <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while($enreg = mysqli_fetch_array($resultat)) { ?>
                        <tr>
                            <td><?php echo $enreg["id"]; ?></td>
                            <td><?php echo htmlspecialchars($enreg["Nom"]); ?></td>
                            <td><?php echo htmlspecialchars($enreg["Espece"]); ?></td>
                            <td><?php echo htmlspecialchars($enreg["date_naissance"]); ?></td>
                            <td><?php echo htmlspecialchars($enreg["Sexe"]); ?></td>
                            <td><?php echo htmlspecialchars($enreg["Enclos"]); ?></td>
                            <?php if ($role !== 'Benevole'): ?>
                            <td>
                                <form method="POST" action="supprimer.php" style="display: inline;">
                                    <input type="hidden" name="id" value="<?php echo $enreg['id']; ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet animal ?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>

<?php mysqli_close($conn); ?>
