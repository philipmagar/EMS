<?php
session_start();
include 'includes/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit;
}

include 'includes/header.php';

// Approve / Reject Event
if(isset($_GET['approve_event'])){
    $id = $_GET['approve_event'];
    $conn->query("UPDATE events SET status='approved' WHERE id='$id'");
}

if(isset($_GET['reject_event'])){
    $id = $_GET['reject_event'];
    $conn->query("UPDATE events SET status='rejected' WHERE id='$id'");
}

// Approve / Reject Booking
if(isset($_GET['approve_booking'])){
    $id = $_GET['approve_booking'];
    $conn->query("UPDATE bookings SET status='approved' WHERE id='$id'");
}

if(isset($_GET['reject_booking'])){
    $id = $_GET['reject_booking'];
    $conn->query("UPDATE bookings SET status='rejected' WHERE id='$id'");
}

$events = $conn->query("SELECT * FROM events");
$bookings = $conn->query("SELECT b.id, u.username, e.title, b.status FROM bookings b JOIN users u ON b.user_id=u.id JOIN events e ON b.event_id=e.id");
?>

<h2>Admin Dashboard</h2>

<h3>Event Requests</h3>
<table class="table">
<tr><th>Title</th><th>Status</th><th>Actions</th></tr>
<?php while($event = $events->fetch_assoc()): ?>
<tr>
<td><?= $event['title']; ?></td>
<td><?= $event['status']; ?></td>
<td>
<?php if($event['status']=='pending'): ?>
<a href="?approve_event=<?= $event['id']; ?>">Approve</a> | 
<a href="?reject_event=<?= $event['id']; ?>">Reject</a>
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</table>

<h3>Bookings</h3>
<table class="table">
<tr><th>User</th><th>Event</th><th>Status</th><th>Actions</th></tr>
<?php while($booking = $bookings->fetch_assoc()): ?>
<tr>
<td><?= $booking['username']; ?></td>
<td><?= $booking['title']; ?></td>
<td><?= $booking['status']; ?></td>
<td>
<?php if($booking['status']=='pending'): ?>
<a href="?approve_booking=<?= $booking['id']; ?>">Approve</a> | 
<a href="?reject_booking=<?= $booking['id']; ?>">Reject</a>
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</table>

<a href="dashboard.php">Back to Dashboard</a>

<?php include 'includes/footer.php'; ?>
