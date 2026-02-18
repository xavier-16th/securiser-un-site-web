<?php
session_start();
require_once 'connect_ddb.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: user/showuser.php');
    exit();
}
//error appear when user try to login with wrong email or password
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        //else check if email exists and verify password
        $sql = "SELECT user_id, username, email, password, role FROM user WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        //execute the statement and get the result from the database
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        //if user exist verify the password with the hashed password in the database
        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $user['password'])) {
                // Store user data in session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect to user dashboard or homepage
                header('Location: user/showuser.php');
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "User not found.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="all.css">
</head>
<body>
    <main>
        <h1>Login</h1>
        <?php if ($error): ?>
            <div class="message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" value="Login">
        </form>
        <a href="register.php" class="back">Create an account</a>
    </main>
</body>
</html>
