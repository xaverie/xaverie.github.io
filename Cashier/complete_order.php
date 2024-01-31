<?php
@include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['order_id']) && is_numeric($_POST['order_id'])) {
        $orderId = $_POST['order_id'];
        echo 'Received order ID: ' . $orderId;

        $updateSql = "UPDATE orders SET queueStatus = 'Completed' WHERE order_id = $orderId";
        $stmt = mysqli_prepare($conn, $updateSql);

        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Order marked as completed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error marking order as completed']);
        }

        mysqli_stmt_close($stmt);   
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid Order ID']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

mysqli_close($conn);
?>

