<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../connexion.php';

// Vérifier que l'utilisateur est connecté
require_connexion();

// Récupérer les tarifs depuis la base de données
$tarifs = [];
$req_tarifs = "SELECT * FROM tarifs WHERE actif = 1 ORDER BY forfait, type";
$result_tarifs = mysqli_query($conn, $req_tarifs);

while ($row = mysqli_fetch_assoc($result_tarifs)) {
    $tarifs[$row['forfait']][$row['type']] = $row['prix'];
}

// Récupérer les informations de l'utilisateur
$user_id = get_user_id();
$user_email = get_email();
$user_prenom = get_prenom();

$stmt = $conn->prepare("SELECT nom, telephone FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$user_nom = $user_data['nom'] ?? '';
$user_tel = $user_data['telephone'] ?? '';
$stmt->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - Zoo Paradis</title>
    <link rel="stylesheet" href="reservation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="reservation-container">
        <!-- Header -->
        <header class="reservation-header">
            <div class="header-content">
                <a href="/ProjetZoo/index.html" class="back-button">
                    <i class="fas fa-arrow-left"></i> Retour à l'accueil
                </a>
                <h1><i class="fas fa-calendar-alt"></i> Réserver votre visite</h1>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($user_prenom); ?></span>
                </div>
            </div>
        </header>

        <div class="main-content">
            <!-- Section gauche : Formulaire -->
            <div class="form-section">
                <div class="form-card">
                    <div class="card-header">
                        <i class="fas fa-ticket-alt"></i>
                        <h2>Informations de réservation</h2>
                    </div>

                    <form id="reservationForm" method="POST" action="traitement_reservation.php">
                        <!-- Informations personnelles -->
                        <div class="form-group">
                            <label for="nom">
                                <i class="fas fa-user"></i> Nom
                            </label>
                            <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($user_nom); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="prenom">
                                <i class="fas fa-user"></i> Prénom
                            </label>
                            <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user_prenom); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Email
                            </label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_email); ?>" required readonly>
                        </div>

                        <div class="form-group">
                            <label for="telephone">
                                <i class="fas fa-phone"></i> Téléphone
                            </label>
                            <input type="tel" id="telephone" name="telephone" value="<?php echo htmlspecialchars($user_tel); ?>" pattern="[0-9]{10}" placeholder="0612345678" required>
                            <small>Format: 10 chiffres sans espaces</small>
                        </div>

                        <!-- Date de visite -->
                        <div class="form-group">
                            <label for="date_visite">
                                <i class="fas fa-calendar"></i> Date de visite
                            </label>
                            <input type="date" id="date_visite" name="date_visite" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                            <div id="disponibilite" class="disponibilite"></div>
                        </div>

                        <!-- Choix du forfait -->
                        <div class="form-group">
                            <label>
                                <i class="fas fa-star"></i> Forfait
                            </label>
                            <div class="forfait-options">
                                <div class="forfait-card" data-forfait="demi_journee">
                                    <input type="radio" id="demi_journee" name="forfait" value="demi_journee" required>
                                    <label for="demi_journee">
                                        <i class="fas fa-sun"></i>
                                        <h3>Demi-journée</h3>
                                        <p>Arrivée à 13h</p>
                                        <p class="prix">
                                            Adulte: <?php echo $tarifs['demi_journee']['adulte']; ?>€<br>
                                            Enfant: <?php echo $tarifs['demi_journee']['enfant']; ?>€
                                        </p>
                                    </label>
                                </div>

                                <div class="forfait-card" data-forfait="1_jour">
                                    <input type="radio" id="1_jour" name="forfait" value="1_jour" required>
                                    <label for="1_jour">
                                        <i class="fas fa-clock"></i>
                                        <h3>1 Jour</h3>
                                        <p>9h - 18h</p>
                                        <p class="prix">
                                            Adulte: <?php echo $tarifs['1_jour']['adulte']; ?>€<br>
                                            Enfant: <?php echo $tarifs['1_jour']['enfant']; ?>€
                                        </p>
                                    </label>
                                </div>

                                <div class="forfait-card popular" data-forfait="2_jours_1_nuit">
                                    <div class="badge">Populaire</div>
                                    <input type="radio" id="2_jours_1_nuit" name="forfait" value="2_jours_1_nuit" required>
                                    <label for="2_jours_1_nuit">
                                        <i class="fas fa-moon"></i>
                                        <h3>2 Jours + 1 Nuit</h3>
                                        <p>Hébergement inclus</p>
                                        <p class="prix">
                                            Adulte: <?php echo $tarifs['2_jours_1_nuit']['adulte']; ?>€<br>
                                            Enfant: <?php echo $tarifs['2_jours_1_nuit']['enfant']; ?>€
                                        </p>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Nombre de visiteurs -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nb_adultes">
                                    <i class="fas fa-male"></i> Adultes
                                </label>
                                <div class="number-input">
                                    <button type="button" class="btn-minus" data-target="nb_adultes">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" id="nb_adultes" name="nb_adultes" min="0" max="20" value="1" required>
                                    <button type="button" class="btn-plus" data-target="nb_adultes">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="nb_enfants">
                                    <i class="fas fa-child"></i> Enfants
                                </label>
                                <div class="number-input">
                                    <button type="button" class="btn-minus" data-target="nb_enfants">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" id="nb_enfants" name="nb_enfants" min="0" max="20" value="0" required>
                                    <button type="button" class="btn-plus" data-target="nb_enfants">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Commentaire optionnel -->
                        <div class="form-group">
                            <label for="commentaire">
                                <i class="fas fa-comment"></i> Commentaire (optionnel)
                            </label>
                            <textarea id="commentaire" name="commentaire" rows="4" placeholder="Demandes spéciales, allergies, informations complémentaires..."></textarea>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fas fa-check-circle"></i>
                            Confirmer la réservation
                        </button>
                    </form>
                </div>
            </div>

            <!-- Section droite : Récapitulatif -->
            <div class="summary-section">
                <div class="summary-card">
                    <div class="card-header">
                        <i class="fas fa-receipt"></i>
                        <h2>Récapitulatif</h2>
                    </div>

                    <div class="summary-content">
                        <div class="summary-item">
                            <span class="label"><i class="fas fa-calendar"></i> Date</span>
                            <span class="value" id="summary-date">-</span>
                        </div>

                        <div class="summary-item">
                            <span class="label"><i class="fas fa-star"></i> Forfait</span>
                            <span class="value" id="summary-forfait">-</span>
                        </div>

                        <div class="summary-item">
                            <span class="label"><i class="fas fa-users"></i> Visiteurs</span>
                            <span class="value" id="summary-visiteurs">-</span>
                        </div>

                        <div class="divider"></div>

                        <div class="summary-item">
                            <span class="label">Adultes</span>
                            <span class="value" id="summary-adultes-detail">-</span>
                        </div>

                        <div class="summary-item">
                            <span class="label">Enfants</span>
                            <span class="value" id="summary-enfants-detail">-</span>
                        </div>

                        <div class="divider"></div>

                        <div class="summary-total">
                            <span class="label">Total</span>
                            <span class="value" id="summary-total">0.00 €</span>
                        </div>

                        <div class="info-box">
                            <i class="fas fa-info-circle"></i>
                            <p>Capacité restante: <strong id="places-restantes">-</strong> places</p>
                        </div>
                    </div>
                </div>

                <!-- Informations pratiques -->
                <div class="info-card">
                    <h3><i class="fas fa-lightbulb"></i> Informations pratiques</h3>
                    <ul>
                        <li><i class="fas fa-check"></i> Réservation confirmée par email</li>
                        <li><i class="fas fa-check"></i> Annulation gratuite jusqu'à 48h avant</li>
                        <li><i class="fas fa-check"></i> Présentation du billet électronique à l'entrée</li>
                        <li><i class="fas fa-check"></i> Parking gratuit pour tous les visiteurs</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tarifs en JSON depuis PHP
        const tarifs = <?php echo json_encode($tarifs); ?>;

        // Gestion des boutons +/-
        document.querySelectorAll('.btn-plus, .btn-minus').forEach(btn => {
            btn.addEventListener('click', function() {
                const target = this.dataset.target;
                const input = document.getElementById(target);
                const currentValue = parseInt(input.value) || 0;
                const max = parseInt(input.max);
                const min = parseInt(input.min);

                if (this.classList.contains('btn-plus') && currentValue < max) {
                    input.value = currentValue + 1;
                } else if (this.classList.contains('btn-minus') && currentValue > min) {
                    input.value = currentValue - 1;
                }

                input.dispatchEvent(new Event('input'));
            });
        });

        // Mise à jour du récapitulatif
        function updateSummary() {
            const date = document.getElementById('date_visite').value;
            const forfait = document.querySelector('input[name="forfait"]:checked');
            const nbAdultes = parseInt(document.getElementById('nb_adultes').value) || 0;
            const nbEnfants = parseInt(document.getElementById('nb_enfants').value) || 0;

            // Date
            if (date) {
                const dateObj = new Date(date);
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                document.getElementById('summary-date').textContent = dateObj.toLocaleDateString('fr-FR', options);
            }

            // Forfait
            let forfaitName = '-';
            if (forfait) {
                const forfaitLabels = {
                    'demi_journee': 'Demi-journée (13h)',
                    '1_jour': '1 Jour (9h-18h)',
                    '2_jours_1_nuit': '2 Jours + 1 Nuit'
                };
                forfaitName = forfaitLabels[forfait.value];
            }
            document.getElementById('summary-forfait').textContent = forfaitName;

            // Visiteurs
            const totalVisiteurs = nbAdultes + nbEnfants;
            document.getElementById('summary-visiteurs').textContent = totalVisiteurs + ' personne' + (totalVisiteurs > 1 ? 's' : '');

            // Calcul du prix
            let total = 0;
            if (forfait && tarifs[forfait.value]) {
                const prixAdulte = parseFloat(tarifs[forfait.value].adulte) || 0;
                const prixEnfant = parseFloat(tarifs[forfait.value].enfant) || 0;

                const totalAdultes = nbAdultes * prixAdulte;
                const totalEnfants = nbEnfants * prixEnfant;

                document.getElementById('summary-adultes-detail').textContent =
                    nbAdultes + ' × ' + prixAdulte.toFixed(2) + '€ = ' + totalAdultes.toFixed(2) + '€';
                document.getElementById('summary-enfants-detail').textContent =
                    nbEnfants + ' × ' + prixEnfant.toFixed(2) + '€ = ' + totalEnfants.toFixed(2) + '€';

                total = totalAdultes + totalEnfants;
            }

            document.getElementById('summary-total').textContent = total.toFixed(2) + ' €';
        }

        // Vérification de la disponibilité
        async function checkDisponibilite() {
            const date = document.getElementById('date_visite').value;
            const nbAdultes = parseInt(document.getElementById('nb_adultes').value) || 0;
            const nbEnfants = parseInt(document.getElementById('nb_enfants').value) || 0;
            const totalPersonnes = nbAdultes + nbEnfants;

            if (!date) return;

            try {
                const response = await fetch('check_disponibilite.php?date=' + date);
                const data = await response.json();

                const placesRestantes = data.places_restantes;
                const disponibiliteDiv = document.getElementById('disponibilite');
                const placesRestantesSpan = document.getElementById('places-restantes');

                placesRestantesSpan.textContent = placesRestantes;

                if (placesRestantes >= totalPersonnes) {
                    disponibiliteDiv.innerHTML = '<i class="fas fa-check-circle"></i> Places disponibles';
                    disponibiliteDiv.className = 'disponibilite disponible';
                } else if (placesRestantes > 0) {
                    disponibiliteDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Plus que ' + placesRestantes + ' place(s) disponible(s)';
                    disponibiliteDiv.className = 'disponibilite limite';
                } else {
                    disponibiliteDiv.innerHTML = '<i class="fas fa-times-circle"></i> Complet pour cette date';
                    disponibiliteDiv.className = 'disponibilite complet';
                }
            } catch (error) {
                console.error('Erreur lors de la vérification:', error);
            }
        }

        // Event listeners
        document.getElementById('date_visite').addEventListener('change', () => {
            updateSummary();
            checkDisponibilite();
        });

        document.querySelectorAll('input[name="forfait"]').forEach(radio => {
            radio.addEventListener('change', updateSummary);
        });

        document.getElementById('nb_adultes').addEventListener('input', () => {
            updateSummary();
            checkDisponibilite();
        });

        document.getElementById('nb_enfants').addEventListener('input', () => {
            updateSummary();
            checkDisponibilite();
        });

        // Validation du formulaire
        document.getElementById('reservationForm').addEventListener('submit', function(e) {
            const nbAdultes = parseInt(document.getElementById('nb_adultes').value) || 0;
            const nbEnfants = parseInt(document.getElementById('nb_enfants').value) || 0;

            if (nbAdultes === 0 && nbEnfants === 0) {
                e.preventDefault();
                alert('Veuillez sélectionner au moins un visiteur (adulte ou enfant).');
                return false;
            }
        });
    </script>
</body>
</html>
