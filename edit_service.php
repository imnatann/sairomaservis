<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch service details
    $stmt = $conn->prepare("SELECT * FROM services WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $service = $result->fetch_assoc();
    } else {
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Service not found'
                }).then(function() {
                    window.location = 'index.php';
                });
              </script>";
        exit();
    }
}

if (isset($_POST['edit_service'])) {
    $id = $_POST['id'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    $cost = $_POST['cost'];

    // Fetch current used products
    $service_products = [];
    if (isset($_POST['used_products'])) {
        $service_products = $_POST['used_products'];
    } else {
        $stmt = $conn->prepare("SELECT product_id FROM service_products WHERE service_id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $service_products[] = $row['product_id'];
            }
        }
    }

    // Update service
    $stmt = $conn->prepare("UPDATE services SET description=?, status=?, cost=?, updated_at=NOW() WHERE id=?");
    $stmt->bind_param("ssdi", $description, $status, $cost, $id);
    if ($stmt->execute() === TRUE) {
        // Update used products
        $stmt = $conn->prepare("DELETE FROM service_products WHERE service_id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        if (!empty($_POST['used_products'])) {
            foreach ($_POST['used_products'] as $product_id) {
                $stmt = $conn->prepare("INSERT INTO service_products (service_id, product_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $id, $product_id);
                $stmt->execute();
                $stmt = $conn->prepare("UPDATE products SET stock = stock - 1 WHERE id=?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
            }
        }
        echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Service updated successfully'
                }).then(function() {
                    window.location = 'index.php';
                });
              </script>";
    } else {
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error: " . $stmt->error . "'
                }).then(function() {
                    window.location = 'index.php';
                });
              </script>";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Service</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="container mt-4">
    <h2>Edit Service</h2>
    <form method="post" action="" class="needs-validation" novalidate>
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($service['id']); ?>">
        <div class="form-group was-validated">
            <label for="description">Description:</label>
            <textarea class="form-control" id="description" name="description" required><?php echo htmlspecialchars($service['description']); ?></textarea>
        </div>
        <div class="form-group was-validated">
            <label for="cost">Cost:</label>
            <input type="number" class="form-control" id="cost" name="cost" value="<?php echo htmlspecialchars($service['cost']); ?>" step="0.01">
        </div>
        <div class="form-group was-validated">
            <label for="used_products">Used Products:</label>
            <select class="form-control" id="used_products" name="used_products[]" multiple>
                <?php $service_products = isset($service_products) ? $service_products : []; ?>
                <?php
                $sql = "SELECT id, name, hargabeli FROM products";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $selected = in_array($row['id'], $service_products) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($row['id']) . "' data-price='" . $row['hargabeli'] . "' $selected>" . htmlspecialchars($row['name']) . " - Rp " . number_format($row['hargabeli'], 0, ',', '.') . "</option>";
                    }
                } else {
                    echo "<option value=''>No products available</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group was-validated">
            <label for="total_cost">Total Cost:</label>
            <input type="text" class="form-control" id="total_cost" name="total_cost" readonly>
        </div>
        <div class="form-group">
            <label for="profit">Profit:</label>
            <input type="text" class="form-control" id="profit" name="profit" readonly>
        </div>
        <div class="form-group">
            <label for="status">Status:</label>
            <select class="form-control" id="status" name="status" required>
                <option value="Pending" <?php if ($service['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                <option value="In Progress" <?php if ($service['status'] == 'In Progress') echo 'selected'; ?>>In Progress</option>
                <option value="Completed" <?php if ($service['status'] == 'Completed') echo 'selected'; ?>>Completed</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" name="edit_service">Save Changes</button>
    </form>
    <script>
        // Example starter JavaScript for disabling form submissions if there are invalid fields
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                // Fetch all the forms we want to apply custom Bootstrap validation styles to
                var forms = document.getElementsByClassName('needs-validation');
                // Loop over them and prevent submission
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    </script>
    </form>
</div>
<script>
    document.getElementById('used_products').addEventListener('change', function() {
        let totalCost = 0;
        let selectedOptions = this.selectedOptions;
        for (let i = 0; i < selectedOptions.length; i++) {
            totalCost += parseFloat(selectedOptions[i].getAttribute('data-price'));
        }
        document.getElementById('total_cost').value = 'Rp ' + totalCost.toLocaleString('id-ID');
        calculateProfit();
    });

    document.getElementById('cost').addEventListener('input', calculateProfit);

    function calculateProfit() {
        let totalCost = parseFloat(document.getElementById('total_cost').value.replace(/[^0-9.-]+/g,""));
        let serviceCost = parseFloat(document.getElementById('cost').value);
        let profit = serviceCost - totalCost;
        document.getElementById('profit').value = 'Rp ' + profit.toLocaleString('id-ID');
    }
</script>
<?php $conn->close(); ?>
</body>
</html>
