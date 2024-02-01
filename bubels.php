<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bubels</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f0f0f0;
        }
        canvas {
            border: 2px solid #000;
        }
        #restartButton, #playButton {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            cursor: pointer;
        }
        #playButton {
            height: 100px;
            width: 100px;
        }
        #score {
            position: absolute;
            text-align:center;
            margin-bottom:380px;
            font-size: 20px;
            font-weight: bold;
        }
        #darkModeButton {
            position: absolute;
            top: 40px;
            left: 10px;
            font-size: 17px;
            font-weight: bold;
            cursor: pointer;
        }
        #loggedInUser {
        position: absolute;
        bottom: 10px;
        left: 10px;
        font-size: 16px;
        font-weight: bold;
        color: #000; /* Adjust the color as needed */
    }
    #leaderboard {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 18px;
        }
        #leaderboard table {
            border-collapse: collapse;
            width: 200px;
            border: solid 1px
        }
        #leaderboard th, #leaderboard td {
            border: 1px solid;
            padding: 8px;
            text-align: left;
        }
        #z{
            font: optional;
        }
        #pauseb{
            position: absolute;
            top:10px;
            left:10px;
            font-size: 15px;
            font-weight: bold;
        }

        #resumeb{
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            cursor: pointer;
        }
    </style>
</head>
<body>
    <canvas id="bubbleShooter" width="480" height="630"></canvas>
    <img id="restartButton" src="restart_button.png" alt="Restart">
    <img id="playButton" src="play_button.png" alt="Play">
    <div id="score">Score: 0</div>
    <button id="darkModeButton">Dark Mode</button>
    <button id=pauseb>Pause</button>
    <button id=resumeb>Resume</button>

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
$score = isset($_SESSION['score']) ? $_SESSION['score'] : 0;
// Update the user's score in the database using prepared statement
$sql = "UPDATE users SET score = ? WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $score, $loggedInUsername);
$stmt->execute();
$stmt->close();

$sqlUpdateBestScore = "UPDATE users SET bscore = ? WHERE score > bscore";
$stmtUpdateBestScore = $conn->prepare($sqlUpdateBestScore);
$stmtUpdateBestScore->bind_param("i", $score);
$stmtUpdateBestScore->execute();
$stmtUpdateBestScore->close();

// Zapisz wynik do pliku
$file = fopen("score.txt", "a");
fwrite($file, $loggedInUsername . ";" . $score . "\n");
fclose($file);

// Zamknij połączenie po zakończeniu operacji

$queryLeaderboard = "SELECT username, bscore FROM users ORDER BY bscore DESC LIMIT 10";
$statementLeaderboard = $conn->prepare($queryLeaderboard);
$statementLeaderboard->execute();
$statementLeaderboard->bind_result($username, $bscore);

// Pobierz wyniki i zapisz je do tablicy
$leaderboardData = array();
while ($statementLeaderboard->fetch()) {
    $leaderboardData[] = array('username' => $username, 'bscore' => $bscore);
}

// Zamknij połączenie po zakończeniu operacji na bazie danych
$statementLeaderboard->close();
$conn->close();

echo '<div id="loggedInUser">Logged in as: ' . $loggedInUsername . '&nbsp; <button onclick="logout()">Logout</button></div>';

// Sortuj dane od najwyższego do najniższego wyniku

usort($leaderboardData, function ($a, $b) {
    return $b['bscore'] - $a['bscore'];
});
?>

<div id="leaderboard">
    <h2>Leaderboard</h2>
    <table id="leaderboardTable">
        <tr>
            <th id='z'>#</th>
            <th id='z'>Username</th>
            <th id='z'>Score</th>
        </tr>
        <?php
        // Wyświetlenie danych z bazy danych
        $rank = 1;
        foreach ($leaderboardData as $row) {
            $username = $row['username'];
            $bscore = $row['bscore'];
            echo "<tr><td>$rank</td><td>$username</td><td>$bscore</td></tr>";
            $rank++;
        }
        ?>
    </table>
</div>

    <script>
        function logout() {
                console.log('Logout button clicked');
                // Redirect to the logout page (create a new logout.php file if not existing)
                window.location.href = 'logout.php';
            }

            function updateLeaderboard() {
                // Pobierz najnowsze dane z leaderboardu i zaktualizuj tabelę
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function () {
                    if (this.readyState == 4 && this.status == 200) {
                        document.getElementById("leaderboardTable").innerHTML = this.responseText;
                    }
                };
                xmlhttp.open("GET", "get_leaderboard.php", true);
                xmlhttp.send();
            }

            setInterval(updateLeaderboard, 5000);

        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('bubbleShooter');
            const ctx = canvas.getContext('2d');

            const outerBubbleRadius = 30;
            const innerBubbleRadius = 20;
            const maxBubbleSpeed = 5;
            const maxBubbles = 25;
            const shooterColor = '#f00';

            const playerBubbleSpeed = 3;

            let shooterX = canvas.width / 2;
            let shooterY = canvas.height - outerBubbleRadius;
            let bubbleX = shooterX;
            let bubbleY = shooterY;
            let bubbleColor = getRandomColor();
            let bubbleInFlight = false;

            const playerBubble = {
                x: shooterX,
                y: shooterY,
                radius: innerBubbleRadius
            };

            const bubbles = [];

            let gameOver = false;
            let gameStarted = false;
            let playButtonVisible = true;

            let frames = 0;
            const increaseRate = 240; // Zwiększenie co 4 sekundy (60fps * 4s)

            function getRandomColor() {
                const letters = '0123456789ABCDEF';
                let color = '#';
                for (let i = 0; i < 6; i++) {
                    color += letters[Math.floor(Math.random() * 16)];
                }
                return color;
            }

            function drawBubble(x, y, radius, color) {
                ctx.beginPath();
                ctx.arc(x, y, radius, 0, Math.PI * 2);
                ctx.fillStyle = color;
                ctx.fill();
                ctx.closePath();
            }

            function drawShooter() {
                drawBubble(shooterX, shooterY, outerBubbleRadius, shooterColor);
                drawBubble(playerBubble.x, playerBubble.y, playerBubble.radius, '#fff');
            }

            function draw() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                if (gameStarted) {
                    drawShooter();

                    for (const bubble of bubbles) {
                        drawBubble(bubble.x, bubble.y, bubble.radius, bubble.color);
                    }

                    if (bubbleInFlight) {
                        drawBubble(bubbleX, bubbleY, outerBubbleRadius, bubbleColor);
                        drawBubble(bubbleX, bubbleY, innerBubbleRadius, '#fff');
                    }
                }

                if (gameOver) {
                    ctx.font = "30px Arial";
                    ctx.fillStyle = "#000";
                    ctx.fillText(" ", canvas.width / 4, canvas.height / 2);
                    document.getElementById('restartButton').style.display = 'block';
                    playButtonVisible = false;
                }

                if (!gameStarted && playButtonVisible) {
                    const playButton = document.getElementById('playButton');
                    playButton.style.display = 'block';
                    playButton.addEventListener('click', startGame);
                } else if (!gameStarted) {
                    const restartButton = document.getElementById('restartButton');
                    restartButton.style.display = 'block';
                    restartButton.addEventListener('click', restartGame);
                }
            }

            function shootBubble(x, y) {
                if (!bubbleInFlight && !gameOver && gameStarted) {
                    bubbleX = shooterX;
                    bubbleY = shooterY;
                    bubbleColor = getRandomColor();
                    bubbleInFlight = true;
                }
            }

            function update() {
                frames++;

                if (isPaused) {
                    // Do nothing if the game is paused
                    return;
                }

                if (bubbleInFlight) {
                    const deltaX = bubbleX - shooterX;
                    const deltaY = bubbleY - shooterY;
                    const distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);
                    const unitVectorX = deltaX / distance;
                    const unitVectorY = deltaY / distance;

                    bubbleX -= unitVectorX * playerBubbleSpeed;
                    bubbleY -= unitVectorY * playerBubbleSpeed;

                    

                    // Sprawdź kolizję z krawędziami planszy
                    if (bubbleX < 0 || bubbleX > canvas.width || bubbleY < 0 || bubbleY > canvas.height) {
                        bubbleInFlight = false;
                    }

                    // Sprawdź kolizję z automatycznie generowanymi kulami
                        for (let i = bubbles.length - 1; i >= 0; i--) {
                        const otherBubble = bubbles[i];
                        const dX = bubbleX - otherBubble.x;
                        const dY = bubbleY - otherBubble.y;
                        const dist = Math.sqrt(dX * dX + dY * dY);

                        if (dist < outerBubbleRadius + otherBubble.radius && otherBubble !== playerBubble) {
                            bubbles.splice(i, 1);  // Usuń kulę, z którą nastąpiła kolizja
                            bubbleInFlight = false;
                        }
                    }
                }

                // Dodaj nowe kule automatycznie
                if (gameStarted && frames % increaseRate === 0 && bubbles.length < maxBubbles) {
                    const newBubble = {
                        x: Math.random() * canvas.width,
                        y: 0,
                        radius: Math.random() * (outerBubbleRadius - innerBubbleRadius) + innerBubbleRadius,
                        color: getRandomColor(),
                        speed: Math.random() * maxBubbleSpeed + 1
                    };

                    bubbles.push(newBubble);
                }

                // Przesuń automatycznie generowane kule
                for (const bubble of bubbles) {
                    bubble.y += bubble.speed;

                    // Sprawdź kolizję z dolną krawędzią
                    if (bubble.y > canvas.height) {
                        bubble.y = 0;
                        bubble.x = Math.random() * canvas.width;
                        bubble.color = getRandomColor();
                        bubble.radius = Math.random() * (outerBubbleRadius - innerBubbleRadius) + innerBubbleRadius;
                        bubble.speed = Math.random() * maxBubbleSpeed + 1;
                    }

                    // Sprawdź kolizję hitboxu gracza z automatycznie generowanymi kulami
                    const dX = playerBubble.x - bubble.x;
                    const dY = playerBubble.y - bubble.y;
                    const dist = Math.sqrt(dX * dX + dY * dY);

                    if (dist < outerBubbleRadius + playerBubble.radius) {
                        gameOver = true;
                        document.getElementById('restartButton').style.display = 'block';
                        gameStarted = false;
                        updateServerScore(score);
                        break;
                    }
                }
            }

            var isPaused = false;

            function porr() {
                if (isPaused===true){
                    console.log('s')
                    resumeGame();
                }
                else{
                    pauseGame();
                    console.log('b')
                }
            }
            
            function mouseMoveHandler(e) {
                const rect = canvas.getBoundingClientRect();

                // Ogranicz pozycję myszki do obszaru gry
                const mouseX = Math.max(0, Math.min(e.clientX - rect.left, canvas.width));
                const mouseY = Math.max(0, Math.min(e.clientY - rect.top, canvas.height));
            }
            function pauseGame() {
                isPaused = true;
                document.getElementById('pauseb').style.display = 'none';
                document.getElementById('resumeb').style.display = 'block';
                document.addEventListener('keydown', handleEscapeKey);
                document.removeEventListener('mousemove', mouseMoveHandler);
              
                // Add logic to pause the game (if needed)
            }

            function resumeGame() {
                isPaused = false;
                document.getElementById('pauseb').style.display = 'block';
                document.getElementById('resumeb').style.display = 'none';
                document.addEventListener('keydown', handleEscapeKey);
                document.addEventListener('mousemove', mouseMoveHandler);

                // Add logic to resume the game (if needed)
            }
            function handleEscapeKey(event) {
                console.log('ufu')
        if (event.key === 'Escape') {
            porr();
        }}
        function updateScore() {
                if (!gameOver) {
                    score += scoreIncreaseRate;
                    scoreElement.textContent = `Score: ${score}`;
                    // Update the score on the server using AJAX
                    updateServerScore(score);
                }
            }
            function updateScore() {
                if (!gameOver && !isPaused) {
                    score += scoreIncreaseRate;
                    scoreElement.textContent = `Score: ${score}`;
                    // Update the score on the server using AJAX
                    updateServerScore(score);
                }
            }

            document.getElementById('pauseb').addEventListener('click', pauseGame);
            document.addEventListener('keydown', handleEscapeKey);
            document.getElementById('resumeb').addEventListener('click', resumeGame);

            function gameLoop() {
                update();
                draw();
                requestAnimationFrame(gameLoop);
            }

            function mouseMoveHandler(e) {
                const rect = canvas.getBoundingClientRect();
                // Ogranicz pozycję myszki do obszaru gry
                shooterX = Math.max(0, Math.min(e.clientX - rect.left, canvas.width));
                shooterY = Math.max(0, Math.min(e.clientY - rect.top, canvas.height));
                // Aktualizuj pozycję hitboxu gracza
                playerBubble.x = shooterX;
                playerBubble.y = shooterY;
            }

            function mouseClickHandler(e) {
                if (gameOver) {
                    gameOver = false;
                    document.getElementById('restartButton').style.display = 'none';
                    bubbles.length = 0;
                    gameStarted = true;
                    score = 0;
                } else {
                    shootBubble(e.clientX, e.clientY);
                }
            }

            function stopScore() {
                clearInterval(scoreInterval);
            }

            function restartGame() {
            gameFinished = false;
            gameOver = false;
            document.getElementById('restartButton').style.display = 'none';
            bubbles.length = 0;
            gameStarted = true;

            // Przywróć hitbox gracza na środek
            playerBubble.x = canvas.width / 2;
            playerBubble.y = canvas.height - outerBubbleRadius;
            frames = 0;
            score = 0;
            scoreElement.textContent = 'Score: 0';
        }

            let score = 0;
            let scoreInterval;

            const scoreElement = document.getElementById('score');
            const scoreIncreaseRate = 10; // Zwiększenie co 1 sekundę

            let gameFinished = false;

            function updateServerScore(score) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'update_score.php', true);
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        console.log(xhr.responseText);
                    }
                };

                // Add information about the game over and username to data sent to the server
                const loggedInUsername = '<?php echo $loggedInUsername; ?>';
                const requestData = `score=${score}&gameOver=true&username=${loggedInUsername}`;
                xhr.send(requestData);
            }



            function startGame() {
                gameStarted = true;
                const playButton = document.getElementById('playButton');
                playButton.style.display = 'none';
                scoreInterval = setInterval(updateScore, 1000);
            }

            document.addEventListener('mousemove', mouseMoveHandler);
            document.addEventListener('click', mouseClickHandler);
            document.getElementById('restartButton').addEventListener('click', restartGame);

            // Rozpocznij grę przy ładowaniu strony
            gameLoop();{

            }

            let darkMode = false;

            function toggleDarkMode() {
                darkMode = !darkMode;

                const canvas = document.getElementById('bubbleShooter');
                const ctx = canvas.getContext('2d');
                const darkModeButton = document.getElementById('darkModeButton');

                if (darkMode) {
                    document.body.style.backgroundColor = '#000';
                    document.getElementById('score').style.color = '#fff';
                    document.getElementById('loggedInUser').style.color = '#fff';
                    document.getElementById('leaderboard').style.color = '#fff';
                    document.getElementById('leaderboard').style.borderColor = '#fff';
                    canvas.style.backgroundColor = '#000';  // Zmiana koloru tła canvas
                    canvas.style.borderColor = '#fff';  // Zmiana koloru obramowania canvas
                    for (const bubble of bubbles) {
                        bubble.color = '#fff';
                    }
                    darkModeButton.textContent = 'Light Mode';
                } else {
                    document.body.style.backgroundColor = '#f0f0f0';
                    document.getElementById('score').style.color = '#000';
                    document.getElementById('loggedInUser').style.color = '#000';
                    document.getElementById('leaderboard').style.color = '#000';
                    document.getElementById('leaderboard').style.borderColor = '#000'; 
                    canvas.style.backgroundColor = '#f0f0f0';  // Zmiana koloru tła canvas
                    canvas.style.borderColor = '#000';  // Zmiana koloru obramowania canvas
                    for (const bubble of bubbles) {
                        bubble.color = getRandomColor();
                    }
                    darkModeButton.textContent = 'Dark Mode';
                }
            }

            const darkModeButton = document.getElementById('darkModeButton');
            darkModeButton.addEventListener('click', toggleDarkMode);
            // Function to logout
        });
    </script>
    
</body>
</html>