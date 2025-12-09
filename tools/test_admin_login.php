<?php
require_once __DIR__ . '/../includes/db.php';
$username = 'admin@example.com';
$password = '1234';

$stmt = $conn->prepare('SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ? LIMIT 1');
$stmt->bind_param('ss', $username, $username);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows === 0) {
    echo "No user found for $username\n";
    exit(1);
}

$user = $res->fetch_assoc();
echo "Found user: " . json_encode(['id'=>$user['id'],'username'=>$user['username'],'email'=>$user['email'],'role'=>$user['role']]) . "\n";

if ($user['role'] !== 'admin') {
    echo "User is not an admin.\n";
    exit(2);
}

if (password_verify($password, $user['password'])) {
    echo "Admin login credentials verified.\n";
    exit(0);
} else {
    echo "Password did NOT verify.\n";
    exit(3);
}

?>
