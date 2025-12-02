<?php
/**
 * Script de débogage pour tester la connexion
 * SUPPRIMER après utilisation (affiche des infos sensibles)
 */

require_once("../connexion.php");

echo "<h2>🔍 Debug de la connexion</h2>";
echo "<hr>";

// TEST 1 : Connexion à la base de données
echo "<h3>1. Test de connexion à la base de données</h3>";
if ($conn) {
    echo "✅ Connexion réussie à la base de données<br>";
} else {
    echo "❌ Erreur de connexion : " . mysqli_connect_error() . "<br>";
    die();
}

// TEST 2 : Afficher tous les utilisateurs
echo "<br><h3>2. Liste des utilisateurs dans la base</h3>";
$query = "SELECT id, role, prenom, email, mdp FROM users";
$result = mysqli_query($conn, $query);

if ($result) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Rôle</th><th>Prénom</th><th>Email</th><th>Mot de passe (hashé)</th><th>Longueur</th></tr>";
    
    while ($user = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . $user['role'] . "</td>";
        echo "<td>" . $user['prenom'] . "</td>";
        echo "<td>" . $user['email'] . "</td>";
        echo "<td>" . substr($user['mdp'], 0, 30) . "...</td>";
        echo "<td>" . strlen($user['mdp']) . " caractères</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Erreur : " . mysqli_error($conn);
}

// TEST 3 : Simuler une connexion
echo "<br><h3>3. Test de connexion manuel</h3>";
echo "<form method='post'>";
echo "Email: <input type='email' name='test_email' required><br><br>";
echo "Mot de passe: <input type='text' name='test_password' required><br><br>";
echo "<button type='submit' name='test_submit'>Tester la connexion</button>";
echo "</form>";

if (isset($_POST['test_submit'])) {
    echo "<hr>";
    $test_email = $_POST['test_email'];
    $test_password = $_POST['test_password'];
    
    echo "<h4>Tentative de connexion avec :</h4>";
    echo "Email : <strong>$test_email</strong><br>";
    echo "Mot de passe saisi : <strong>$test_password</strong><br><br>";
    
    // Recherche de l'utilisateur
    $stmt = mysqli_prepare($conn, "SELECT id, prenom, email, role, mdp FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $test_email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        echo "✅ Utilisateur trouvé dans la base<br>";
        echo "- ID : " . $user['id'] . "<br>";
        echo "- Prénom : " . $user['prenom'] . "<br>";
        echo "- Rôle : " . $user['role'] . "<br>";
        echo "- Hash en base : " . $user['mdp'] . "<br><br>";
        
        // Test du mot de passe
        echo "<h4>Test de vérification du mot de passe :</h4>";
        
        if (password_verify($test_password, $user['mdp'])) {
            echo "✅ <span style='color: green; font-weight: bold;'>SUCCÈS ! Le mot de passe correspond</span><br>";
            echo "👉 La connexion devrait fonctionner normalement<br>";
        } else {
            echo "❌ <span style='color: red; font-weight: bold;'>ÉCHEC ! Le mot de passe ne correspond pas</span><br><br>";
            
            echo "<strong>Diagnostic :</strong><br>";
            
            // Vérifier si c'est un hash valide
            $hash_info = password_get_info($user['mdp']);
            echo "- Type de hash : " . $hash_info['algoName'] . "<br>";
            
            if ($hash_info['algoName'] == 'unknown') {
                echo "⚠️ <span style='color: orange;'>Le mot de passe en base n'est PAS un hash valide !</span><br>";
                echo "Il semble être en clair : " . $user['mdp'] . "<br>";
                echo "Solution : Supprime ce compte et recrée-le via le formulaire d'inscription<br>";
            } else {
                echo "- Le hash est valide, mais le mot de passe saisi ne correspond pas<br>";
                echo "- Vérifie que tu saisis exactement le même mot de passe (attention aux espaces, majuscules, etc.)<br>";
            }
        }
        
    } else {
        echo "❌ <span style='color: red;'>Aucun utilisateur trouvé avec cet email</span><br>";
    }
    
    mysqli_stmt_close($stmt);
}

echo "<br><hr>";
echo "<p style='color: red; font-weight: bold;'>⚠️ SUPPRIME CE FICHIER après utilisation (il affiche des infos sensibles) !</p>";

mysqli_close($conn);
?>