<?php
require 'vendor/autoload.php';
$json = file_get_contents("php://input");
echo file_put_contents('webhook.txt',$json);
$data =  json_decode($json);
$array = json_decode(json_encode($data), True);
$event_type =  $array['event_type'];
$event_action =  $array['object_attributes']['action'];
$lable = $array['labels']['0']['title'];
$AuthToken = 'c8eb44be3bca2c3caf5c76b7539e6e92';
$Get_method_name = 'GET';
$Post_method_name = 'POST';
//~~~~~~~~~~~~~~~~~~~~~~ zoho project data ~~~~~~~~~~~~~~//
$Get_request_parameters = array(
	 'authtoken' => $AuthToken,
	 'index' => 1
	  ); 
	  
//~  Get Portal id ~//
$PortalRequest_url = 'https://projectsapi.zoho.com/restapi/portals/';
$PortalResponse = zohoApiResponce($AuthToken,$PortalRequest_url,$Get_method_name,$Get_request_parameters) ;

//~ Get All Projects ~//
//echo "====================== All Project Response=======================";
 $portal_id =  $PortalResponse['portals']['0']['id'];
 $AllProjec_request_parameters = array(
	 'authtoken' => $AuthToken,
	 'status' => 'active',
	 'sort_column' => 'created_time',
	 'sort_order' =>'ascending'
	  ); 
 $AllProjecRequest_url = 'https://projectsapi.zoho.com/restapi/portal/'.$portal_id.'/projects/';
$AllProjectResponse = zohoApiResponce($AuthToken,$AllProjecRequest_url,$Get_method_name,$AllProjec_request_parameters) ;

$zohoProjectid =  $AllProjectResponse['projects']['0']['id'];

$Bug_request_parameters = array(
	 'authtoken' => $AuthToken,
	 'statustype' => 'open',
	  ); 
$BugRequest_url = 'https://projectsapi.zoho.com/restapi/portal/'.$portal_id.'/projects/'.$zohoProjectid.'/bugs/';
$BugResponse = zohoApiResponce($AuthToken,$BugRequest_url,$Get_method_name,$Bug_request_parameters);
//~~~~~~~~~~~~~~~~~~~~~~~~~end ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//
if($event_type =='issue' && $event_action =='open'){
	$description =  $array['object_attributes']['description'];
	preg_match('#\(/(.*?)\)#', $description, $match);
	$path = pathinfo($match['1']);
	$str = $path['filename'];
	$finalstr = '!['.$str.']';
	$desc_str = str_replace($match['0'],'',$description);
	$finaldesc_str = str_replace($finalstr,'',$desc_str);
	$issue_id = $array['object_attributes']['id'];
	$iid =  $array['object_attributes']['iid'];
	$project_id  =  $array['object_attributes']['project_id'];
	$title  =  $array['object_attributes']['title'];
	$assignee = $array['assignee']['name'];
	//$user_notes_count = $value['user_notes_count'];	
	$issue_web_url =$array['object_attributes']['url'];
	$web_url = $array['project']['homepage'];
	$Subsystems_value = 'Release planner';
	$Planned_Release_values = 'Not Planned Yet';
	$severity_id = '981042000000007007';
		// if($priority == 'High'){
			// $severity_id = '981042000000007005';
		// }else if($priority == 'Medium'){
			// $severity_id = '981042000000007003';
		// }else if($priority == 'Low'){
			// $severity_id = '981042000000007007';
		// }
		
	if(!empty($match['1'])){
		$attachment = $web_url.'/'.$match['1'];
			$uploaddoc = array('@'.$attachment);
								// $files = 	json_encode($file,true);
								 $CreateBug_request_parameters = array(
								'authtoken' => $AuthToken,
								'title' =>$title,
								'description' =>$finaldesc_str,
								'assignee' =>$created_person, // not available 
								'severity_id' =>$severity_id, // not available 
								'uploaddoc' => $uploaddoc,
								'CHAR1' =>'632412251',
								'CHAR2' =>'Device',
								'CHAR4' =>$Subsystems_value, // not available
								'CHAR5' =>$Planned_Release_values, // not available
								'LONG1' => $issue_id
							 );
							 	$create_bug_request_url  = 'https://projectsapi.zoho.com/restapi/portal/'.$portal_id.'/projects/'.$zohoProjectid.'/bugs/';	 
								$CreateBugResponse = prepare_curl_statement($create_bug_request_url,$Post_method_name, $CreateBug_request_parameters, $uploaddoc);
		
	}else{
		$CreateBug_request_parameters = array(
								'authtoken' => $AuthToken,
								'title' =>$title,
								'description' =>$finaldesc_str,
								'assignee' =>$created_person, // not available 
								'severity_id' =>$severity_id, // not available 
								'CHAR1' =>'632412251',
								'CHAR2' =>'Device',
								'CHAR4' =>$Subsystems_value, // not available
								'CHAR5' =>$Planned_Release_values, // not available
								'LONG1' => $issue_id
							); 
								$CreateBugRequest_url = 'https://projectsapi.zoho.com/restapi/portal/'.$portal_id.'/projects/'.$zohoProjectid.'/bugs/';
								$CreateBugResponse = zohoApiResponce($AuthToken,$CreateBugRequest_url,$Post_method_name,$CreateBug_request_parameters) ; 
	}

}elseif($event_type =='issue' && $event_action =='update'){
	
	$issue_id = $array['object_attributes']['id'];
	foreach($BugResponse['bugs'] as $bugsvalue){
		if ($bugsvalue['customfields']['0']['label_name'] == 'GitlabID' &&  $bugsvalue['customfields']['0']['value'] == $issue_id){	
			 $BugResponse_id = $bugsvalue['id_string'];
		}
	}
$config['sync_status'] = array(
'todo' => 'Open',
'Doing' => 'In progress',
'QA' => 'To be tested',
);	  
	 if(!empty($lable)){
		if($config['sync_status'][$lable] == 'Open'){
			$status_id = '981042000000007045';	
		}elseif ($config['sync_status'][$lable] == 'In progress'){
			$status_id = '981042000000179001';
		}elseif ($config['sync_status'][$lable] == 'To be tested'){
			$status_id = '981042000000179008';
		}else{
			$status_id = '981042000000007045';
		}
	 } 
	$description =  $array['object_attributes']['description'];
	preg_match('#\(/(.*?)\)#', $description, $match);
	$path = pathinfo($match['1']);
	$str = $path['filename'];
	$finalstr = '!['.$str.']';
	$desc_str = str_replace($match['0'],'',$description);
	$finaldesc_str = str_replace($finalstr,'',$desc_str);
	$iid =  $array['object_attributes']['iid'];
	$project_id  =  $array['object_attributes']['project_id'];
	$title  =  $array['object_attributes']['title'];
	$assignee = $array['assignee']['name'];
	//$user_notes_count = $value['user_notes_count'];	
	$issue_web_url =$array['object_attributes']['url'];
	$web_url = $array['project']['homepage'];
	$Subsystems_value = 'Release planner';
	$Planned_Release_values = 'Not Planned Yet';
	$severity_id = '981042000000007007';
		// if($priority == 'High'){
			// $severity_id = '981042000000007005';
		// }else if($priority == 'Medium'){
			// $severity_id = '981042000000007003';
		// }else if($priority == 'Low'){
			// $severity_id = '981042000000007007';
		// } 	
	if(!empty($match['1'])){
		$attachment = $web_url.'/'.$match['1'];
			$uploaddoc = array('@'.$attachment);
								// $files = 	json_encode($file,true);
								 $CreateBug_request_parameters = array(
								'authtoken' => $AuthToken,
								'title' =>$title,
								'description' =>$finaldesc_str,
								'assignee' =>$created_person, // not available 
								'severity_id' =>$severity_id, // not available 
								'uploaddoc' => $uploaddoc,
								'status_id' => $status_id,
								'CHAR1' =>'632412251',
								'CHAR2' =>'Device',
								'CHAR4' =>$Subsystems_value, // not available
								'CHAR5' =>$Planned_Release_values, // not available
								'LONG1' => $issue_id
							 );
							 	$BugupdateRequest_url  = 'https://projectsapi.zoho.com/restapi/portal/'.$portal_id.'/projects/'.$zohoProjectid.'/bugs/'.$BugResponse_id.'/';	 
								$updateBugResponse = prepare_curl_statement($BugupdateRequest_url, 'POST', $CreateBug_request_parameters, $uploaddoc);
	}else{
		$CreateBug_request_parameters = array(
								'authtoken' => $AuthToken,
								'title' =>$title,
								'description' =>$finaldesc_str,
								'assignee' =>$created_person, // not available 
								'severity_id' =>$severity_id, // not available 
								'status_id' => $status_id,
								'CHAR1' =>'632412251',
								'CHAR2' =>'Device',
								'CHAR4' =>$Subsystems_value, // not available
								'CHAR5' =>$Planned_Release_values, // not available
								'LONG1' => $issue_id
							); 
								$BugupdateRequest_url = 'https://projectsapi.zoho.com/restapi/portal/'.$portal_id.'/projects/'.$zohoProjectid.'/bugs/'.$BugResponse_id.'/';
								$updateBugResponse = zohoApiResponce($AuthToken,$BugupdateRequest_url,$Post_method_name,$CreateBug_request_parameters) ; 
	}
	
}elseif($event_type =='note'){
	$issue_id = $array['issue']['id'];
	foreach($BugResponse['bugs'] as $bugsvalue){
		if ($bugsvalue['customfields']['0']['label_name'] == 'GitlabID' &&  $bugsvalue['customfields']['0']['value'] == $issue_id){	
			 $BugResponse_id = $bugsvalue['id_string'];
		}
	}
			$content = $array['object_attributes']['note'];
			$Bugcomments_request_parameters = array(
				 'authtoken' => $AuthToken,
				 'content' =>$content,
				 );	
			$BugCommentsRequest_url = 'https://projectsapi.zoho.com/restapi/portal/'.$portal_id.'/projects/'.$zohoProjectid.'/bugs/'.$BugResponse_id.'/comments/';
			$CommentsBugResponse = zohoApiResponce($AuthToken,$BugCommentsRequest_url,$Post_method_name,$Bugcomments_request_parameters) ;
} 

function zohoApiResponce($AuthToken,$request_url,$method_name,$request_parameters){
$ch = curl_init();
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
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
 curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
 curl_setopt($ch, CURLOPT_URL, $request_url);
 curl_setopt($ch, CURLOPT_HEADER, TRUE);
 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
 $response = curl_exec($ch);
 $response_info = curl_getinfo($ch);
 curl_close($ch);
 $response_body = substr($response, $response_info['header_size']);
	 $data = json_decode($response_body);
	 $array = json_decode(json_encode($data), True);
 return $array;
 }

function prepare_curl_statement($request_url, $type, $parameters = NULL, $uploaddoc, $json = TRUE) {
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