<?php
session_start();
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/roles.php';
require_once __DIR__ . '/../../../connexion.php';

// Vérifier que l'utilisateur est Directeur ou Chef d'équipe
require_role(['Directeur', 'Chef_Equipe'], "Accès non autorisé");

$prenom = get_prenom();
$role = get_role();

// Vérifier la connexion à la base de données
if (!$conn) {
    die("Erreur de connexion à la base de données.");
}

// Récupérer les espèces en utilisant mysqli
$query = mysqli_query($conn, "SELECT id, nom_race FROM especes ORDER BY nom_race");
$reqq = mysqli_query($conn, "SELECT Nom FROM enclos ORDER BY Nom");

if (!$query) {
    die("Erreur SQL : " . mysqli_error($conn));
}

$especes = [];
while ($row = mysqli_fetch_assoc($query)) {
    $especes[] = $row;
}

$enclos = [];
while ($row = mysqli_fetch_assoc($reqq)) {
    $enclos[] = $row;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Animal - Zoo Paradis</title>
    <link rel="stylesheet" href="../../../global-futuriste.css">
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
            z-index: 10;
        }

        .back-button:hover {
            background: rgba(0, 212, 255, 0.1);
            border-color: var(--primary);
            transform: translateX(-5px);
        }

        .form-container {
            width: 100%;
            max-width: 700px;
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

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
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
            margin-bottom: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group label i {
            color: var(--primary);
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

        .btn-reset {
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
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-reset:hover {
            background: rgba(255, 0, 110, 0.2);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .back-button {
                position: static;
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <a href="../dashboard/dashboard.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Retour au dashboard
    </a>

    <div class="form-container">
        <div class="form-header">
            <div class="icon">
                <i class="fas fa-paw"></i>
            </div>
            <h1>Ajouter un Animal</h1>
            <p>Enregistrez un nouvel animal dans votre zoo</p>
        </div>

        <form action="ajout.php" method="post">
            <div class="form-grid">
                <div class="form-group">
                    <label for="Nom">
                        <i class="fas fa-tag"></i>
                        Nom de l'animal
                    </label>
                    <input type="text" id="Nom" name="Nom" placeholder="Ex: Simba" required>
                </div>

                <div class="form-group">
                    <label for="espece">
                        <i class="fas fa-dna"></i>
                        Espèce
                    </label>
                    <select name="espece" id="espece" required>
                        <option value="">Sélectionnez une espèce</option>
                        <?php foreach ($especes as $espece): ?>
                            <option value="<?php echo htmlspecialchars($espece['nom_race']); ?>">
                                <?php echo htmlspecialchars($espece['nom_race']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="date_naissance">
                        <i class="fas fa-calendar"></i>
                        Date de naissance
                    </label>
                    <input type="date" id="date_naissance" name="date_naissance" required>
                </div>

                <div class="form-group">
                    <label for="Sexe">
                        <i class="fas fa-venus-mars"></i>
                        Sexe
                    </label>
                    <select name="Sexe" id="Sexe" required>
                        <option value="Mâle">Mâle</option>
                        <option value="Femelle">Femelle</option>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label for="Enclos">
                        <i class="fas fa-warehouse"></i>
                        Enclos
                    </label>
                    <select name="Enclos" id="Enclos">
                        <option value="">Aucun enclos (à définir plus tard)</option>
                        <?php foreach ($enclos as $enc): ?>
                            <option value="<?php echo htmlspecialchars($enc['Nom']); ?>">
                                <?php echo htmlspecialchars($enc['Nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-check"></i>
                    Enregistrer l'animal
                </button>
                <button type="reset" class="btn-reset">
                    <i class="fas fa-redo"></i>
                    Réinitialiser
                </button>
            </div>
        </form>
    </div>
</body>
</html>
