<?php
session_start();
include 'includes/db.php';

if(isset($_POST['login'])){
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Allow login by username OR by email (if column exists). Use prepared statements.
    $stmt = $conn->prepare('SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ? LIMIT 1');
    $stmt->bind_param('ss', $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result && $result->num_rows > 0){
        $user = $result->fetch_assoc();
        if(password_verify($password, $user['password'])){
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>

<?php include 'includes/header.php'; ?>

<h2>Login</h2>
<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="POST">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit" name="login">Login</button>
</form>
<a href="signup.php">Don't have an account? Sign Up</a>

<?php include 'includes/footer.php'; ?>
