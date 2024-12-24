<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS Order</title>
</head>
<body>
    <h1>SMS Order Webpage</h1>

    <form method="POST" action="">
        <label for="apiKey">API Key:</label>
        <input type="text" id="apiKey" name="apiKey" placeholder="Enter API Key" required><br><br>

        <label for="countryCode">Country Code:</label>
        <input type="text" id="countryCode" name="countryCode" placeholder="Enter ISO Country Code (e.g., US)" required><br><br>

        <label for="countryId">Country ID:</label>
        <input type="text" id="countryId" name="countryId" placeholder="Enter Country ID" required><br><br>

        <label for="maxPrice">Max Price:</label>
        <input type="text" id="maxPrice" name="maxPrice" placeholder="Enter Max Price (e.g., 0.5)" required><br><br>

        <button type="submit">Order SMS</button>
    </form>

    <h2>Status:</h2>
    <p id="status">
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $apiKey = $_POST['apiKey'];
            $countryCode = $_POST['countryCode'];
            $countryId = $_POST['countryId'];
            $maxPrice = $_POST['maxPrice'];

            $url = "https://api.smspool.net/purchase/sms";
            $payload = json_encode([
                "country" => $countryCode,
                "service" => 1034,
                "max_price" => (float)$maxPrice,
                "quantity" => 1
            ]);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json",
                "Authorization: Bearer $apiKey"
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

            $retries = 0;
            $maxRetries = 10;
            $success = false;

            while ($retries < $maxRetries) {
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($httpCode === 200) {
                    $data = json_decode($response, true);
                    if ($data['success']) {
                        echo "Number generated successfully! Order ID: " . $data['orderid'] . " Number: " . $data['number'];
                        $success = true;
                        break;
                    } else {
                        echo "Error: " . ($data['message'] ?? 'Unknown error.');
                    }
                } else {
                    echo "HTTP Error: $httpCode";
                }

                $retries++;
                echo " Retrying... ($retries/$maxRetries)";
                sleep(2);
            }

            if (!$success) {
                echo "Error: All retries failed.";
            }

            curl_close($ch);
        }
        ?>
    </p>

    <h2>Generated Number:</h2>
    <input type="text" id="numberGenerated" value="" readonly>
</body>
</html>
