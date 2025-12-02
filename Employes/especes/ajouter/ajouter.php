<?php
session_start();
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/roles.php';
require_once __DIR__ . '/../../../connexion.php';

// Vérifier que l'utilisateur est connecté avec le bon rôle
require_role(['Employe', 'Chef_Equipe'], "Accès non autorisé");

$prenom = get_prenom();
$role = get_role();

// Vérifier la connexion à la base de données
if (!$conn) {
    die("Erreur de connexion à la base de données.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Espèce - Zoo Paradis</title>
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
            background: linear-gradient(135deg, #7c3aed, #a78bfa);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 30px rgba(124, 58, 237, 0.3);
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
        .form-group select,
        .form-group textarea {
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

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
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
    <a href="../Dashboard/dashboard.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Retour au dashboard
    </a>

    <div class="form-container">
        <div class="form-header">
            <div class="icon">
                <i class="fas fa-dna"></i>
            </div>
            <h1>Ajouter une Espèce</h1>
            <p>Enregistrez une nouvelle espèce dans votre zoo</p>
        </div>

        <form action="ajout.php" method="post">
            <div class="form-grid">
                <div class="form-group">
                    <label for="Nom">
                        <i class="fas fa-tag"></i>
                        Nom de l'espèce
                    </label>
                    <input type="text" id="Nom" name="Nom" placeholder="Ex: Lion d'Afrique" required>
                </div>

                <div class="form-group">
                    <label for="nourriture">
                        <i class="fas fa-utensils"></i>
                        Type de nourriture
                    </label>
                    <select name="nourriture" id="nourriture" required>
                        <option value="">Sélectionnez un type</option>
                        <option value="herbivores">Herbivores</option>
                        <option value="granivores">Granivores</option>
                        <option value="frugivores">Frugivores</option>
                        <option value="nectarivores">Nectarivores</option>
                        <option value="insectivores">Insectivores</option>
                        <option value="piscivores">Piscivores</option>
                        <option value="charognards">Charognards</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="Vie">
                        <i class="fas fa-hourglass-half"></i>
                        Durée de vie (années)
                    </label>
                    <input type="text" id="Vie" name="Vie" placeholder="Ex: 15" required>
                </div>

                <div class="form-group">
                    <label for="Eau">
                        <i class="fas fa-water"></i>
                        Type d'habitat
                    </label>
                    <select name="Eau" id="Eau" required>
                        <option value="">Sélectionnez un type</option>
                        <option value="aquatique">Espèce Aquatique</option>
                        <option value="amphibies">Espèce Amphibie</option>
                        <option value="non_aquatique">Espèce Non Aquatique</option>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label for="complementaire">
                        <i class="fas fa-info-circle"></i>
                        Information complémentaire
                    </label>
                    <textarea id="complementaire" name="complementaire" placeholder="Ajoutez des informations supplémentaires sur l'espèce..."></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-check"></i>
                    Enregistrer l'espèce
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
