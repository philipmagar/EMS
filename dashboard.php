<?php
session_start();
include 'includes/db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

include 'includes/header.php';

$user_id = $_SESSION['user_id'];

// Get approved events
$result = $conn->query("SELECT * FROM events WHERE status='approved' ORDER BY date ASC");

// Prepared statement for booking checks
$bookingCheckStmt = $conn->prepare('SELECT id, status FROM bookings WHERE user_id = ? AND event_id = ? LIMIT 1');
?>

<div class="dashboard-head">
    <h2>Dashboard</h2>
    <p>Welcome! Browse upcoming events and request bookings.</p>
    <div style="margin-top:12px; display:flex; gap:10px; align-items:center;">
        <a class="btn" href="add_event.php">Create event</a>
        <a class="btn ghost" href="logout.php">Logout</a>
    </div>
</div>

<?php if (!$result || $result->num_rows === 0): ?>
    <p>No available events yet. Consider adding one.</p>
<?php else: ?>
    <div class="events-grid">
        <?php while ($event = $result->fetch_assoc()):
            // Check whether user has already booked/requested this event
            $bookingCheckStmt->bind_param('ii', $user_id, $event['id']);
            $bookingCheckStmt->execute();
            $bkres = $bookingCheckStmt->get_result();
            $booked = ($bkres && $bkres->num_rows > 0) ? $bkres->fetch_assoc() : null;
        ?>
            <div class="event-card">
                <div>
                    <h3><?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= nl2br(htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                </div>
                <div class="event-meta">
                    <div>
                        <div class="date"><?= date('M j, Y', strtotime($event['date'])) ?></div>
                    </div>
                    <div class="event-actions">
                        <div class="badge <?= htmlspecialchars($event['status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($event['status'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php if ($booked): ?>
                            <span class="link-btn">Request: <?= htmlspecialchars($booked['status'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php else: ?>
                            <a class="link-btn" href="book_event.php?event_id=<?= $event['id'] ?>">Request booking</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
