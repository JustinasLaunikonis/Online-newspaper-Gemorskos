<?php
require_once __DIR__ . '/config.php';

if(!isset($currentPage)){
    $currentPage = basename($_SERVER['PHP_SELF']);
}

$currentPageIndex = array_search(basename($currentPage), array_map('basename', $navigationLink));
$pageTitle = ($currentPageIndex !== false) ? $navigation[$currentPageIndex] : '';

function renderHeader($pageTitle){
    echo '<header>';
    echo '<h2 class="headerTitle">'.htmlspecialchars($pageTitle).'</h2>';
    echo '<h4 class="headerCaption">Online Newspaper - Gemorskos </h4>';
    echo '<h4 class="headerCaption">Logged in as - '.htmlspecialchars($_SESSION['user'] ?? 'User').'</h4>';
    echo '</header>';
}

function renderSidebar($navigation, $navigationLink, $navigationLogo, $currentPage){
    echo '<aside class="sidebar"><nav>';
    echo '<img src="../assets/logo.png" alt="logo">';
    echo '<ul>';
    foreach($navigation as $i => $label){
        $link = '../website/' . $navigationLink[$i];
        $icon = '../assets/sidebar/' . basename($navigationLogo[$i]);
        $alt  = strtolower(str_replace(' ', '_', $label));
        $selected = (basename($navigationLink[$i]) === basename($currentPage));

        if($selected){
            $icon = str_replace('.png', '_blue.png', $icon);
            echo '<li><a class="selected" href="'.$link.'"><img src="'.$icon.'" alt="'.$alt.'">'.$label.'</a></li>';
        } else {
            echo '<li><a href="'.$link.'"><img src="'.$icon.'" alt="'.$alt.'">'.$label.'</a></li>';
        }
    }
    echo '</ul></nav>';
    echo '</div></aside>';
}
?>