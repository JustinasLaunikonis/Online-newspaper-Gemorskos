<?php
    session_start();
    require_once '../components/layout.php';

    // Database connection
    try {
        $dbHandler = new PDO("mysql:host=mysql;dbname=gemorskos;charset=utf8", "root", "qwerty");
        $dbHandler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $exception) {
        die("Connection error: " . $exception->getMessage());
    }

    // Get user info from session
    $fname = $_SESSION['user_fname'] ?? 'User';
    $lname = $_SESSION['user_lname'] ?? '';
    $username = $_SESSION['user'] ?? 'guest';
    $role = $_SESSION['user_role'] ?? 'Unknown';
    $user_id = $_SESSION['user_id'] ?? 0;

    // Get total number of users
    $totalUsers = $dbHandler->query("SELECT COUNT(*) FROM users")->fetchColumn();

    // Get user's join position (simulated by ID)
    $joinDate = date('F Y'); // You could store this in the database

    $faviconPath = "../assets/sidebar/file_manager.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gemorskos - <?php echo $pageTitle; ?></title>
    <link rel="icon" type="image/x-icon" href="<?php echo $faviconPath; ?>">

    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../style_welcome.css">
</head>
<body>
    <?php
    renderHeader($pageTitle);
    renderSidebar($navigation, $navigationLink, $navigationLogo, $currentPage);
    ?>

    <div class="herobox">
        <div class="welcome-container">
            <div class="welcome-header">
                <h1>Welcome back, <?php echo htmlspecialchars($fname . ' ' . $lname); ?>!</h1>
                <p>Glad to see you at Gemorskos Online Newspaper Management</p>
            </div>

            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-number">#<?php echo $user_id; ?></div>
                    <div class="stat-label">User ID</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo $totalUsers; ?></div>
                    <div class="stat-label">Total Team Members</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo $joinDate; ?></div>
                    <div class="stat-label">Member Since</div>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-card">
                    <h3>Account Information</h3>
                    <div class="info-item">
                        <span class="info-label">Full Name</span>
                        <span class="info-value"><?php echo htmlspecialchars($fname . ' ' . $lname); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Username</span>
                        <span class="info-value"><?php echo htmlspecialchars($username); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Role</span>
                        <span class="info-value"><span class="role-badge"><?php echo htmlspecialchars($role); ?></span></span>
                    </div>
                </div>

                <div class="info-card" style="border-left-color: #5EA8DE;">
                    <h3>Your Role & Responsibilities</h3>
                    <p style="color: #cccccc; line-height: 1.6;">
                        <?php
                        $roleDescriptions = [
                            'Administration' => 'Manage financial operations, budgeting, and administrative tasks. Oversee business operations and resource allocation.',
                            'Editor in Chief' => 'You have full access to manage users, content, and system settings. Oversee the entire newspaper operation.',
                            'Editor' => 'Review and edit articles, manage content workflow, and ensure published materials meet quality standards.',
                            'Journalist/Photographer' => 'Create compelling content through writing and photography. Capture stories that matter to our readers.',
                            'Web Designer' => 'Maintain and improve the digital presence of our newspaper through creative design and user experience.'
                        ];
                        echo $roleDescriptions[$role] ?? 'Contribute to the success of Gemorskos Online Newspaper.';
                        ?>
                    </p>
                </div>
            </div>

            <div class="quick-actions">
                <h3>Quick Actions</h3>
                <div class="action-buttons">
                    <a href="database.php" class="action-btn">Database</a>
                    <a href="logout.php" class="action-btn" style="background-color: #d32f2f;">Logout</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>