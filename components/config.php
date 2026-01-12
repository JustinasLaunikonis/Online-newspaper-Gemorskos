<?php
$userRole = $_SESSION['user_role'] ?? '';

$navigation = [
    "Welcome",
];

if (in_array($userRole, ['Editor in Chief', 'Editor'])) {
    $navigation[] = "Editorial";
}

if ($userRole === 'Editor in Chief') {
    $navigation[] = "Users";
    $navigation[] = "Instructions";
}

$navigation = array_merge($navigation, [
    "Database",
    "Logout"
]);

$navigationLink = [
    "welcome.php",
];

if (in_array($userRole, ['Editor in Chief', 'Editor'])) {
    $navigationLink[] = "editorial.php";
}

if ($userRole === 'Editor in Chief') {
    $navigationLink[] = "users.php";
    $navigationLink[] = "instructions.php";
}

$navigationLink = array_merge($navigationLink, [
    "database.php",
    "logout.php"
]);

$navigationLogo  = [
    "welcome.png",
];

if (in_array($userRole, ['Editor in Chief', 'Editor'])) {
    $navigationLogo[] = "editorial.png";
}

if ($userRole === 'Editor in Chief') {
    $navigationLogo[] = "users.png";
    $navigationLogo[] = "instructions.png";
}

$navigationLogo = array_merge($navigationLogo, [
    "database.png",
    "logout.png"
]);
?>