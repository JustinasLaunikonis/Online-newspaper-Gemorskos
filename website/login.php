<?php
session_start();

$savedUsername = $_COOKIE['saved_username'] ?? '';
$savedPassword = $_COOKIE['saved_password'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if ($username === 'admin' && $password === 'qwerty') {
        $_SESSION['user'] = $username;
        
        if ($remember) {
            setcookie('saved_username', $username, time() + (30 * 24 * 60 * 60), '/');
            setcookie('saved_password', $password, time() + (30 * 24 * 60 * 60), '/');
        } else {
            // clear cookies if 'remember me' is disabled
            setcookie('saved_username', '', time() - 3600, '/');
            setcookie('saved_password', '', time() - 3600, '/');
        }
        
        header('Location: file_manager/file_manager.php');
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../style_login.css">
</head>
<body>
    <div class="hero">
        <div>
            <img src="../assets/logo.png" alt="Logo">
            <h3>Welcome to Gemorskos</h3>
            <p>Online Newspaper Management</p>
            <small>&copy;Gemorskos</small>
        </div>

        <div>
            <?php if (isset($error)): ?>
                <div style="color: #ff6b6b; margin-bottom: 15px; padding: 10px; background-color: #4a2525; border-radius: 5px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="login-form">

            <label for="username">Username</label>
            <input type="text" id="username" name="username" autocomplete="username" value="<?php echo htmlspecialchars($savedUsername); ?>">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" autocomplete="current-password" value="<?php echo htmlspecialchars($savedPassword); ?>">

            <div>
                <label class="switch">
                    <input type="checkbox" id="remember" name="remember" <?php echo !empty($savedUsername) ? 'checked' : ''; ?>>
                    <span class="slider"></span>
                </label>
                
                <p>Remember Me</p>
            </div>

            <button type="submit">Login</button>
            </form>
        </div>
    </div>
</body>
</html>