<?php include 'config.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Financial Report</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>Financial Report</h2>
    <form method="get" action="">
        <div class="form-row">
            <div class="form-group col-md-5">
                <label for="start_date">Start Date:</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>">
            </div>
            <div class="form-group col-md-5">
                <label for="end_date">End Date:</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>">
            </div>
            <div class="form-group col-md-2">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary btn-block">Filter</button>
            </div>
        </div>
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Harga Jual</th>
                <th>Discount</th>
                <th>Total</th>
                <th>Profit</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '1970-01-01';
            $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
            $sql = "SELECT p.merk, p.name, s.quantity, s.price, s.discount, s.total, s.date, (s.price - p.hargabeli) * s.quantity - s.discount as profit 
                    FROM sales s 
                    JOIN products p ON s.product_id = p.id 
                    WHERE s.date BETWEEN '$start_date' AND '$end_date'
                    ORDER BY s.date DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["merk"] . " " . $row["name"] . "</td>";
                    echo "<td>" . $row["quantity"] . "</td>";
                    echo "<td>Rp " . number_format($row["price"], 0, ',', '.') . "</td>";
                    echo "<td>Rp " . number_format($row["discount"], 0, ',', '.') . "</td>";
                    echo "<td>Rp " . number_format($row["total"], 0, ',', '.') . "</td>";
                    echo "<td>Rp " . number_format($row["profit"], 0, ',', '.') . "</td>";
                    echo "<td>" . $row["date"] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No sales found</td></tr>";
            }
            $conn->close();
            ?>
        </tbody>
    </table>
</div>
</body>
</html>
