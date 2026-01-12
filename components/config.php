<?php
// Get user role from session
$userRole = $_SESSION['user_role'] ?? '';

// display navigation items
$navigation = [
    "Welcome",
];

// Add Editorial for Editor in Chief and Editor only
if (in_array($userRole, ['Editor in Chief', 'Editor'])) {
    $navigation[] = "Editorial";
}

// Add remaining navigation items
$navigation = array_merge($navigation, [
    "Database",
    "Logout"
]);

// corresponding links
$navigationLink = [
    "welcome.php",
];

if (in_array($userRole, ['Editor in Chief', 'Editor'])) {
    $navigationLink[] = "editorial.php";
}

$navigationLink = array_merge($navigationLink, [
    "database.php",
    "logout.php"
]);

// corresponding icons
$navigationLogo  = [
    "welcome.png",
];

if (in_array($userRole, ['Editor in Chief', 'Editor'])) {
    $navigationLogo[] = "editorial.png";
}

$navigationLogo = array_merge($navigationLogo, [
    "database.png",
    "logout.png"
]);
?>