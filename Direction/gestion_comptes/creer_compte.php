<?php
session_start();
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/roles.php';
require_once __DIR__ . '/../../connexion.php';

// Seuls Directeur et Chef d'équipe peuvent accéder
require_role(['Directeur', 'Chef_Equipe'], "Vous n'avez pas la permission de créer des comptes.");

$role_utilisateur = get_role();
$user_id = get_user_id();

// Obtenir les rôles que l'utilisateur peut créer
$roles_assignables = get_roles_assignables();

if (empty($roles_assignables)) {
    $_SESSION['erreur'] = "Vous n'avez pas la permission de créer des comptes.";
    header('Location: ../main.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte - Zoo Paradis</title>
    <link rel="stylesheet" href="../../global-futuriste.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .page-container {
            min-height: 100vh;
            padding: 2rem;
        }

        .page-header {
            background: rgba(22, 33, 62, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text);
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .back-button:hover {
            background: rgba(0, 212, 255, 0.1);
            border-color: var(--primary);
            transform: translateX(-5px);
        }

        .form-card {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(22, 33, 62, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .form-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .form-header .icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 8px 30px rgba(0, 212, 255, 0.4);
        }

        .form-header .icon i {
            font-size: 2.5rem;
            color: white;
        }

        .role-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .role-option {
            position: relative;
        }

        .role-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .role-option label {
            display: block;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid var(--border);
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .role-option label i {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 0.8rem;
            display: block;
        }

        .role-option label h3 {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .role-option label p {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .role-option input:checked + label {
            background: rgba(0, 212, 255, 0.15);
            border-color: var(--primary);
            box-shadow: 0 0 30px rgba(0, 212, 255, 0.3);
            transform: scale(1.05);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper input,
        .input-wrapper select {
            width: 100%;
            padding: 1rem 1.2rem;
            padding-left: 3rem;
        }

        .input-wrapper i {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .role-selector {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-user-plus"></i> Créer un compte employé</h1>
                <p style="color: var(--text-muted); margin-top: 0.5rem;">
                    <?php echo $role_utilisateur === 'Directeur' ? 'Tous les rôles disponibles' : 'Employé, Vétérinaire, Bénévole'; ?>
                </p>
            </div>
            <a href="../main.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Retour
            </a>
        </div>

        <div class="form-card">
            <div class="form-header">
                <div class="icon">
                    <i class="fas fa-users-cog"></i>
                </div>
                <h2>Nouveau compte</h2>
                <p style="color: var(--text-muted);">Créez un compte pour un membre de l'équipe</p>
            </div>

            <form action="traitement_creation.php" method="POST" id="createAccountForm">
                <div class="form-group">
                    <label><i class="fas fa-user-tag"></i> Rôle</label>
                    <div class="role-selector">
                        <?php foreach ($roles_assignables as $role_key => $role_name): ?>
                            <div class="role-option">
                                <input type="radio" id="role_<?php echo $role_key; ?>" name="role" value="<?php echo $role_key; ?>" required>
                                <label for="role_<?php echo $role_key; ?>">
                                    <i class="fas fa-<?php
                                        echo match($role_key) {
                                            'Directeur' => 'crown',
                                            'Chef_Equipe' => 'user-tie',
                                            'Veterinaire' => 'stethoscope',
                                            'Employe' => 'user',
                                            'Benevole' => 'hands-helping',
                                            default => 'user'
                                        };
                                    ?>"></i>
                                    <h3><?php echo $role_name; ?></h3>
                                    <p><?php
                                        echo match($role_key) {
                                            'Directeur' => 'Toutes les permissions',
                                            'Chef_Equipe' => 'Gestion d\'équipe',
                                            'Veterinaire' => 'Soins des animaux',
                                            'Employe' => 'Gestion quotidienne',
                                            'Benevole' => 'Accès en lecture',
                                            default => ''
                                        };
                                    ?></p>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nom"><i class="fas fa-user"></i> Nom</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" id="nom" name="nom" placeholder="Nom" required minlength="2">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="prenom"><i class="fas fa-user"></i> Prénom</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" id="prenom" name="prenom" placeholder="Prénom" required minlength="2">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="email@exemple.fr" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="telephone"><i class="fas fa-phone"></i> Téléphone</label>
                    <div class="input-wrapper">
                        <i class="fas fa-phone"></i>
                        <input type="tel" id="telephone" name="telephone" placeholder="0612345678" pattern="[0-9]{10}" required>
                    </div>
                    <small>Format: 10 chiffres sans espaces</small>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> Mot de passe</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Min. 8 caractères" required minlength="8">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password"><i class="fas fa-lock"></i> Confirmation</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirmez" required minlength="8">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit" style="width: 100%; margin-top: 2rem;">
                    <i class="fas fa-check-circle"></i>
                    Créer le compte
                </button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('createAccountForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas');
                return false;
            }

            const telephone = document.getElementById('telephone').value;
            if (!/^[0-9]{10}$/.test(telephone)) {
                e.preventDefault();
                alert('Le téléphone doit contenir 10 chiffres');
                return false;
            }
        });
    </script>
</body>
</html>
