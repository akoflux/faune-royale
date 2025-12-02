<?php
/**
 * Système de gestion des permissions par rôle
 * Définit les permissions pour chaque rôle dans le système
 */

require_once __DIR__ . '/../connexion.php';
require_once __DIR__ . '/auth.php';

/**
 * Définition des rôles dans le système (hiérarchie)
 */
define('ROLE_CLIENT', 'Client');
define('ROLE_BENEVOLE', 'Benevole');
define('ROLE_EMPLOYE', 'Employe');
define('ROLE_VETERINAIRE', 'Veterinaire');
define('ROLE_CHEF_EQUIPE', 'Chef_Equipe');
define('ROLE_DIRECTEUR', 'Directeur');

/**
 * Vérifie si un utilisateur a la permission pour une action spécifique
 * @param string $module Le module (animaux, especes, employes, etc.)
 * @param string $action L'action (lire, creer, modifier, supprimer)
 * @return bool
 */
function a_permission($module, $action) {
    if (!est_connecte()) {
        return false;
    }

    global $conn;
    $role = get_role();

    // Le directeur a toutes les permissions
    if ($role === ROLE_DIRECTEUR) {
        return true;
    }

    // Mapping des actions vers les colonnes de la base de données
    $colonnes_actions = [
        'lire' => 'peut_lire',
        'creer' => 'peut_creer',
        'modifier' => 'peut_modifier',
        'supprimer' => 'peut_supprimer'
    ];

    if (!isset($colonnes_actions[$action])) {
        return false;
    }

    $colonne = $colonnes_actions[$action];

    $stmt = $conn->prepare("SELECT $colonne FROM permissions WHERE role = ? AND module = ?");
    $stmt->bind_param("ss", $role, $module);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $permission = $row[$colonne];
        $stmt->close();
        return $permission == 1;
    }

    $stmt->close();
    return false;
}

/**
 * Vérifie si l'utilisateur peut créer des comptes utilisateurs
 * @param string $role_a_creer Le rôle du compte à créer
 * @return bool
 */
function peut_creer_compte($role_a_creer) {
    if (!est_connecte()) {
        return false;
    }

    $role_utilisateur = get_role();

    // Le directeur peut créer tous les types de comptes
    if ($role_utilisateur === ROLE_DIRECTEUR) {
        return true;
    }

    // Le chef d'équipe peut créer des comptes sauf Chef d'équipe et Directeur
    if ($role_utilisateur === ROLE_CHEF_EQUIPE) {
        return in_array($role_a_creer, [ROLE_EMPLOYE, ROLE_VETERINAIRE, ROLE_BENEVOLE]);
    }

    return false;
}

/**
 * Vérifie si l'utilisateur peut modifier/supprimer un utilisateur
 * @param int $user_id ID de l'utilisateur à modifier/supprimer
 * @return bool
 */
function peut_gerer_utilisateur($user_id) {
    if (!est_connecte()) {
        return false;
    }

    global $conn;
    $role_utilisateur = get_role();

    // Le directeur peut tout gérer
    if ($role_utilisateur === ROLE_DIRECTEUR) {
        return true;
    }

    // Le chef d'équipe peut gérer les comptes qu'il a créés (sauf chefs d'équipe)
    if ($role_utilisateur === ROLE_CHEF_EQUIPE) {
        $stmt = $conn->prepare("SELECT role, cree_par FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $role_cible = $row['role'];
            $cree_par = $row['cree_par'];
            $stmt->close();

            // Ne peut pas gérer les chefs d'équipe et directeurs
            if (in_array($role_cible, [ROLE_CHEF_EQUIPE, ROLE_DIRECTEUR])) {
                return false;
            }

            // Peut gérer si c'est lui qui a créé le compte
            return $cree_par == get_user_id();
        }

        $stmt->close();
    }

    return false;
}

/**
 * Retourne la liste des rôles que l'utilisateur peut assigner
 * @return array
 */
function get_roles_assignables() {
    if (!est_connecte()) {
        return [];
    }

    $role_utilisateur = get_role();

    if ($role_utilisateur === ROLE_DIRECTEUR) {
        return [
            ROLE_DIRECTEUR => 'Directeur',
            ROLE_CHEF_EQUIPE => 'Chef d\'équipe',
            ROLE_VETERINAIRE => 'Vétérinaire',
            ROLE_EMPLOYE => 'Employé',
            ROLE_BENEVOLE => 'Bénévole'
        ];
    }

    if ($role_utilisateur === ROLE_CHEF_EQUIPE) {
        return [
            ROLE_VETERINAIRE => 'Vétérinaire',
            ROLE_EMPLOYE => 'Employé',
            ROLE_BENEVOLE => 'Bénévole'
        ];
    }

    return [];
}

/**
 * Retourne un nom de rôle en français
 * @param string $role
 * @return string
 */
function get_nom_role($role) {
    $noms = [
        ROLE_CLIENT => 'Client',
        ROLE_BENEVOLE => 'Bénévole',
        ROLE_EMPLOYE => 'Employé',
        ROLE_VETERINAIRE => 'Vétérinaire',
        ROLE_CHEF_EQUIPE => 'Chef d\'équipe',
        ROLE_DIRECTEUR => 'Directeur'
    ];

    return $noms[$role] ?? $role;
}

/**
 * Vérifie si l'utilisateur a accès à un module spécifique
 * @param string $module
 * @return bool
 */
function a_acces_module($module) {
    return a_permission($module, 'lire');
}

/**
 * Retourne la liste des modules accessibles pour l'utilisateur
 * @return array
 */
function get_modules_accessibles() {
    if (!est_connecte()) {
        return [];
    }

    global $conn;
    $role = get_role();

    $stmt = $conn->prepare("SELECT module FROM permissions WHERE role = ? AND peut_lire = 1");
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result();

    $modules = [];
    while ($row = $result->fetch_assoc()) {
        $modules[] = $row['module'];
    }

    $stmt->close();
    return $modules;
}

/**
 * Affiche un message d'erreur si l'utilisateur n'a pas la permission
 * @param string $module
 * @param string $action
 */
function require_permission($module, $action) {
    if (!a_permission($module, $action)) {
        $_SESSION['erreur_permission'] = "Vous n'avez pas la permission de " . $action . " dans le module " . $module . ".";
        rediriger_selon_role();
        exit();
    }
}

/**
 * Vérifie si l'utilisateur est en mode lecture seule
 * @return bool
 */
function est_lecture_seule() {
    $role = get_role();
    return $role === ROLE_BENEVOLE;
}

/**
 * Génère le menu de navigation selon les permissions de l'utilisateur
 * @return array
 */
function get_menu_navigation() {
    if (!est_connecte()) {
        return [];
    }

    $role = get_role();
    $menu = [];

    // Menu pour les clients
    if ($role === ROLE_CLIENT) {
        return [
            ['titre' => 'Mes réservations', 'url' => '/ProjetZoo/Client/reservations.php', 'icone' => 'calendar'],
            ['titre' => 'Nouvelle réservation', 'url' => '/ProjetZoo/Reservation/reservation.php', 'icone' => 'plus'],
            ['titre' => 'Mon profil', 'url' => '/ProjetZoo/Client/profil.php', 'icone' => 'user']
        ];
    }

    // Menu pour le personnel
    if (a_acces_module('animaux')) {
        $menu[] = ['titre' => 'Animaux', 'url' => '/ProjetZoo/Direction/animaux/dashboard/dashboard.php', 'icone' => 'paw'];
    }

    if (a_acces_module('especes')) {
        $menu[] = ['titre' => 'Espèces', 'url' => '/ProjetZoo/Direction/especes/Dashboard/dashboard.php', 'icone' => 'dna'];
    }

    if (a_acces_module('employes')) {
        $menu[] = ['titre' => 'Employés', 'url' => '/ProjetZoo/Direction/employes/Dashboard/dashboard.php', 'icone' => 'users'];
    }

    if ($role === ROLE_DIRECTEUR || $role === ROLE_CHEF_EQUIPE) {
        $menu[] = ['titre' => 'Utilisateurs', 'url' => '/ProjetZoo/Direction/user/dashboard/dashboard.php', 'icone' => 'user-cog'];
    }

    if (a_acces_module('reservations') && in_array($role, [ROLE_DIRECTEUR, ROLE_CHEF_EQUIPE])) {
        $menu[] = ['titre' => 'Réservations', 'url' => '/ProjetZoo/Direction/reservations/dashboard.php', 'icone' => 'calendar-check'];
    }

    return $menu;
}
