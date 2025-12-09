<?php
// Quick DB inspection helper to show users table structure and entries.
require_once __DIR__ . '/../includes/db.php';

if (!$conn) {
    echo "DB connection failed\n";
    exit(2);
}

echo "Columns in users table:\n";
$res = $conn->query('SHOW COLUMNS FROM users');
while ($row = $res->fetch_assoc()) {
    echo sprintf(" - %s %s %s\n", $row['Field'], $row['Type'], $row['Key']);
}

echo "\nSample rows (id, username, email?, role):\n";
$r = $conn->query('SELECT * FROM users LIMIT 50');
if ($r->num_rows === 0) {
    echo " (no rows)\n";
} else {
    while ($row = $r->fetch_assoc()) {
        $keys = array_keys($row);
        $display = [];
        foreach (['id','username','email','role'] as $k) {
            if (array_key_exists($k, $row)) $display[$k] = $row[$k];
        }
        echo json_encode($display) . "\n";
    }
}

?>
