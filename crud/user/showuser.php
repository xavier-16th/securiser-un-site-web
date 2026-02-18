<?php
require_once '../auth.php';
require_once '../connect_ddb.php';

requireLogin(); // All logged-in users can READ

$sql = "SELECT user_id, username, email, role FROM user";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User List</title>
    <link rel="stylesheet" href="../all.css">
</head>
<body>
    <main>
        <nav>
            <ul>
                <li><span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?> (<?= htmlspecialchars($_SESSION['role']) ?>)</span></li>
                <li><a href="../logout.php">Logout</a></li>
                <?php if (isAdmin()): ?>
                    <li><a href="adduser.php" class="link">Add User</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <h1>User List</h1>
        
        <?php if (isset($_GET['message'])): ?>
            <div class="message"><?= htmlspecialchars($_GET['message']) ?></div>
        <?php endif; ?>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <?php if (isAdmin()): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td data-label="ID"><?= htmlspecialchars($row['user_id']) ?></td>
                        <td data-label="Username"><?= htmlspecialchars($row['username']) ?></td>
                        <td data-label="Email"><?= htmlspecialchars($row['email']) ?></td>
                        <td data-label="Role"><?= htmlspecialchars($row['role']) ?></td>
                        <?php if (isAdmin()): ?>
                            <td data-label="Actions">
                                <a href="modifyuser.php?id=<?= $row['user_id'] ?>">Edit</a> |
                                <a href="deleteuser.php?id=<?= $row['user_id'] ?>" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
<?php mysqli_close($conn); ?>
