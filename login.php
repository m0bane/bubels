<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script>
        function validateForm() {
            var username = document.getElementById('username').value;
            var password = document.getElementById('password').value;

            if (username.length > 20) {
                alert("Username should be at most 20 characters long.");
                return false;
            }

            // Check password length only if the username is provided
            if (username && password && (password.length < 10 || password.length > 20)) {
                alert("Password should be between 10 and 20 characters long.");
                return false;
            }

            return true;
        }

        function togglePassword() {
            var passwordField = document.getElementById('password');
            var toggleButton = document.getElementById('toggleButton');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleButton.textContent = 'Hide Password';
            } else {
                passwordField.type = 'password';
                toggleButton.textContent = 'Show Password';
            }
        }

        function redirectToLoginPage() {
            // Redirect to the bubels.php page
            window.location.href = 'bubels.php';
        }
    </script>
    <?php
    session_start();
        // Check if the form has been submitted
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Get data from the form
            $username = $_POST['username'];
            $passwordInput = $_POST['password'];

            // Connect to the database
            $conn = new mysqli('localhost', 'root', '', 'bubels_reg_log');

            // Check the connection
            if ($conn->connect_error) {
                die("Database connection error: " . $conn->connect_error);
            }

            // Query the database to retrieve the password
            $query = "SELECT password FROM users WHERE username='$username'";
            $result = $conn->query($query);

            // Check the query result
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $dbPassword = $row['password'];

                // Check if passwords match
                if ($passwordInput === $dbPassword) {
                    echo "Correct password";
                    $_SESSION['username'] = $username;
                    // Show the 'Play' button after successful login
                    echo '&nbsp;<input type="button" id="playButton" value="Play" onclick="redirectToLoginPage()">';
                } else {
                    echo "Incorrect password";
                }
            } else {
                // No user found with the provided username
                echo "User not found";
            }

            // Close the database connection
            $conn->close();
        }
    ?>
</head>
<body>
    <h2>Login Form</h2>
    <form method="post" action="login.php" onsubmit="return validateForm()">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required maxlength="20"><br>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" minlength="10" maxlength="20">
        <button type="button" id="toggleButton" onclick="togglePassword()">Show Password</button><br>
        <input type="submit" value="Login">
    </form>
    <p>Don't have an account? <a href="register.php">Register</a></p>
</body>
</html>
