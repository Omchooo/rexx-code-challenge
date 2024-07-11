<?php
require_once 'db.php';

function fetchCustomers($conn) {
    $result = $conn->query("SELECT * FROM Customers");
    if (!$result) {
        die("Error fetching customers: " . $conn->error);
    }
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
    return $customers;
}

function fetchProducts($conn) {
    $result = $conn->query("SELECT * FROM Products");
    if (!$result) {
        die("Error fetching products: " . $conn->error);
    }
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    return $products;
}
?>