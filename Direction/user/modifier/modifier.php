<?php
session_start();
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/roles.php';
require_once __DIR__ . '/../../../connexion.php';

require_role(['Directeur', 'Chef_Equipe'], "Accès non autorisé");

$prenom = get_prenom();
$role = get_role();

// Récupération de tous les comptes
$sql = "SELECT * FROM users ORDER BY id DESC";
$result = $conn->query($sql);
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

        .btn-edit {
            background: rgba(0, 212, 255, 0.1);
            border: 1px solid var(--primary);
            color: var(--primary);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-edit:hover {
            background: rgba(0, 212, 255, 0.2);
            transform: scale(1.05);
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            color: var(--text-muted);
            font-size: 1.2rem;
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
                Modifier un compte
            </h1>
            <p>Sélectionnez un compte à modifier</p>
        </div>

        <div class="table-container">
            <?php if ($result && $result->num_rows > 0): ?>
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
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row["id"]; ?></td>
                                <td>
                                    <span class="role-badge role-<?php
                                        echo match($row["role"]) {
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
                                            echo match($row["role"]) {
                                                'Directeur' => '<i class="fas fa-crown"></i> Directeur',
                                                'Chef_Equipe' => '<i class="fas fa-user-tie"></i> Chef d\'équipe',
                                                'Veterinaire' => '<i class="fas fa-stethoscope"></i> Vétérinaire',
                                                'Employe' => '<i class="fas fa-user"></i> Employé',
                                                'Benevole' => '<i class="fas fa-hands-helping"></i> Bénévole',
                                                'Client' => '<i class="fas fa-user-circle"></i> Client',
                                                default => $row["role"]
                                            };
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row["nom"] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row["prenom"] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row["email"]); ?></td>
                                <td><?php echo htmlspecialchars($row["telephone"] ?? ''); ?></td>
                                <td>
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn-edit">
                                        <i class="fas fa-edit"></i>
                                        Modifier
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-exclamation-circle" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                    <p>Aucun compte trouvé dans la base de données</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>

<?php $conn->close(); ?>
