<?php

$DEVICEID = "";
$ACCESS_TOKEN = "";

function outputData($data) {
    header("Content-type: text/plain");
    echo($data);
}

function clearAlarm() {
    global $ACCESS_TOKEN;
    $data = sprintf("access_token=%s&args=off", $ACCESS_TOKEN);
    $res = postRequest($data);
    $json = json_decode($res);
    if ($json->return_value == 0) {
        outputData("CLEAR");
    } else {
        throw new Exception("Clear failed");
    }
}

function soundAlarm() {
    global $ACCESS_TOKEN;
    $data = sprintf("access_token=%s&args=alarm", $ACCESS_TOKEN);
    $res = postRequest($data);
    $json = json_decode($res);
    if ($json->return_value == 1) {
        outputData("SOUNDING");
    } else {
        throw new Exception("Sounding failed");
    }
}

function postRequest($data=null) {
    global $DEVICEID;
    $url = "https://api.spark.io/v1/devices/".$DEVICEID."/state";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $sc = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($sc != 200) { throw new Exception(sprintf("HTTP CODE: %s", $sc)); }
    return $response;
}

function checkStatus() {
    global $DEVICEID, $ACCESS_TOKEN;
    $url = "https://api.spark.io/v1/devices/".$DEVICEID."/alarm_active?access_token=".$ACCESS_TOKEN;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $sc = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($sc != 200) { throw new Exception(sprintf("HTTP CODE: %s", $sc)); }
    $json = json_decode($response);
    if ($json->result == 0) {
        outputData("OFF");
    } else {
        outputData("ACTIVE");
    }    
}

// Get web data
$action = isset($_GET['action']) ? strtolower(substr($_GET['action'], 0, 5)) : null;

// The "main" function
try {
    switch ($action) {
        case "clear":
            clearAlarm();
            break;
        case "sound":
            soundAlarm();
            break;
        case "check":
            checkStatus();
            break;
        default:
            throw new Exception("No proper action requested");
    }
} catch(Exception $e) {
    outputData(sprintf("ERROR: %s\n", $e->getMessage()));
}

exit;

?>
