<?php
    session_start();
    require_once '../components/layout.php';

    $faviconPath = "../assets/sidebar/database.png";
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
                <h2>Database Management</h2>
                <p>Access the database administration panel</p>
                
                <div class="logout-buttons">
                    <a href="http://localhost:8080" target="_blank" class="database-btn">Open Database</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>