<?php
include 'includes/db.php';
if(isset($_POST['signup'])){
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, password) VALUES ('$username','$password')";
    if($conn->query($sql)){
        header("Location: login.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<?php include 'includes/header.php'; ?>

<h2>Sign Up</h2>
<form method="POST">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit" name="signup">Sign Up</button>
</form>
<a href="login.php">Already have an account? Login</a>

<?php include 'includes/footer.php'; ?>
