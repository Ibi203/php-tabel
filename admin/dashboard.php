<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

require_once '../config.php';
require_once '../database.php';

$db = new Database();
$conn = $db->getConnection();

if (isset($_POST['reservation_id']) && isset($_POST['status'])) {
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("SELECT table_id FROM reservations WHERE reservation_id = ?");
    $stmt->execute([$_POST['reservation_id']]);
    $reservation_details = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($status === 'cancelled') {
        $stmt = $conn->prepare("DELETE FROM reservations WHERE reservation_id = ?");
        $stmt->execute([$_POST['reservation_id']]);

        $stmt = $conn->prepare("UPDATE tables SET status = 'available' WHERE table_id = ?");
        $stmt->execute([$reservation_details['table_id']]);
        
    } else if ($status === 'confirmed') {
        $stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
        $stmt->execute(['confirmed', $_POST['reservation_id']]);

        $stmt = $conn->prepare("UPDATE tables SET status = 'reserved' WHERE table_id = ?");
        $stmt->execute([$reservation_details['table_id']]);
        
    } else if ($status === 'denied') {
        $stmt = $conn->prepare("DELETE FROM reservations WHERE reservation_id = ?");
        $stmt->execute([$_POST['reservation_id']]);

        $stmt = $conn->prepare("UPDATE tables SET status = 'available' WHERE table_id = ?");
        $stmt->execute([$reservation_details['table_id']]);
    }
}

$stmt = $conn->prepare("DELETE FROM reservations WHERE reservation_date < CURDATE() OR (reservation_date = CURDATE() AND reservation_time < CURTIME())");
$stmt->execute();

if (isset($_POST['delete_table'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM tables WHERE table_id = ?");
        $stmt->execute([$_POST['table_id']]);
        header("Location: dashboard.php");
        exit();
    } catch(PDOException $e) {
        $error = "Error deleting table: " . $e->getMessage();
    }
}

$stmt = $conn->prepare("SELECT r.*, t.table_number FROM reservations r JOIN tables t ON r.table_id = t.table_id ORDER BY r.reservation_date ASC, r.reservation_time ASC");
$stmt->execute();
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM tables ORDER BY table_number");
$stmt->execute();
$tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['add_table'])) {
    try {
        $stmt = $conn->prepare("INSERT INTO tables (table_number, capacity, status) VALUES (?, ?, 'available')");
        $stmt->execute([$_POST['table_number'], $_POST['capacity']]);
        header("Location: dashboard.php");
        exit();
    } catch(PDOException $e) {
        $error = "Error adding table: " . $e->getMessage();
    }
}

if (isset($_POST['add_admin'])) {
    try {
        $hashed_password = password_hash($_POST['admin_password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, role, created_at) VALUES (?, ?, 'admin', NOW())");
        $stmt->execute([$_POST['admin_username'], $hashed_password]);
        $success_message = "Admin user added successfully";
    } catch(PDOException $e) {
        $admin_error = "Error adding admin user: " . $e->getMessage();
    }
}

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title> Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta http-equiv="refresh" content="30">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2> Dashboard</h2>
            <form method="POST" action="">
                <button type="submit" name="logout" class="btn btn-danger">Loguit</button>
            </form>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">Voeg tafels toe z</div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" name="table_number" placeholder="Table Number" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <input type="number" name="capacity" placeholder="Capacity" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" name="add_table" class="btn btn-primary">Add Table</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Voeg nieuwe admin toe</div>
            <div class="card-body">
                <?php if (isset($admin_error)): ?>
                    <div class="alert alert-danger"><?php echo $admin_error; ?></div>
                <?php endif; ?>
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" name="admin_username" placeholder="Username" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <input type="password" name="admin_password" placeholder="Password" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" name="add_admin" class="btn btn-primary">Add Admin User</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <h3>Tables</h3>
                <table class="table mb-4">
                    <thead>
                        <tr>
                            <th>Table Number</th>
                            <th>Capacity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($tables as $table): ?>
                        <tr>
                            <td><?php echo $table['table_number']; ?></td>
                            <td><?php echo $table['capacity']; ?></td>
                            <td><?php echo $table['status']; ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="table_id" value="<?php echo $table['table_id']; ?>">
                                    <button type="submit" name="delete_table" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this table?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="col-md-6">
                <h3>Reservations</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Table</th>
                            <th>Customer</th>
                            <th>Date & Time</th>
                            <th>Party Size</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reservations as $reservation): ?>
                        <tr>
                            <td><?php echo $reservation['reservation_id']; ?></td>
                            <td>Table <?php echo $reservation['table_number']; ?></td>
                            <td>
                                <?php echo $reservation['customer_name']; ?><br>
                                <?php echo $reservation['customer_email']; ?><br>
                                <?php echo $reservation['customer_phone']; ?>
                            </td>
                            <td>
                                <?php echo $reservation['reservation_date']; ?><br>
                                <?php echo $reservation['reservation_time']; ?>
                            </td>
                            <td><?php echo $reservation['party_size']; ?></td>
                            <td><?php echo $reservation['status']; ?></td>
                            <td>
                                <?php if($reservation['status'] === 'pending'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="reservation_id" value="<?php echo $reservation['reservation_id']; ?>">
                                    <button type="submit" name="status" value="confirmed" class="btn btn-success btn-sm">Confirm</button>
                                    <button type="submit" name="status" value="denied" class="btn btn-danger btn-sm">Deny</button>
                                </form>
                                <?php elseif($reservation['status'] === 'confirmed'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="reservation_id" value="<?php echo $reservation['reservation_id']; ?>">
                                    <button type="submit" name="status" value="cancelled" class="btn btn-warning btn-sm">Cancel</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>