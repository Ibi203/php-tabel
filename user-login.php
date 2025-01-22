<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'config.php';

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Invalid username or password!";
        }
    }

    if (isset($_POST['register'])) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'klant')");
        if ($stmt->execute([$username, $password])) {
            $error_message = "Registration successful! Please login.";
        } else {
            $error_message = "Registration failed!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">User Login</h2>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <h3>Register</h3>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" name="register" class="btn btn-primary">Register</button>
                </form>
            </div>

            <div class="col-md-6">
                <h3>Login</h3>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-success">Login</button>
                </form>
            </div>
        </div>

        <div class="mt-4">
            <div class="d-flex gap-2">
                <button onclick="location.href='admin/login.php'" class="btn btn-secondary">Go to Admin Login</button>
                <button onclick="location.href='menu.php'" class="btn btn-success">View Menu</button>
            </div>
        </div>
    </div>
</body>
</html>
