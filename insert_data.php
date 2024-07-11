<?php
// Function to insert data from JSON
function insertDataFromJson($conn) {
    $jsonData = file_get_contents('data.json');
    $data = json_decode($jsonData, true);

    foreach ($data as $sale) {
        // Check if customer already exists
        $stmt = $conn->prepare("SELECT customer_id FROM Customers WHERE email = ?");
        $stmt->bind_param("s", $sale['customer_mail']);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            // Customer exists, bind the result to the $customer_id variable
            $stmt->bind_result($customer_id);
            $stmt->fetch();
        } else {
            // Insert customer
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO Customers (name, email) VALUES (?, ?)");
            $stmt->bind_param("ss", $sale['customer_name'], $sale['customer_mail']);
            $stmt->execute();
            $customer_id = $stmt->insert_id;
        }
        $stmt->close();

        // Check if product already exists
        $stmt = $conn->prepare("SELECT product_id FROM Products WHERE product_id = ?");
        $stmt->bind_param("i", $sale['product_id']);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows == 0) {
            // Insert product
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO Products (product_id, name, price) VALUES (?, ?, ?)");
            $stmt->bind_param("isd", $sale['product_id'], $sale['product_name'], $sale['product_price']);
            $stmt->execute();
        }
        $stmt->close();

        // Check if order already exists
        $stmt = $conn->prepare("SELECT order_id FROM Orders WHERE order_id = ?");
        $stmt->bind_param("i", $sale['sale_id']);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 0) {
            // Insert order
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO Orders (order_id, customer_id, product_id, quantity, total_price, created_at) VALUES (?, ?, ?, ?, ?, ?)");
            $quantity = 1; // Assuming quantity is always 1 as it's not in the JSON
            $stmt->bind_param("iiidss", $sale['sale_id'], $customer_id, $sale['product_id'], $quantity, $sale['product_price'], $sale['sale_date']);
            $stmt->execute();
        }
        $stmt->close();
    }
}
?>