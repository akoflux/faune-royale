<?php
session_start();
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/roles.php';
require_once __DIR__ . '/../../../connexion.php';

require_role(['Directeur', 'Chef_Equipe'], "Accès non autorisé");

$prenom = get_prenom();
$role = get_role();

// Récupération des données si une recherche est effectuée
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sql = "SELECT * FROM users WHERE
        nom LIKE '%$search%' OR
        prenom LIKE '%$search%' OR
        email LIKE '%$search%' OR
        role LIKE '%$search%'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rechercher un compte - Zoo Paradis</title>
    <link rel="stylesheet" href="../../../global-nature-zoo.css">
    <link rel="stylesheet" href="../../../dashboard-nature.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
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
                <i class="fas fa-search"></i>
                Rechercher un compte
            </h1>
            <p>Recherchez un compte par nom, prénom, email ou rôle</p>
        </div>

        <div class="search-container">
            <form method="GET" class="search-form">
                <input
                    type="text"
                    name="search"
                    class="search-input"
                    placeholder="Rechercher par nom, prénom, email ou rôle..."
                    value="<?php echo htmlspecialchars($search); ?>"
                >
                <button type="submit" class="btn-search">
                    <i class="fas fa-search"></i>
                    Rechercher
                </button>
            </form>
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
                                    <form method="POST" action="supprimer.php" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce compte ?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                    <p>Aucun résultat trouvé<?php echo $search ? ' pour "' . htmlspecialchars($search) . '"' : ''; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>

<?php $conn->close(); ?>
