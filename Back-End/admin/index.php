<?php
require 'database.php';
require 'CategoryModel.php';
require 'CategoryController.php';

$controller = new CategoryController($db);

if ($_SERVER['REQUEST_URI'] === '/category/add') {
    $controller->addCategory();
} elseif ($_SERVER['REQUEST_URI'] === '/category/delete' && isset($_GET['id'])) {
    $controller->deleteCategory($_GET['id']);
} elseif ($_SERVER['REQUEST_URI'] === '/category/add-multiple') {
    // Handle bulk addition of categories
    $items = json_decode(file_get_contents('php://input'), true);
    $controller->addMultipleCategories($items);
    echo json_encode(['status' => 'success', 'message' => 'Categories added successfully']);
} else {
    include 'index.html';
}
?>
