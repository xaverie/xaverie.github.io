<<?php 
include 'connection.php';
?>

<?php
$sql = "SELECT *, MOP FROM orders WHERE paymentStatus = 'Processing'";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
?>
    <div class="modal" id="discountModal<?php echo $row['order_id']; ?>">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order <?php echo $row['order_id']; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="height: 500px;">
                    <h3 class="header text-center">Table <?php echo $row['tableNo']; ?></h3>
                    <h3 class="header text-center">Order No. <?php echo $row['order_id']; ?></h3>
                    <br>
                    <br>
                    <h6>Order Details: </h6>
                    <p><?php echo $row['order_details']; ?></p>
                    <p><?php echo $row['price']; ?></p>

                    <form method="post">
                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                        <button type="submit" name="proceed_to_payment">Proceed</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php } ?>