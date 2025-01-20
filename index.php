
<?php
session_start();
require_once 'config.php';
require_once 'database.php';

$db = new Database();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $conn->prepare("INSERT INTO reservations (table_id, customer_name, customer_email, customer_phone, reservation_date, reservation_time, party_size, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        
        $stmt->execute([
            $_POST['table_id'],
            $_POST['customer_name'], 
            $_POST['customer_email'],
            $_POST['customer_phone'],
            $_POST['reservation_date'],
            $_POST['reservation_time'],
            $_POST['party_size']
        ]);

        $stmt = $conn->prepare("UPDATE tables SET status = 'reserved' WHERE table_id = ?");
        $stmt->execute([$_POST['table_id']]);

        header("Location: index.php?success=1");
        exit();
    } catch(PDOException $e) {
        $error = "Error making reservation: " . $e->getMessage();
    }
}

$stmt = $conn->prepare("SELECT * FROM tables WHERE status = 'available'");
$stmt->execute();
$tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Table Reservation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>reserveer</h2>
            <a href="admin/login.php" class="btn btn-secondary">Admin Login</a>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Reservatie opgestuurd.</div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label>naam:</label>
                <input type="text" name="customer_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Email:</label>
                <input type="email" name="customer_email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Numer:</label>
                <input type="tel" name="customer_phone" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Date:</label>
                <input type="date" name="reservation_date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>tijd:</label>
                <input type="time" name="reservation_time" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Party Size:</label>
                <input type="number" name="party_size" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>tafel:</label>
                <select name="table_id" class="form-control" required>
                    <?php foreach($tables as $table): ?>
                        <option value="<?php echo $table['table_id']; ?>">
                            Table <?php echo $table['table_number']; ?> (Capacity: <?php echo $table['capacity']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Submit Reservation</button>
        </form>
    </div>
</body>
</html>
