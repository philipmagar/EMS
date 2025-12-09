<?php
session_start();
include 'includes/db.php';

// User must be logged in to book
if (!isset($_SESSION['user_id'])) {
	header('Location: login.php');
	exit();
}

// After checks include header (do not send output before redirects)
include 'includes/header.php';

$user_id = $_SESSION['user_id'];

// Simple helper for escaping output
function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$message = '';

// If booking form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
	$event_id = intval($_POST['event_id']);

	// Validate event exists and is approved
	$stmt = $conn->prepare('SELECT id, title, status FROM events WHERE id = ?');
	$stmt->bind_param('i', $event_id);
	$stmt->execute();
	$res = $stmt->get_result();

	if (!$res || $res->num_rows === 0) {
		$message = "Event not found.";
	} else {
		$event = $res->fetch_assoc();
		if ($event['status'] !== 'approved') {
			$message = 'This event is not open for booking.';
		} else {
			// Check for existing booking
			$check = $conn->prepare('SELECT id FROM bookings WHERE user_id = ? AND event_id = ?');
			$check->bind_param('ii', $user_id, $event_id);
			$check->execute();
			$cr = $check->get_result();
			if ($cr && $cr->num_rows > 0) {
				$message = 'You have already requested a booking for this event.';
			} else {
				// Create booking (pending)
				$insert = $conn->prepare('INSERT INTO bookings (user_id, event_id, status) VALUES (?, ?, "pending")');
				$insert->bind_param('ii', $user_id, $event_id);
				if ($insert->execute()) {
					$message = 'Booking request sent — waiting for admin approval.';
				} else {
					$message = 'Failed to create booking: ' . e($conn->error);
				}
			}
		}
	}

	// Keep event data for redisplay
	if (isset($event_id) && is_int($event_id) && $event_id > 0) {
		$stmt2 = $conn->prepare('SELECT id, title, description, date, status FROM events WHERE id = ?');
		$stmt2->bind_param('i', $event_id);
		$stmt2->execute();
		$evtRes = $stmt2->get_result();
		$event = ($evtRes && $evtRes->num_rows > 0) ? $evtRes->fetch_assoc() : null;
	}
}

// If arriving with GET event_id, load the event
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['event_id'])) {
	$event_id = intval($_GET['event_id']);
	$stmt = $conn->prepare('SELECT id, title, description, date, status FROM events WHERE id = ?');
	$stmt->bind_param('i', $event_id);
	$stmt->execute();
	$res = $stmt->get_result();
	$event = ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
}

?>

<h2>Book Event</h2>

<?php if (!empty($message)): ?>
	<p style="color:green;"><?= e($message) ?></p>
<?php endif; ?>

<?php if (empty($event)): ?>
	<p>Event not found.</p>
<?php else: ?>
	<h3><?= e($event['title']) ?></h3>
	<p><?= nl2br(e($event['description'])) ?></p>
	<p><strong>Date:</strong> <?= e($event['date']) ?></p>
	<p><strong>Status:</strong> <?= e($event['status']) ?></p>

	<?php if ($event['status'] !== 'approved'): ?>
		<p style="color:orange;">This event is not open for booking.</p>
	<?php else: ?>

		<form method="POST">
			<input type="hidden" name="event_id" value="<?= e($event['id']) ?>">
			<button type="submit">Request Booking</button>
		</form>

		<p><a href="dashboard.php">Back to Dashboard</a></p>
	<?php endif; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

