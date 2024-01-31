<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Camera</title>
</head>
<body>
    <!-- Add a button to trigger camera capture -->
    <button id="openCameraBtn">Open Camera</button>

    <!-- Video element to display the camera stream -->
    <video id="cameraStream" autoplay style="width: 100%; max-width: 600px;"></video>

    <!-- Button to capture a photo -->
    <button id="capturePhotoBtn" style="display: none;">Capture Photo</button>

    <!-- Image element to display the captured photo -->
    <img id="capturedPhoto" style="display: none;">

    <script>
        let videoStream;
        let photoCanvas;
        let photoContext;

        // Function to open the camera when the button is clicked
        async function openCamera() {
            try {
                // Access the user's camera
                videoStream = await navigator.mediaDevices.getUserMedia({ video: true });

                // Display the camera stream in a video element
                const videoElement = document.getElementById('cameraStream');
                videoElement.srcObject = videoStream;

                // Show the video element and the photo capture button
                videoElement.style.display = 'block';
                document.getElementById('capturePhotoBtn').style.display = 'block';

                // Initialize canvas for capturing photos
                photoCanvas = document.createElement('canvas');
                photoContext = photoCanvas.getContext('2d');
            } catch (error) {
                console.error('Error accessing the camera:', error);
            }
        }

        // Function to capture a photo
        function capturePhoto() {
            // Set the canvas size to match the video stream
            photoCanvas.width = videoStream.getVideoTracks()[0].getSettings().width;
            photoCanvas.height = videoStream.getVideoTracks()[0].getSettings().height;

            // Draw the current frame from the video stream onto the canvas
            photoContext.drawImage(document.getElementById('cameraStream'), 0, 0);

            // Display the captured photo in an image element
            const capturedPhotoElement = document.getElementById('capturedPhoto');
            capturedPhotoElement.src = photoCanvas.toDataURL('image/png');
            capturedPhotoElement.style.display = 'block';

            // Stop the video stream (optional, depending on your use case)
            videoStream.getTracks().forEach(track => track.stop());
        }

        // Add event listeners to the buttons
        document.getElementById('openCameraBtn').addEventListener('click', openCamera);
        document.getElementById('capturePhotoBtn').addEventListener('click', capturePhoto);
    </script>
</body>
</html>
