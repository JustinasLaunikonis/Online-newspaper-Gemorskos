<?php
    session_start();
    require_once '../components/layout.php';

    try {
        $dbHandler = new PDO("mysql:host=mysql;dbname=gemorskos;charset=utf8", "root", "qwerty");
        $dbHandler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $exception) {
        die("Connection error: " . $exception->getMessage());
    }

    $fname = $_SESSION['user_fname'] ?? 'User';
    $lname = $_SESSION['user_lname'] ?? '';
    $username = $_SESSION['user'] ?? 'guest';
    $role = $_SESSION['user_role'] ?? 'Unknown';
    $user_id = $_SESSION['user_id'] ?? 0;

    $totalUsers = $dbHandler->query("SELECT COUNT(*) FROM users")->fetchColumn();

    $joinDate = date('F Y');

    try {
        $stmt = $dbHandler->prepare("
            SELECT i.instructions_id, i.message_text, i.priority, i.due_date, i.created_at,
                   s.fname as sender_fname, s.lname as sender_lname
            FROM instructions i
            LEFT JOIN users s ON i.sender_id = s.id
            WHERE i.recipient_role = :role
            ORDER BY 
                CASE i.priority
                    WHEN 'urgent' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'medium' THEN 3
                    WHEN 'low' THEN 4
                END,
                i.due_date ASC
        ");
        $stmt->execute(['role' => $role]);
        $userInstructions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $userInstructions = [];
    }

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

            <?php if (count($userInstructions) > 0): ?>
                <div class="instructions-section">
                    <h3>Your Instructions (<?php echo count($userInstructions); ?>)</h3>
                    <div class="instructions-list">
                        <?php foreach ($userInstructions as $instruction): 
                            $isOverdue = strtotime($instruction['due_date']) < strtotime('today');
                        ?>
                            <div class="instruction-card priority-<?php echo $instruction['priority']; ?>">
                                <div class="instruction-header">
                                    <span class="priority-badge priority-<?php echo $instruction['priority']; ?>">
                                        <?php echo htmlspecialchars(ucfirst($instruction['priority'])); ?>
                                    </span>
                                    <span class="instruction-date <?php echo $isOverdue ? 'overdue' : ''; ?>">
                                        Due: <?php echo date('M d, Y', strtotime($instruction['due_date'])); ?>
                                        <?php if ($isOverdue): ?>
                                            <span class="overdue-label">OVERDUE</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="instruction-message">
                                    <?php echo nl2br(htmlspecialchars($instruction['message_text'])); ?>
                                </div>
                                <div class="instruction-footer">
                                    <span class="instruction-from">From: <?php echo htmlspecialchars($instruction['sender_fname'] . ' ' . $instruction['sender_lname']); ?></span>
                                    <span class="instruction-created">Created: <?php echo date('M d, Y', strtotime($instruction['created_at'])); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

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