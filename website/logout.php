<?php
    session_start();
    require_once '../components/layout.php';

    // Handle logout action
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_logout'])) {
        header('Location: ../index.php');
        exit();
    }

    $faviconPath = "../assets/sidebar/users.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gemorskos - <?php echo $pageTitle; ?></title>
    <link rel="icon" type="image/x-icon" href="<?php echo $faviconPath; ?>">

    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php
    renderHeader($pageTitle);
    renderSidebar($navigation, $navigationLink, $navigationLogo, $currentPage);
    ?>

    <div class="herobox">
        <div class="logout-container">
            <div class="logout-box">
                <h2>Logout Confirmation</h2>
                <p><?php echo htmlspecialchars($_SESSION['user'] ?? 'User'); ?>, would you like to logout?</p>
                
                <form method="POST" action="">
                    <div class="logout-buttons">
                        <button type="submit" name="confirm_logout" class="logout-btn">Yes, Logout</button>
                        <a href="welcome.php" class="cancel-btn">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>