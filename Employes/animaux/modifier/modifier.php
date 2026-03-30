<?php
session_start();
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/roles.php';
require_once __DIR__ . '/../../../connexion.php';

require_role(['Employe', 'Veterinaire', 'Benevole'], "Accès non autorisé");

$prenom = get_prenom();
$role = get_role();

// Récupération de tous les animaux
$sql = "SELECT * FROM animaux ORDER BY id DESC";
$result = $conn->query($sql);
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
            <li><a href="../Dashboard/dashboard.php" class="active"><i class="fas fa-paw"></i> Animaux</a></li>
            <li><a href="../../especes/dashboard/dashboard.php"><i class="fas fa-dna"></i> Espèces</a></li>

            <li><a href="../../../Connexion/deconnexion.php" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="page-header">
            <h1>
                <i class="fas fa-edit"></i>
                Modifier un animal
            </h1>
            <p>Sélectionnez un animal à modifier</p>
        </div>

        <div class="table-container">
            <?php if ($result && $result->num_rows > 0): ?>
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
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row["id"]; ?></td>
                                <td><?php echo htmlspecialchars($row["Nom"]); ?></td>
                                <td><?php echo htmlspecialchars($row["Espece"]); ?></td>
                                <td><?php echo htmlspecialchars($row["date_naissance"]); ?></td>
                                <td><?php echo htmlspecialchars($row["Sexe"]); ?></td>
                                <td><?php echo htmlspecialchars($row["Enclos"]); ?></td>
                                <?php if ($role !== 'Benevole'): ?>
                                <td>
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn-edit">
                                        <i class="fas fa-edit"></i>
                                        Modifier
                                    </a>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-exclamation-circle" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                    <p>Aucun animal trouvé dans la base de données</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>

<?php $conn->close(); ?>
