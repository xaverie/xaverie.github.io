<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the Base64-encoded image data from the form
    $capturedImageBase64 = $_POST['captured_image'];

    // Decode the Base64 data
    $capturedImage = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $capturedImageBase64));

    // Specify the folder where you want to save the image
    $savePath = 'capturePhoto';

    // Generate a unique filename (you may want to use a more robust method)
    $filename = $savePath . 'captured_photo_' . time() . '.png';

    // Save the image to the specified folder
    file_put_contents($filename, $capturedImage);

    // Optionally, you can send a response back to the client if needed
    echo json_encode(['status' => 'success', 'message' => 'Photo saved successfully']);
} else {
    // Handle invalid requests or direct access to this file
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
