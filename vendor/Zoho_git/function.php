<?php
function zohoApiResponce($AuthToken,$request_url,$method_name,$request_parameters){
	$getUrl = explode('/',$request_url);
$ch = curl_init();
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
/* Here you can set all Parameters based on your method_name */
 if ($method_name == 'GET')
 {
 $request_url .= '?' . http_build_query($request_parameters);
 }
if ($method_name == 'POST')
 {
 curl_setopt($ch, CURLOPT_POST, TRUE);
 curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request_parameters));
//curl_setopt($ch, CURLOPT_POSTFIELDS, $request_parameters);
 }
if ($mtehod_name == 'DELETE')
 {
 curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
 curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request_parameters));
 $request_url .= '?' . http_build_query($request_parameters);
 }
/* Here you can set the Response Content Type */
 curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
/* Let's give the Request Url to Curl */
 curl_setopt($ch, CURLOPT_URL, $request_url);
/*
 Yes we want to get the Response Header
 (it will be mixed with the response body but we'll separate that after)
 */
 curl_setopt($ch, CURLOPT_HEADER, TRUE);
/* Allows Curl to connect to an API server through HTTPS */
 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
/* Let's get the Response ! */
 $response = curl_exec($ch);
/* We need to get Curl infos for the http_code */
 $response_info = curl_getinfo($ch);
/* Don't forget to close Curl */
 curl_close($ch);
/* Here we get the Response Body */
 $response_body = substr($response, $response_info['header_size']);
// Response Body
	 $data = json_decode($response_body);
	 $array = json_decode(json_encode($data), True);
 return $array;
 //return $getUrl;
 }

function prepare_curl_statement($request_url, $type, $parameters = NULL, $uploaddoc, $json = TRUE) {
// Initalise the curl object.
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/xml"));
curl_setopt($ch, CURLOPT_URL,$request_url);
curl_setopt($ch, CURLOPT_POST,1);
//curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
curl_setopt_custom_postfields($ch, $parameters);
$result = curl_exec($ch);
$response_info = curl_getinfo($ch);
curl_close ($ch);
$response_body_data = substr($result, $response_info['header_size']);
// Response Body
$data = json_decode($result);
	 $array = json_decode(json_encode($data), True);
 return $array;
}



function curl_setopt_custom_postfields($ch, $postfields, $headers = null) {
$algos = hash_algos();
$hashAlgo = null;
foreach ( array('sha1', 'md5') as $preferred ) {
if ( in_array($preferred, $algos) ) {
$hashAlgo = $preferred;
break;
}
}
if ( $hashAlgo === null ) { list($hashAlgo) = $algos; }
$boundary =
'----------------------------' .
substr(hash($hashAlgo, 'cURL-php-multiple-value-same-key-support' . microtime()), 0, 12);

$body = array();
$crlf = "\r\n";
$fields = array();
foreach ( $postfields as $key => $value ) {
if ( is_array($value) ) {
foreach ( $value as $v ) {
$fields[] = array($key, $v);
}
} else {
$fields[] = array($key, $value);
}
}
foreach ( $fields as $field ) {
list($key, $value) = $field;
if ( strpos($value, '@') === 0 ) {
preg_match('/^@(.*?)$/', $value, $matches);
list($dummy, $filename) = $matches;
$body[] = '--' . $boundary;
$body[] = 'Content-Disposition: form-data; name="' . $key . '"; filename="' . basename($filename) . '"';
$body[] = 'Content-Type: application/octet-stream';
$body[] = '';
$body[] = file_get_contents($filename);
} else {
$body[] = '--' . $boundary;
$body[] = 'Content-Disposition: form-data; name="' . $key . '"';
$body[] = '';
$body[] = $value;
}
}
$body[] = '--' . $boundary . '--';
$body[] = '';
$contentType = 'multipart/form-data; boundary=' . $boundary;
$contenttt = join($crlf, $body);
$contentLength = strlen($contenttt);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
'Content-Length: ' . $contentLength,
'Expect: 100-continue',
'Content-Type: ' . $contentType,
));
curl_setopt($ch, CURLOPT_POSTFIELDS, $contenttt);
}
	 
	 
?>