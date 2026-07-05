<?php
$stock_table = "Pharmacy";
$status = "active";

// Use prepared statement for safety
$stmt = $db->prepare("SELECT * FROM stock_table WHERE stock_table = :stock_table AND status = :status");
$stmt->bindValue(':stock_table', $stock_table, PDO::PARAM_STR);
$stmt->bindValue(':status', $status, PDO::PARAM_STR);
$stmt->execute();
?>

<div class="row">
    <div class="col-lg-12">
        <div class="ibox">
            <div class="ibox-title">
                <h5>Price Enquiry</h5>
            </div>
            <div class="ibox-content">
                <?php if ($stmt->rowCount() > 0) { ?>
                    <table class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Cost</th>
                                <th>Hosp/HMO</th>
                                <th>Cash Price</th>
                                <th>Unit</th>
                                <th>Qty</th>
                                <th>R/Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $n = 1;
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                <tr>
                                    <td><?php echo $n++; ?></td>
                                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td><?php echo number_format($row['buying_cost'], 2); ?></td>
                                    <td><?php echo number_format($row['hosp_price'], 2); ?></td>
                                    <td><?php echo number_format($row['cash_price'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($row['stock_total_unit']); ?></td>
                                    <td><?php echo $row['qty']; ?></td>
                                    <td><?php echo $row['reorder_level']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <div class="alert alert-warning">No Records to show</div>
                <?php } ?>

                <hr>
                <a href="index.php" class="btn btn-danger btn-xs">Close</a>
            </div>
        </div>
    </div>
</div>