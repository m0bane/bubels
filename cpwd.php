<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="booba.css">
    <script>
        function validateForm() {
            var username = document.getElementById('username').value;
            var newPassword = document.getElementById('newPassword').value;
            var confirmPassword = document.getElementById('confirmPassword').value;

            if (username.trim() === "") {
                alert("Username cannot be empty.");
                return false;
            }

            if (newPassword.length < 5 || newPassword.length > 15) {
                alert("New password should be between 5 and 15 characters long.");
                return false;
            }

            if (newPassword !== confirmPassword) {
                alert("New password and confirm password do not match.");
                return false;
            }

            return true;
        }

        function togglePassword() {
            var newPasswordField = document.getElementById('newPassword');
            var confirmPasswordField = document.getElementById('confirmPassword');

            if (newPasswordField.type === 'password') {
                newPasswordField.type = 'text';
                confirmPasswordField.type = 'text';
            } else {
                newPasswordField.type = 'password';
                confirmPasswordField.type = 'password';
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
        $username = isset($_POST['username']) ? $_POST['username'] : '';
        $newPasswordInput = $_POST['newPassword'];

        // Check if username is provided
        if (empty($username)) {
            echo "Error: Username not provided.";
            exit();
        }

        // Connect to the database
        $conn = new mysqli('localhost', 'root', '', 'bubels_reg_log');

        // Check the connection
        if ($conn->connect_error) {
            die("Database connection error: " . $conn->connect_error);
        }

        // Prepare and bind the update query
        $updateQuery = $conn->prepare("UPDATE users SET password=? WHERE username=?");
        $updateQuery->bind_param("ss", $newPasswordInput, $username);

        // Execute the update query
        if ($updateQuery->execute()) {
            echo "Password updated successfully";
        } else {
            echo "Error updating password: " . $updateQuery->error;
        }

        // Close the prepared statement and database connection
        $updateQuery->close();
        $conn->close();
    }
    ?>
</head>
<body>
    <h2>Change Password</h2>
    <form method="post" action="cpwd.php" onsubmit="return validateForm()">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required><br>
        <label for="newPassword">New Password:</label>
        <input type="password" name="newPassword" id="newPassword" minlength="5" maxlength="15"><br>
        <label for="confirmPassword">Confirm New Password:</label>
        <input type="password" name="confirmPassword" id="confirmPassword" minlength="5" maxlength="15"><br>
        <button type="button" id="toggleButton" onclick="togglePassword()">Show Password</button><br>
        <input type="submit" value="Change Password">
    </form>
    <p>Back to <a href="login.php">login</a></p>
</body>
</html>
