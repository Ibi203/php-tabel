<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: user-login.php");
    exit();
}

require_once 'config.php';

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_reservation'])) {
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE reservation_id = ? AND customer_email = ?");
        $stmt->execute([$_POST['reservation_id'], $_SESSION['username']]);
        
        $stmt = $pdo->prepare("UPDATE tables SET status = 'available' WHERE table_id = ?");
        $stmt->execute([$_POST['table_id']]);
        
        $pdo->commit();
        
        header("Location: view-reservations.php?cancel_success=1");
        exit();
    } catch(PDOException $e) {
        $pdo->rollBack();
        $error = "Error cancelling reservation: " . $e->getMessage();
    }
}

$stmt = $pdo->prepare("SELECT r.reservation_id, r.table_id, r.reservation_date, r.reservation_time, 
                       r.party_size, r.status, r.customer_name, r.customer_email, t.table_number 
                       FROM reservations r
                       JOIN tables t ON r.table_id = t.table_id
                       WHERE r.customer_email = ?
                       ORDER BY r.reservation_date, r.reservation_time");
$stmt->execute([$_SESSION['username']]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Reservations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Reservations</h2>
            <div>
                <a href="index.php" class="btn btn-primary">Back to Reservations</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>

        <?php if (isset($_GET['cancel_success'])): ?>
            <div class="alert alert-success">Reservation cancelled successfully.</div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($reservations)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Table Number</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Party Size</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reservations as $reservation): ?>
                            <tr>
                                <td>Table <?php echo htmlspecialchars($reservation['table_number']); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($reservation['reservation_date'])); ?></td>
                                <td><?php echo date('H:i', strtotime($reservation['reservation_time'])); ?></td>
                                <td><?php echo htmlspecialchars($reservation['party_size']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['status']); ?></td>
                                <td>
                                    <?php if ($reservation['status'] !== 'cancelled'): ?>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                                            <input type="hidden" name="reservation_id" value="<?php echo htmlspecialchars($reservation['reservation_id']); ?>">
                                            <input type="hidden" name="table_id" value="<?php echo htmlspecialchars($reservation['table_id']); ?>">
                                            <button type="submit" name="cancel_reservation" class="btn btn-danger btn-sm">Cancel</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">You have no reservations.</div>
        <?php endif; ?>
    </div>
</body>
</html>
