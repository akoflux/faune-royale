document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById("btn-reservation");

    // Rediriger vers la page de réservation
    btn.addEventListener("click", () => {
        window.location.href = "./Reservation/reservation.php";
    });
});
