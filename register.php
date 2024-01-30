<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'bubels_reg_log');

// Check connection
if ($conn->connect_error) {
    die("Database connection error: " . $conn->connect_error);
}

// Registration form handling
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if a user with the given username already exists
    $query_check = "SELECT * FROM users WHERE username='$username'";
    $result_check = $conn->query($query_check);

    if ($result_check->num_rows > 0) {
        echo "A user with this username already exists. Please choose a different username.";
    } else {
        // Add a new user to the database
        $query_insert = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
        
        if ($conn->query($query_insert) === TRUE) {
            echo "Registration successful!";
        } else {
            echo "Registration error. Please try again.";
        }
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
</head>
<body>
    <h2>Registration Form</h2>
    <form method="post" action="">
        <label for="username">Username:</label>
        <input type="text" name="username" required maxlength="20"><br>
        <label for="password">Password:</label>
        <input type="password" name="password" required minlength="10" maxlength="20"><br>
        <input type="submit" value="Register">
    </form>
    <p>Already have an account? <a href="login.php">Login</a></p>
</body>
</html>
