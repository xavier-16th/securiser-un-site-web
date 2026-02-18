<?php
require_once '../auth.php';
require_once '../connect_ddb.php';

requireAdmin(); // Only admins can UPDATE

$error = '';
$success = '';
$user = null;

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: showuser.php');
    exit();
}

// Fetch user data
$sql = "SELECT * FROM user WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    header('Location: showuser.php?message=User not found');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    
    // Validate role
    if (!in_array($role, ['admin', 'user'])) {
        $role = 'user';
    }
    
    if (empty($username) || empty($email)) {
        $error = "Username and email are required.";
    } else {
        // Check if email already exists for another user
        $checkSql = "SELECT user_id FROM user WHERE email = ? AND user_id != ?";
        $checkStmt = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($checkStmt, "si", $email, $id);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        
        if (mysqli_fetch_assoc($checkResult)) {
            $error = "Email already exists for another user.";
        } else {
            if (!empty($password)) {
                // Update with new password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE user SET username = ?, email = ?, password = ?, role = ? WHERE user_id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssssi", $username, $email, $hashedPassword, $role, $id);
            } else {
                // Update without changing password
                $sql = "UPDATE user SET username = ?, email = ?, role = ? WHERE user_id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "sssi", $username, $email, $role, $id);
            }
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "User updated successfully.";
                // Refresh user data
                $user['username'] = $username;
                $user['email'] = $email;
                $user['role'] = $role;
            } else {
                $error = "Error updating user: " . mysqli_error($conn);
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
    <title>Edit User</title>
    <link rel="stylesheet" href="../all.css">
</head>
<body>
    <main>
        <h1>Edit User</h1>
        
        <?php if ($error): ?>
            <div class="message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="message"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($user['username']) ?>" required>
            <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($user['email']) ?>" required>
            <input type="password" name="password" placeholder="New password (leave empty to keep current)">
            <select name="role" style="margin: 10px 0; padding: 14px; border: none; background-color: var(--spotify-black); border-radius: 4px; color: var(--spotify-white);">
                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
            <input type="submit" value="Update User">
        </form>
        <a href="showuser.php" class="back">Back to list</a>
    </main>
</body>
</html>
<?php mysqli_close($conn); ?>
