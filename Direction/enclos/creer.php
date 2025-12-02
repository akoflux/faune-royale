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
    <link rel="stylesheet" href="../../global-futuriste.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 2rem;
        }

        .back-button {
            position: fixed;
            top: 2rem;
            left: 2rem;
            padding: 1rem 1.5rem;
            background: rgba(22, 33, 62, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background: rgba(0, 212, 255, 0.1);
            border-color: var(--primary);
            transform: translateX(-5px);
        }

        .form-container {
            width: 100%;
            max-width: 600px;
            background: rgba(22, 33, 62, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 25px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .form-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .form-header .icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--success), #00ff88);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 30px rgba(6, 255, 165, 0.3);
        }

        .form-header .icon i {
            font-size: 2.5rem;
            color: white;
        }

        .form-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .form-header p {
            color: var(--text-muted);
        }

        .form-group {
            margin-bottom: 2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group label i {
            color: var(--primary);
        }

        .form-group input {
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

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 20px rgba(0, 212, 255, 0.3);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-submit {
            flex: 1;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--success), #00ff88);
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
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text);
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
            background: rgba(255, 0, 110, 0.1);
            border-color: var(--danger);
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .alert i {
            font-size: 1.5rem;
        }

        .alert-success {
            background: rgba(6, 255, 165, 0.1);
            border: 1px solid rgba(6, 255, 165, 0.3);
            color: var(--success);
        }

        .alert-error {
            background: rgba(255, 0, 110, 0.1);
            border: 1px solid rgba(255, 0, 110, 0.3);
            color: var(--danger);
        }

        .help-text {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .help-text i {
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <a href="dashboard.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Retour aux enclos
    </a>

    <div class="form-container">
        <div class="form-header">
            <div class="icon">
                <i class="fas fa-warehouse"></i>
            </div>
            <h1>Créer un Enclos</h1>
            <p>Ajoutez un nouvel enclos pour vos animaux</p>
        </div>

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
</body>
</html>

<?php mysqli_close($conn); ?>
