<?php
session_start();
include 'includes/db.php';

// Only logged-in users can add events
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

include 'includes/header.php';

$message = '';
$errors = [];

// Helper to escape
function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date = trim($_POST['date'] ?? '');

    // Basic validation
    if ($title === '') {
        $errors[] = 'Title is required.';
    }
    if ($date === '') {
        $errors[] = 'Date is required.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $errors[] = 'Date must be in YYYY-MM-DD format.';
    }

    if (empty($errors)) {
        $created_by = $_SESSION['user_id'];
        $status = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'approved' : 'pending';

        $stmt = $conn->prepare('INSERT INTO events (title, description, date, created_by, status) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssis', $title, $description, $date, $created_by, $status);
        if ($stmt->execute()) {
            if ($status === 'approved') {
                $message = 'Event created and published.';
            } else {
                $message = 'Event submitted. It will be visible after admin approval.';
            }
            // clear form values
            $title = $description = $date = '';
        } else {
            $errors[] = 'Failed to create event: ' . e($conn->error);
        }
    }
}

?>

<h2>Add Event</h2>

<?php if (!empty($message)): ?>
    <p style="color:green;"><?= e($message) ?></p>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <?php foreach ($errors as $err): ?>
        <p style="color:red;"><?= e($err) ?></p>
    <?php endforeach; ?>
<?php endif; ?>

<form method="POST">
    <label>Title</label>
    <input type="text" name="title" value="<?= e($title ?? '') ?>" required>

    <label>Description</label>
    <textarea name="description"><?= e($description ?? '') ?></textarea>

    <label>Date (YYYY-MM-DD)</label>
    <input type="date" name="date" value="<?= e($date ?? '') ?>" required>

    <button type="submit" name="add_event">Submit Event</button>
</form>

<p><a href="dashboard.php">Back to Dashboard</a></p>

<?php include 'includes/footer.php'; ?>

