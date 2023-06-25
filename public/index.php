<?php
// Make Sure This file is the only file in the public access folder
header("Content-Type: application/json");


include('../config.php');

function encryptString($string, $key) {
    $iv = openssl_random_pseudo_bytes(16); // Initialization Vector
    $cipherText = openssl_encrypt($string, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    $encryptedString = base64_encode($iv . $cipherText);
    return $encryptedString;
}

// Use this function on the otherside to decode and use with the Zoom API
// function decryptString($encryptedString, $key) {
//     $data = base64_decode($encryptedString);
//     $iv = substr($data, 0, 16); // Extract the Initialization Vector
//     $cipherText = substr($data, 16);
//     $decryptedString = openssl_decrypt($cipherText, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
//     return $decryptedString;
// }

// API Security - Can Further Improve with ip/domain locking etc..
if (!isset($_SERVER["HTTP_ACCESS_KEY"]) || $_SERVER["HTTP_ACCESS_KEY"] != $CONFIG["ACCESS_KEY"] ){
    echo json_encode(array("error"=>"Invalid access key"));
    exit();
}

$token_file = '../token.php'; // Change this path if needed. but make sure its not publicly accessible!
$token_json = json_decode(file_get_contents($token_file), true);

if ($token_json) {
    // Return Token if its valid
    if (isset($token_json["expires_at"]) && $token_json["expires_at"] - (1*60) > time()) {
        echo json_encode($token_json);
        exit();
    }
}

// If the file is missing and token is expired, this will regenerate the zoom token and store
$ZOOM_CLIENT_ID = $CONFIG['ZOOM_CLIENT_ID'];
$ZOOM_ACCOUNT_ID = $CONFIG['ZOOM_ACCOUNT_ID'];
$ZOOM_CLIENT_SECRET = $CONFIG['ZOOM_CLIENT_SECRET'];

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "https://zoom.us/oauth/token",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => 'grant_type=account_credentials&account_id=' . $ZOOM_ACCOUNT_ID,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/x-www-form-urlencoded',
        "Accept: application/json",
        "Authorization: Basic " .  base64_encode($ZOOM_CLIENT_ID . ':' . $ZOOM_CLIENT_SECRET),
        "content-type: application/json"
    ),
));

// Sample Response
// {"access_token":"....","token_type":"bearer","expires_in":3599,"scope":"user:write:admin..."}

$response = curl_exec($curl);
$response_json = json_decode($response, true);
$response_json["expires_at"] = time() + $response_json["expires_in"];
$zoom_keys_encrypted = encryptString(json_encode($response_json), $CONFIG["ENCRYPTION_KEY"]);
$store_response = json_encode(array("expires_at" => $response_json["expires_at"], "key"=> $zoom_keys_encrypted));

file_put_contents($token_file, $store_response );

// Sample Response
// { "expires_at": 1687695375, "key": "ENCRYPTED_JSON" }
echo $store_response;
exit();
