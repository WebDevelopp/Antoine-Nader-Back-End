<?php
require 'database.php';
require 'CategoryModel.php';
require 'CategoryController.php';

$controller = new CategoryController($db);

// Debug the URI to see what it actually is
var_dump($_SERVER['REQUEST_URI']);

if ($_SERVER['REQUEST_URI'] === '/category/add') {
    $controller->addCategory();
} elseif ($_SERVER['REQUEST_URI'] === '/category/delete' && isset($_GET['id'])) {
    $controller->deleteCategory($_GET['id']);
} else {
    // Handle default or other routes
    echo "Page not found";
}
?>

