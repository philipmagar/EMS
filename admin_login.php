<?php
session_start();
include 'includes/db.php';

// Process admin login — only admins can login here
if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Prepared query: match username OR email
    $stmt = $conn->prepare('SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ? LIMIT 1');
    $stmt->bind_param('ss', $username, $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows > 0) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            if ($user['role'] !== 'admin') {
                $error = 'User is not an admin.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                header('Location: admin.php');
                exit();
            }
        } else {
            $error = 'Invalid password!';
        }
    } else {
        $error = 'User not found!';
    }
}

// Render minimal UI
include 'includes/header.php';
?>

<h2>Admin Login</h2>
<?php if (isset($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<form method="POST">
    <input type="text" name="username" placeholder="username or email" required>
    <input type="password" name="password" placeholder="password" required>
    <button type="submit" name="login">Sign in as admin</button>
    <p><a href="login.php">Back to regular login</a></p>
</form>

<?php include 'includes/footer.php'; ?>
