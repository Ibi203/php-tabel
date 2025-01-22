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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reservation'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO reservations (table_id, customer_name, customer_email, customer_phone, reservation_date, reservation_time, party_size, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        
        $stmt->execute([
            $_POST['table_id'],
            $_POST['customer_name'],
            $_POST['customer_email'],
            $_POST['customer_phone'],
            $_POST['reservation_date'],
            $_POST['reservation_time'],
            $_POST['party_size']
        ]);

        $stmt = $pdo->prepare("UPDATE tables SET status = 'reserved' WHERE table_id = ?");
        $stmt->execute([$_POST['table_id']]);

        header("Location: index.php?success=1");
        exit();
    } catch(PDOException $e) {
        $error = "Error making reservation: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_reservation'])) {
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE reservation_id = ? AND customer_email = ?");
        $stmt->execute([$_POST['reservation_id'], $_SESSION['username']]);
        
        $stmt = $pdo->prepare("UPDATE tables SET status = 'available' WHERE table_id = ?");
        $stmt->execute([$_POST['table_id']]);
        
        $pdo->commit();
        
        header("Location: index.php?cancel_success=1");
        exit();
    } catch(PDOException $e) {
        $pdo->rollBack();
        $error = "Error cancelling reservation: " . $e->getMessage();
    }
}

$stmt = $pdo->prepare("SELECT * FROM tables WHERE status = 'available'");
$stmt->execute();
$tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

$reservations = [];
if (isset($_POST['show_reservations'])) {
    $stmt = $pdo->prepare("SELECT r.reservation_id, r.table_id, r.reservation_date, r.reservation_time, r.party_size, 
                          r.status, r.customer_name, r.customer_email, t.table_number 
                          FROM reservations r 
                          JOIN tables t ON r.table_id = t.table_id 
                          WHERE r.customer_email = ? 
                          ORDER BY r.reservation_date, r.reservation_time");
    $stmt->execute([$_SESSION['username']]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Restaurant Reservation System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <div class="mt-3">
            <a href="make-reservation.php" class="btn btn-primary">Make a Reservation</a>
            <a href="view-reservations.php" class="btn btn-info">View My Reservations</a>
            <a href="menu.php" class="btn btn-success">View Menu</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Reservation submitted successfully.</div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['cancel_success'])): ?>
            <div class="alert alert-success">Reservation cancelled successfully.</div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label>Name:</label>
                <input type="text" name="customer_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Email:</label>
                <input type="email" name="customer_email" class="form-control" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" readonly required>
            </div>
            <div class="mb-3">
                <label>Phone:</label>
                <input type="tel" name="customer_phone" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Date:</label>
                <input type="date" name="reservation_date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Time:</label>
                <input type="time" name="reservation_time" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Party Size:</label>
                <input type="number" name="party_size" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Table:</label>
                <select name="table_id" class="form-control" required>
                    <?php foreach($tables as $table): ?>
                        <option value="<?php echo $table['table_id']; ?>">
                            Table <?php echo $table['table_number']; ?> (Capacity: <?php echo $table['capacity']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="submit_reservation" class="btn btn-primary">Submit Reservation</button>
        </form>

        <form method="POST" class="mb-4">
            <button type="submit" name="show_reservations" class="btn btn-info">Show My Reservations</button>
        </form>

        <?php if (!empty($reservations)): ?>
            <h3 class="mt-4">Reservations</h3>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Table Number</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Party Size</th>
                            <th>Status</th>
                            <th>Customer Name</th>
                            <th>Contact</th>
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
                                <td><?php echo htmlspecialchars($reservation['customer_name']); ?></td>
                                <td>
                                    Email: <?php echo htmlspecialchars($reservation['customer_email']); ?><br>
                                </td>
                                <td>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                                        <input type="hidden" name="reservation_id" value="<?php echo htmlspecialchars($reservation['reservation_id']); ?>">
                                        <input type="hidden" name="table_id" value="<?php echo htmlspecialchars($reservation['table_id']); ?>">
                                        <button type="submit" name="cancel_reservation" class="btn btn-danger btn-sm">Cancel</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>