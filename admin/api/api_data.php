 <?php
 $apiUrl = "https://oarsmart.oas.psu.ac.th/preview/ws_shirt";

        // กำหนด API Key
        $apiKey = "OAR123456";

        // สร้าง cURL request
        $ch = curl_init($apiUrl);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        // ประมวลผลผลลัพธ์
        if ($response !== false) {
            $result = json_decode($response, true);

            if ($result["status"] === "success") {
                echo '<pre>';
                print_r($result["data"]);
                echo '</pre>';
            } else {
                echo $result["status"]. " (" . $result["data"]. ")";
            }
        } else {
            echo "ไม่สามารถเชื่อมต่อกับ API ได้";
        }