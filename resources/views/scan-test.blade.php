<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Scanner Test</title>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body>
    <h1>Test QR / Barcode Scanner</h1>
    <div id="reader" style="width: 300px;"></div>
    <p id="result"></p>

    <script>
        const resultContainer = document.getElementById('result');
        const html5QrCode = new Html5Qrcode("reader");

        Html5Qrcode.getCameras().then(devices => {
            if (devices && devices.length) {
                const cameraId = devices[0].id;
                html5QrCode.start(
                    cameraId,
                    {
                        fps: 10,
                        qrbox: 250
                    },
                    qrCodeMessage => {
                        resultContainer.innerText = "QR Code: " + qrCodeMessage;
                        html5QrCode.stop();
                    },
                    errorMessage => {
                        // ignore
                    }
                );
            } else {
                resultContainer.innerText = "No cameras found.";
            }
        }).catch(err => {
            resultContainer.innerText = "Camera error: " + err;
        });
    </script>
</body>
</html>
