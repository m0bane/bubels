<?php
session_start();

if (isset($_POST['score']) && isset($_POST['gameOver'])) {
    $score = $_POST['score'];

    // Sprawdź, czy to jest game over
    if ($_POST['gameOver'] === 'true') {
        // Odczytaj poprzedni najwyższy wynik
        $prevHighestScore = isset($_SESSION['prev_highest_score']) ? $_SESSION['prev_highest_score'] : 0;

        // Sprawdź, czy aktualny wynik jest wyższy niż poprzedni najwyższy wynik
        if ($score > $prevHighestScore) {
            // Zapisz aktualny wynik jako nowy najwyższy wynik
            $_SESSION['prev_highest_score'] = $score;

            // Usuń poprzedni najwyższy wynik z pliku
            file_put_contents("highest_score.txt", $_SESSION['username'] . ";" . $prevHighestScore . "\n");

            // Zapisz nowy najwyższy wynik do pliku
            file_put_contents("highest_score.txt", $_SESSION['username'] . ";" . $score . "\n", FILE_APPEND);

            echo 'Highest score updated successfully.';
        } else {
            echo 'Score not higher than the previous highest score.';
        }
    } else {
        echo 'Invalid request.';
    }
} else {
    echo 'Invalid request.';
}
?>
