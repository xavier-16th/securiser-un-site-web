<?php
require_once '../auth.php';
require_once '../connect_ddb.php';

requireAdmin(); // Only admins can DELETE

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: showuser.php?message=ID missing');
    exit();
}

// Prevent admin from deleting themselves
if ($id == $_SESSION['user_id']) {
    header('Location: showuser.php?message=You cannot delete your own account');
    exit();
}

// Check if user exists
$checkSql = "SELECT user_id FROM user WHERE user_id = ?";
$checkStmt = mysqli_prepare($conn, $checkSql);
mysqli_stmt_bind_param($checkStmt, "i", $id);
mysqli_stmt_execute($checkStmt);
$checkResult = mysqli_stmt_get_result($checkStmt);

if (!mysqli_fetch_assoc($checkResult)) {
    header('Location: showuser.php?message=User not found');
    mysqli_stmt_close($checkStmt);
    exit();
}
mysqli_stmt_close($checkStmt);

// Delete user
$sql = "DELETE FROM user WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    header('Location: showuser.php?message=User deleted successfully');
} else {
    header('Location: showuser.php?message=Error deleting user');
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
exit();
?>
