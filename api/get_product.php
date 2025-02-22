<?php
header('Content-Type: application/json');
include 'config.php';
include 'navbar.html';


if (isset($_GET['id']) || isset($_GET['category'])) {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $sql = "SELECT * FROM products WHERE id='$id'";
    } else {
        $category = $_GET['category'];
        $sql = "SELECT * FROM products WHERE kategori='$category'";
    }

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        echo json_encode($products);
    } else {
        echo json_encode(["error" => "No products found"]);
    }
} else {
    echo json_encode(["error" => "No products found"]);
}

$conn->close();
?>
