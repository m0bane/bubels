<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bubels_reg_log";

// Utwórz połączenie
$conn = new mysqli($servername, $username, $password, $dbname);

// Sprawdź połączenie
if ($conn->connect_error) {
    die("Błąd połączenia z bazą danych: " . $conn->connect_error);
}

// Tutaj możesz wykonywać operacje na bazie danych, na przykład zapisywać, pobierać lub aktualizować dane.

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to the login page if not logged in
    header('Location: login.php');
    exit();
}

$loggedInUsername = $_SESSION['username'];
$score = isset($_POST['score']) ? intval($_POST['score']) : 0;

// Update the user's score in the database
$sql = "UPDATE users SET score = ? WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $score, $loggedInUsername);
$stmt->execute();
$stmt->close();


// Zamknij połączenie po zakończeniu operacji
$conn->close();
?>