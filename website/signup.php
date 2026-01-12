<?php
session_start();

// Database connection
try {
    $dbHandler = new PDO("mysql:host=mysql;dbname=gemorskos;charset=utf8", "root", "qwerty");
    $dbHandler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $exception) {
    die("Connection error: " . $exception->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $fname = $_POST['fname'] ?? '';
    $lname = $_POST['lname'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';

    // Validation
    if (empty($username) || empty($fname) || empty($lname) || empty($password) || empty($role)) {
        $error = "All fields are required";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } else {
        try {
            // Check if username already exists
            $stmt = $dbHandler->prepare("SELECT id FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            
            if ($stmt->fetch()) {
                $error = "Username already exists";
            } else {
                // Insert new user
                $stmt = $dbHandler->prepare("INSERT INTO users (username, fname, lname, password, role) VALUES (:username, :fname, :lname, :password, :role)");
                $stmt->execute([
                    'username' => $username,
                    'fname' => $fname,
                    'lname' => $lname,
                    'password' => $password,
                    'role' => $role
                ]);
                
                $success = "Account created successfully! You can now log in.";
            }
        } catch(PDOException $e) {
            $error = "Registration error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>

    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../style_login.css">
</head>
<body>

    <div class="hero">
        <div>
            <img src="../assets/logo.png" alt="Logo">
            <h3>Create Your Account</h3>
            <p>Join Gemorskos</p>
            <small>&copy;Gemorskos</small>
        </div>

        <div>
            <?php if (isset($error)): ?>
                <div style="color: #ff6b6b; margin-bottom: 15px; padding: 10px; background-color: #4a2525; border-radius: 5px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div style="color: #4caf50; margin-bottom: 15px; padding: 10px; background-color: #1e3a1e; border-radius: 5px;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="login-form">

            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>

            <label for="fname">First Name</label>
            <input type="text" id="fname" name="fname" required>

            <label for="lname">Last Name</label>
            <input type="text" id="lname" name="lname" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <label for="role">Role</label>
            <select id="role" name="role" required style="padding: 15px; background-color: #222222; border: 1px solid #555555; border-radius: 5px; color: white; font-size: 16px; font-family: inherit;">
                <option value="">Select a role</option>
                <option value="Administration">Administration</option>
                <option value="Editor in Chief">Editor in Chief</option>
                <option value="Editor">Editor</option>
                <option value="Journalist/Photographer">Journalist/Photographer</option>
                <option value="Web Designer">Web Designer</option>
            </select>

            <div class="button-group">
                <button type="submit">Sign Up</button>
                <a href="../index.php" class="signup-btn">Back to Login</a>
            </div>
            </form>
        </div>
    </div>
</body>
</html>