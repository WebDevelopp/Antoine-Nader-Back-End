<?php
class CategoryController {
    private $categoryModel;

    public function __construct($db) {
        $this->categoryModel = new CategoryModel($db);
    }

    public function addCategory() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'sku' => $_POST['sku'],
                'parent' => $_POST['parent'],
                'seo_keyword' => $_POST['seo_keyword'],
                'sort_order' => $_POST['sort_order'],
                'status' => $_POST['status']
            ];
            $this->categoryModel->addCategory($data);
            header('Location: /index.html');
        }
    }

    public function addMultipleCategories($items) {
        $this->categoryModel->addCategories($items);
    }

    public function getCategories() {
        return $this->categoryModel->getCategories();
    }

    public function deleteCategory($id) {
        $this->categoryModel->deleteCategory($id);
        header('Location: /index.html');
    }
}
?>
