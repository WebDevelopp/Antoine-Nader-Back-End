<?php
class CategoryModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function addCategory($data) {
        $sql = "INSERT INTO category (sku, parent, seo_keyword, sort_order, status) VALUES (:sku, :parent, :seo_keyword, :sort_order, :status)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'sku' => $data['sku'],
            'parent' => $data['parent'],
            'seo_keyword' => $data['seo_keyword'],
            'sort_order' => $data['sort_order'],
            'status' => $data['status'],
        ]);
    }

    public function addCategories(array $items) {
        $sql = "INSERT INTO category (sku, parent, seo_keyword, sort_order, status) VALUES (:sku, :parent, :seo_keyword, :sort_order, :status)";
        $stmt = $this->db->prepare($sql);

        foreach ($items as $data) {
            $stmt->execute([
                'sku' => $data['sku'],
                'parent' => $data['parent'],
                'seo_keyword' => $data['seo_keyword'],
                'sort_order' => $data['sort_order'],
                'status' => $data['status'],
            ]);
        }
    }

    public function getCategories() {
        $sql = "SELECT * FROM category";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteCategory($id) {
        $sql = "DELETE FROM category WHERE category_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
    }
}
?>