<?php
session_start();
include 'connectiondb/connection.php';

if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    // ✅ Remove session token in the main database
    $stmt = $conn->prepare("UPDATE registerlanding SET session_token=NULL WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    // ✅ List of subdomains for logout requests
    $subdomain_logout_urls = [
        "https://bpa.smartbarangayconnect.com/logout.php",
        "https://crms.unifiedlgu.com/logout.php",
        "https://businesspermit.unifiedlgu.com/logout.php",
    ];

    $postData = http_build_query(["email" => $email]);

    // ✅ Initialize multi cURL
    $mh = curl_multi_init();
    $curl_handles = [];

    foreach ($subdomain_logout_urls as $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignore SSL issues (for testing)
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Set timeout for the request
        
        curl_multi_add_handle($mh, $ch);
        $curl_handles[] = $ch;
    }

    // ✅ Execute all cURL handles concurrently
    do {
        $status = curl_multi_exec($mh, $active);
    } while ($status === CURLM_CALL_MULTI_PERFORM);

    while ($active && $status === CURLM_OK) {
        if (curl_multi_select($mh) !== -1) {
            do {
                $status = curl_multi_exec($mh, $active);
            } while ($status === CURLM_CALL_MULTI_PERFORM);
        }
    }

    // ✅ Check responses and close handles
    foreach ($curl_handles as $ch) {
        $response = curl_multi_getcontent($ch);
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response_code !== 200) {
            echo "❌ Subdomain logout request failed! URL: " . curl_getinfo($ch, CURLINFO_EFFECTIVE_URL) . 
                 " HTTP Code: $response_code, Response: $response <br>";
        } else {
            echo "✅ Subdomain logout successful: " . curl_getinfo($ch, CURLINFO_EFFECTIVE_URL) . " <br>";
        }

        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
    }

    curl_multi_close($mh);
}

// ✅ Destroy session
session_unset();
session_destroy();

// ✅ Remove session token in cookies (cross-domain)
setcookie("session_token", "", time() - 3600, "/", ".smartbarangayconnect.com", true, true);

// ✅ Redirect to main login page
header("Location: index.php");
exit();
?>



