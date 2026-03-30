<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../connexion.php';

// Vérifier que l'utilisateur est connecté et est un client
require_role('Client', "Accès réservé aux clients.");

$user_id = get_user_id();
$prenom = get_prenom();

// Récupérer les informations complètes de l'utilisateur
$stmt = $conn->prepare("SELECT nom, prenom, email, telephone FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Gestion des messages de succès/erreur
$success = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Faune Royal</title>
    <link rel="stylesheet" href="../global-nature-zoo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #faf8f3 0%, #f4e7d7 100%);
            min-height: 100vh;
        }

        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .profile-header {
            background: white;
            border: 3px solid var(--primary-green);
            border-radius: var(--radius-lg);
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-green), var(--accent-orange), var(--accent-gold));
        }

        .profile-header h1 {
            color: var(--primary-green);
            font-size: 1.8rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            background: white;
            border: 2px solid var(--primary-light);
            border-radius: var(--radius-md);
            color: var(--primary-green);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-secondary:hover {
            background: var(--primary-light);
            color: white;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
        }

        .profile-sidebar {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .profile-avatar-card {
            background: white;
            border: 2px solid var(--border-light);
            border-radius: var(--radius-lg);
            padding: 2rem;
            text-align: center;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
        }

        .profile-avatar-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-green), var(--accent-orange));
        }

        .avatar-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-green), var(--accent-orange));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            font-weight: 800;
            margin: 0 auto 1.5rem;
            box-shadow: 0 8px 20px rgba(74, 124, 44, 0.3);
            position: relative;
        }

        .avatar-edit {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 35px;
            height: 35px;
            background: var(--accent-orange);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            border: 3px solid white;
            transition: var(--transition);
        }

        .avatar-edit:hover {
            transform: scale(1.1);
            background: var(--primary-green);
        }

        .profile-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-green);
            margin-bottom: 0.5rem;
        }

        .profile-email {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .profile-stats {
            background: white;
            border: 2px solid var(--border-light);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
        }

        .profile-stats h3 {
            color: var(--primary-green);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid var(--border-light);
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        .stat-label {
            color: var(--text-muted);
        }

        .stat-value {
            font-weight: 700;
            color: var(--primary-green);
        }

        .profile-content {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .profile-section {
            background: white;
            border: 2px solid var(--border-light);
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
        }

        .profile-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-green), var(--accent-orange));
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .section-header h2 {
            color: var(--primary-green);
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .btn-edit {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            background: linear-gradient(135deg, var(--primary-green), var(--primary-light));
            border: none;
            border-radius: var(--radius-md);
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .info-label {
            font-size: 0.9rem;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 1.1rem;
            color: var(--text-dark);
            font-weight: 600;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: var(--radius-md);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .alert-success {
            background: rgba(74, 124, 44, 0.1);
            border: 2px solid var(--success);
            color: var(--success);
        }

        .alert-error {
            background: rgba(220, 38, 38, 0.1);
            border: 2px solid var(--danger);
            color: var(--danger);
        }

        .alert i {
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            color: var(--primary-green);
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid var(--border-light);
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-green);
            box-shadow: 0 0 0 3px rgba(74, 124, 44, 0.1);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary-green), var(--primary-light));
            color: white;
            padding: 0.9rem 2rem;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-danger {
            background: linear-gradient(135deg, #8b0000, #a52a2a);
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 0, 0, 0.3);
        }

        .danger-zone {
            border-top: 2px solid rgba(220, 38, 38, 0.2);
            padding-top: 2rem;
            margin-top: 2rem;
        }

        .danger-zone h3 {
            color: var(--danger);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .edit-form {
            display: none;
        }

        .edit-form.active {
            display: block;
        }

        .view-mode.hidden {
            display: none;
        }

        @media (max-width: 968px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <h1>
                <i class="fas fa-user-circle"></i>
                Mon Profil
            </h1>
            <div class="header-actions">
                <a href="main.php" class="btn-secondary">
                    <i class="fas fa-home"></i>
                    Dashboard
                </a>
                <a href="../Connexion/deconnexion.php" class="btn-secondary" style="border-color: #8b0000; color: #8b0000;">
                    <i class="fas fa-sign-out-alt"></i>
                    Déconnexion
                </a>
            </div>
        </div>

        <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <div><?php echo htmlspecialchars($success); ?></div>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <div><?php echo htmlspecialchars($error); ?></div>
        </div>
        <?php endif; ?>

        <div class="profile-grid">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-avatar-card">
                    <div class="avatar-circle">
                        <?php echo strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1)); ?>
                        <div class="avatar-edit" title="Changer de photo">
                            <i class="fas fa-camera"></i>
                        </div>
                    </div>
                    <div class="profile-name"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></div>
                    <div class="profile-email"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>

                <div class="profile-stats">
                    <h3><i class="fas fa-chart-line"></i> Statistiques</h3>
                    <?php
                    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM reservations WHERE user_id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $total_reservations = $stmt->get_result()->fetch_assoc()['total'];
                    $stmt->close();

                    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM reservations WHERE user_id = ? AND statut = 'confirmee'");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $reservations_confirmees = $stmt->get_result()->fetch_assoc()['total'];
                    $stmt->close();

                    $stmt = $conn->prepare("SELECT MIN(date_creation) as premiere FROM reservations WHERE user_id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $premiere_reservation = $stmt->get_result()->fetch_assoc()['premiere'];
                    $stmt->close();
                    ?>
                    <div class="stat-item">
                        <span class="stat-label">Réservations totales</span>
                        <span class="stat-value"><?php echo $total_reservations; ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Visites confirmées</span>
                        <span class="stat-value"><?php echo $reservations_confirmees; ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Membre depuis</span>
                        <span class="stat-value">
                            <?php
                            if ($premiere_reservation) {
                                echo date('Y', strtotime($premiere_reservation));
                            } else {
                                echo date('Y');
                            }
                            ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Contenu principal -->
            <div class="profile-content">
                <!-- Informations personnelles -->
                <div class="profile-section">
                    <div class="section-header">
                        <h2><i class="fas fa-user"></i> Informations personnelles</h2>
                        <button class="btn-edit" onclick="toggleEditMode('personal')">
                            <i class="fas fa-edit"></i>
                            Modifier
                        </button>
                    </div>

                    <div id="personal-view" class="view-mode">
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Prénom</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['prenom']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Nom</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['nom']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Email</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Téléphone</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['telephone']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div id="personal-edit" class="edit-form">
                        <form action="update_profil.php" method="POST">
                            <div class="info-grid">
                                <div class="form-group">
                                    <label for="prenom"><i class="fas fa-user"></i> Prénom</label>
                                    <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="nom"><i class="fas fa-user"></i> Nom</label>
                                    <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="telephone"><i class="fas fa-phone"></i> Téléphone</label>
                                    <input type="tel" id="telephone" name="telephone" value="<?php echo htmlspecialchars($user['telephone']); ?>" pattern="[0-9]{10}" required>
                                </div>
                            </div>
                            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                                <button type="submit" class="btn-submit">
                                    <i class="fas fa-save"></i>
                                    Enregistrer les modifications
                                </button>
                                <button type="button" class="btn-secondary" onclick="toggleEditMode('personal')">
                                    <i class="fas fa-times"></i>
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Sécurité -->
                <div class="profile-section">
                    <div class="section-header">
                        <h2><i class="fas fa-lock"></i> Sécurité</h2>
                        <button class="btn-edit" onclick="toggleEditMode('security')">
                            <i class="fas fa-key"></i>
                            Changer le mot de passe
                        </button>
                    </div>

                    <div id="security-view" class="view-mode">
                        <div class="info-item">
                            <span class="info-label">Mot de passe</span>
                            <span class="info-value">••••••••</span>
                        </div>
                        <p style="color: var(--text-muted); margin-top: 1rem;">
                            <i class="fas fa-info-circle"></i>
                            Dernière modification : Non disponible
                        </p>
                    </div>

                    <div id="security-edit" class="edit-form">
                        <form action="update_password.php" method="POST">
                            <div class="form-group">
                                <label for="current_password"><i class="fas fa-lock"></i> Mot de passe actuel</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password"><i class="fas fa-key"></i> Nouveau mot de passe</label>
                                <input type="password" id="new_password" name="new_password" minlength="8" required>
                                <small>Minimum 8 caractères</small>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password"><i class="fas fa-check"></i> Confirmer le nouveau mot de passe</label>
                                <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
                            </div>
                            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                                <button type="submit" class="btn-submit">
                                    <i class="fas fa-save"></i>
                                    Modifier le mot de passe
                                </button>
                                <button type="button" class="btn-secondary" onclick="toggleEditMode('security')">
                                    <i class="fas fa-times"></i>
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="danger-zone">
                        <h3><i class="fas fa-exclamation-triangle"></i> Zone dangereuse</h3>
                        <p style="color: var(--text-muted); margin-bottom: 1rem;">
                            La suppression de votre compte est irréversible. Toutes vos données seront définitivement supprimées.
                        </p>
                        <button class="btn-danger" onclick="confirmDeleteAccount()">
                            <i class="fas fa-trash-alt"></i>
                            Supprimer mon compte
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleEditMode(section) {
            const viewMode = document.getElementById(`${section}-view`);
            const editForm = document.getElementById(`${section}-edit`);

            if (editForm.classList.contains('active')) {
                editForm.classList.remove('active');
                viewMode.classList.remove('hidden');
            } else {
                editForm.classList.add('active');
                viewMode.classList.add('hidden');
            }
        }

        function confirmDeleteAccount() {
            if (confirm('⚠️ ATTENTION ⚠️\n\nÊtes-vous VRAIMENT sûr de vouloir supprimer votre compte ?\n\nCette action est IRRÉVERSIBLE et supprimera :\n- Toutes vos informations personnelles\n- Toutes vos réservations\n- Tout votre historique\n\nTapez "SUPPRIMER" pour confirmer.')) {
                const confirmation = prompt('Tapez "SUPPRIMER" en majuscules pour confirmer :');
                if (confirmation === 'SUPPRIMER') {
                    window.location.href = 'delete_account.php';
                } else {
                    alert('Suppression annulée.');
                }
            }
        }

        // Validation du formulaire de mot de passe
        const passwordForm = document.querySelector('#security-edit form');
        if (passwordForm) {
            passwordForm.addEventListener('submit', function(e) {
                const newPassword = document.getElementById('new_password').value;
                const confirmPassword = document.getElementById('confirm_password').value;

                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    alert('Les mots de passe ne correspondent pas !');
                    return false;
                }
            });
        }
    </script>
</body>
</html>

<?php mysqli_close($conn); ?>
