<?php
// ===================== login page =====================
// hiniwalay namin to sa header.php kasi ang laki na niya, tas standalone naman talaga
// itong page na to (may sariling <html> doc, di na kailangan ng header/footer.php)
// note: header.php lang dapat tumatawag dito (require 'login.php';) pag $page === 'login'
// laging may exit() sa dulo ng bawat branch dito so safe siya, di na babalik sa header.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');

    // Hardcoded credentials (La peace bro)
    $credentials = [
        'admin'    => ['password' => 'admin123', 'role' => 'admin'],
        'student1' => ['password' => 'pass123',  'role' => 'student'],
        'student2' => ['password' => 'pass123',  'role' => 'student'],
    ];

    // ts logic where it checks if creds correct dont touch
    // process login for creds dont touch bro
    if (isset($credentials[$u]) && $credentials[$u]['password'] === $p) {
        $_SESSION['username']  = $u;
        $_SESSION['role']      = $credentials[$u]['role'];
        $_SESSION['logged_in'] = true;
        header('Location: header.php?page=dashboard');
        exit();
    } else {
        $loginError = 'Invalid username or password.';
    }
} elseif ($loggedIn) {
    // GET lang to (walang sinubmit na creds) straight to dash

    header('Location: header.php?page=dashboard');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Task Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <div class="logo-container">
                    <span style="font-size:50px;">📋</span>
                </div>
                <h1>Task Manager</h1>
                <p class="subtitle">Sign in to manage your tasks</p>
                <p class="university-name">ITCC1023 - Web Systems and Technologies I</p>
            </div>

            <?php if ($loginError): ?>
                <div class="error-message"><?php echo htmlspecialchars($loginError); ?></div>
            <?php endif; ?>

            <form method="POST" action="header.php?page=login">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-login">🔐 Login</button>
                <button type="reset" class="btn-reset">Clear</button>
            </form>

            <div class="login-info">
                <span class="demo-label">Demo Accounts</span>
                <strong>Admin:</strong> admin / admin123<br>
                <strong>Student:</strong> student1 / pass123<br>
                <strong>Student:</strong> student2 / pass123
            </div>
        </div>
    </div>
</body>
</html>
<?php
exit();
