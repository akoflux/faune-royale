<?php
@include("../../../connexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
    $id = intval($_POST["id"]); 

    $requete = "DELETE FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $requete);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Espèce supprimée avec succès !'); window.location.href = 'dashboard.php';</script>";
        } else {
            echo "<script>alert('Erreur lors de la suppression.'); window.location.href = 'dashboard.php';</script>";
        }
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($conn);
?>
