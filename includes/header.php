<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<link rel="stylesheet" href="css/style.css">
	<title>Event Management System</title>
	<meta charset="utf-8">
</head>
<body>
	<header class="site-header">
		<div class="container header-inner">
			<div class="brand">
				<a href="dashboard.php" class="brand-link">Event Management</a>
			</div>
			<nav class="main-nav">
				<a href="dashboard.php">Dashboard</a>
				<a href="add_event.php">Add Event</a>
				<a href="book_event.php">Book</a>
				<?php if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
					<a href="admin.php">Admin</a>
					<a href="admin_users.php">Users</a>
				<?php endif; ?>
			</nav>
			<div class="header-actions">
				<?php if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id'])): ?>
					<span class="user">Hello, <?= htmlspecialchars($_SESSION['user_id']) ?></span>
					<a class="btn small" href="logout.php">Logout</a>
				<?php else: ?>
					<a class="btn small" href="login.php">Login</a>
				<?php endif; ?>
			</div>
		</div>
	</header>
	<main class="container main-content">
