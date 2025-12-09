<?php
session_start();
include 'includes/db.php';

// Only admin can access
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// header included after checks
include 'includes/header.php';

// Helper
function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$message = '';
$errors = [];

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $user_id = intval($_POST['user_id'] ?? 0);
    $new_password = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$user_id) {
        $errors[] = 'Invalid user selected.';
    }
    if ($new_password === '') {
        $errors[] = 'Password cannot be empty.';
    }
    if ($new_password !== $confirm) {
        $errors[] = 'Password confirmation does not match.';
    }

    if (empty($errors)) {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->bind_param('si', $hash, $user_id);
        if ($stmt->execute()) {
            $message = 'Password updated successfully.';
        } else {
            $errors[] = 'Failed to update password: ' . e($conn->error);
        }
    }
}

// Load users list
$users = [];
$res = $conn->query('SELECT id, username, email, role FROM users ORDER BY id');
if ($res) {
    while ($row = $res->fetch_assoc()) $users[] = $row;
}

?>

<h2>Admin: Manage Users</h2>

<?php if ($message): ?>
    <p style="color:green;"><?= e($message) ?></p>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <?php foreach ($errors as $err): ?>
        <p style="color:red;"><?= e($err) ?></p>
    <?php endforeach; ?>
<?php endif; ?>

<h3>Users</h3>
<?php if (empty($users)): ?>
    <p>No users found.</p>
<?php else: ?>
    <table class="table">
        <tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= e($u['id']) ?></td>
                <td><?= e($u['username']) ?></td>
                <td><?= e($u['email'] ?? '') ?></td>
                <td><?= e($u['role']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<h3>Change a user's password</h3>
<form method="POST">
    <label for="user_id">User</label>
    <select name="user_id" id="user_id" required>
        <option value="">-- choose --</option>
        <?php foreach ($users as $u): ?>
            <option value="<?= e($u['id']) ?>"><?= e($u['username']) ?> (id: <?= e($u['id']) ?>)</option>
        <?php endforeach; ?>
    </select>

    <label for="new_password">New password</label>
    <input type="password" name="new_password" id="new_password" required>

    <label for="confirm_password">Confirm password</label>
    <input type="password" name="confirm_password" id="confirm_password" required>

    <button type="submit" name="change_password">Update Password</button>
</form>

<p><a href="admin.php">Back to Admin Dashboard</a></p>

<?php include 'includes/footer.php'; ?>
