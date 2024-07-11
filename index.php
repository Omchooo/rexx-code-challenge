<?php
require_once 'db.php';
require_once 'insert_data.php';
require_once 'fetch_data.php';

// Insert data from JSON if necessary
insertDataFromJson($conn);

$customers = fetchCustomers($conn);
$products = fetchProducts($conn);

$totalPrice = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer = $conn->real_escape_string($_POST['customer'] ?? '');
    $product = $conn->real_escape_string($_POST['product'] ?? '');
    $price = $conn->real_escape_string($_POST['price'] ?? '');

    $query = "SELECT Orders.order_id, Customers.name AS customer_name, Products.name AS product_name, Orders.quantity, Orders.total_price
              FROM Orders
              JOIN Customers ON Orders.customer_id = Customers.customer_id
              JOIN Products ON Orders.product_id = Products.product_id
              WHERE 1=1";

    if (!empty($customer)) {
        $query .= " AND Orders.customer_id = " . intval($customer);
    }
    if (!empty($product)) {
        $query .= " AND Orders.product_id = " . intval($product);
    }
    if (!empty($price)) {
        $query .= " AND Orders.total_price <= " . floatval($price);
    }

    $result = $conn->query($query);
    if (!$result) {
        die("Error executing query: " . $conn->error);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Filter</title>
</head>
<body>

<form method="POST" action="">
    <label for="customer">Customer:</label>
    <select name="customer" id="customer">
        <option value="">Select Customer</option>
        <?php foreach ($customers as $customer): ?>
            <option value="<?= htmlspecialchars($customer['customer_id'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($customer['name'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>

    <label for="product">Product:</label>
    <select name="product" id="product">
        <option value="">Select Product</option>
        <?php foreach ($products as $product): ?>
            <option value="<?= htmlspecialchars($product['product_id'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>

    <label for="price">Price:</label>
    <input type="number" name="price" id="price" step="0.01">

    <button type="submit">Filter</button>
</form>

<?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && $result && $result->num_rows > 0): ?>
    <table border='1'>
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Product</th>
            <th>Quantity</th>
            <th>Total Price</th>
        </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['order_id'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($row['customer_name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($row['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($row['quantity'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($row['total_price'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <?php $totalPrice += $row['total_price']; ?>
    <?php endwhile; ?>
        <tr>
            <td colspan='4'>Total</td>
            <td><?= htmlspecialchars($totalPrice, ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
    </table>
<?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
    <p>No results found for the given filters.</p>
<?php endif; ?>

</body>
</html>

<?php
$conn->close();
?>