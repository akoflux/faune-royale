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
    <link rel="stylesheet" href="../../global-nature-zoo.css">
    <link rel="stylesheet" href="../../dashboard-nature.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #faf8f3 0%, #f4e7d7 100%);
            min-height: 100vh;
            padding: 2rem;
        }

        .page-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .page-header {
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

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-green), var(--accent-orange), var(--accent-gold));
        }

        .page-header h1 {
            color: var(--primary-green);
            font-size: 1.8rem;
            margin: 0;
        }

        .back-button {
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

        .back-button:hover {
            background: var(--primary-light);
            color: white;
        }

        .form-card {
            background: white;
            border: 2px solid var(--border-light);
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
        }

        .form-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-green), var(--accent-orange));
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-header .icon {
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

        .form-header .icon i {
            font-size: 2.5rem;
            color: white;
        }

        .form-header h2 {
            color: var(--primary-green);
            margin-bottom: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            color: var(--primary-green);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--accent-orange);
        }

        .input-wrapper input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.8rem;
            border: 2px solid var(--border-light);
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: var(--transition);
        }

        .input-wrapper input:focus {
            outline: none;
            border-color: var(--primary-green);
            box-shadow: 0 0 0 3px rgba(74, 124, 44, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .role-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .role-option input[type="radio"] {
            display: none;
        }

        .role-option label {
            display: block;
            padding: 1.5rem;
            background: var(--bg-light);
            border: 2px solid var(--border-light);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
        }

        .role-option label i {
            font-size: 2rem;
            color: var(--accent-orange);
            margin-bottom: 0.5rem;
        }

        .role-option label h3 {
            color: var(--primary-green);
            font-size: 1rem;
            margin: 0.5rem 0;
        }

        .role-option label p {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin: 0;
        }

        .role-option input[type="radio"]:checked + label {
            background: white;
            border-color: var(--primary-green);
            box-shadow: 0 0 0 3px rgba(74, 124, 44, 0.1);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary-green), var(--primary-light));
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(74, 124, 44, 0.3);
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
