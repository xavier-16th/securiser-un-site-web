<?php
require_once '../auth.php';
require_once '../connect_ddb.php';

requireAdmin(); // Only admins can CREATE

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    
    // Validate role
    if (!in_array($role, ['admin', 'user'])) {
        $role = 'user';
    }
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Check if email already exists
        $checkSql = "SELECT user_id FROM user WHERE email = ?";
        $checkStmt = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        
        if (mysqli_fetch_assoc($checkResult)) {
            $error = "Email already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO user (username, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $hashedPassword, $role);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "User created successfully.";
            } else {
                $error = "Error creating user: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_stmt_close($checkStmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <link rel="stylesheet" href="../all.css">
</head>
<body>
    <main>
        <h1>Add User</h1>
        
        <?php if ($error): ?>
            <div class="message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="message"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role" style="margin: 10px 0; padding: 14px; border: none; background-color: var(--spotify-black); border-radius: 4px; color: var(--spotify-white);">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
            <input type="submit" value="Create User">
        </form>
        <a href="showuser.php" class="back">Back to list</a>
    </main>
</body>
</html>
<?php mysqli_close($conn); ?>
