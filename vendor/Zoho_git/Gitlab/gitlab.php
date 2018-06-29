<?php
require 'vendor/autoload.php';
include_once("function.php");
//~~~~ zoho authtoken --//
$AuthToken = 'c8eb44be3bca2c3caf5c76b7539e6e92';
//~~~~ gitlab authtoken --//
$private_token = 'F8E2UDH2SXfvFAaHJBCt';
/* $Application_Id	='7830d396c1b0d37389ada26def67de2877271b363ddcc71bc9b675fae8036241';
$Secret = '6ca79c5b547b2516224144a11012a3332e101bb4a7dc7677e0f7bba69f1823d8';
$Callback_url= 'http://dev.seventhfoundation.com/Zoho-Portal/gitlab.php'; */
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

//~~~~~~~~~~~~~~~~~~~~~~~~~end ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//
$Bug_request_parameters = array(
	 'authtoken' => $AuthToken,
	 'statustype' => 'open',
	  ); 
$BugRequest_url = 'https://projectsapi.zoho.com/restapi/portal/'.$portal_id.'/projects/'.$zohoProjectid.'/bugs/';
$BugResponse = zohoApiResponce($AuthToken,$BugRequest_url,$Get_method_name,$Bug_request_parameters) ;
$Gitlabarray = array();;
foreach($BugResponse['bugs'] as $bugsvalue){
	
	if($bugsvalue['customfields']['0']['label_name'] == 'GitlabID'){	
		 $GitValue = $bugsvalue['customfields']['0']['value'];	
		 $issue_ids = $bugsvalue['id_string'];
		// array(issue id=>GitlabID value)
		$Gitlabarray[$issue_ids]= $GitValue;
	}
}
//~~~~~~~~~~~~~~~~~~~~~~~ gitlab data ~~~~~~~~~~~~~~~~~~~~//
$url = 'https://gitlab.com/api/v4/projects/';
	$parameters = array(
		'private_token' =>$private_token,
		'visibility' =>'private'
	);
 $Response = zohoApiResponce(NULL,$url,$Get_method_name,$parameters) ;
  $web_url  = $Response['0']['web_url'];
   $issue_url = $Response['0']['_links']['issues'];
  $issue_parameters = array(
		'private_token' =>$private_token,
	);
$IssueResponse = zohoApiResponce(NULL,$issue_url,$Get_method_name,$issue_parameters) ;
 foreach($IssueResponse as $value){
 $lable = $value['labels']['0']; 
 $title  =  $value['title'];

$config['sync_status'] = array(
'todo' => 'Open',
'Doing' => 'In-progress',
'QA' => 'To-be-tested',
);  
	 if(!empty($lable)){
		if($config['sync_status'][$lable] == 'Open'){
			echo 'open'.$status_id = '981042000000007045';	
		}elseif ($config['sync_status'][$lable] == 'In-progress'){
			echo 'progress'.$status_id = '981042000000179001';
		}elseif ($config['sync_status'][$lable] == 'To-be-tested'){
			'tested'.$status_id = '981042000000179008';
		}
	 } else{
		 $status_id = '981042000000007045';
	 } 
	 
	$description =  $value['description'];
	preg_match('#\(/(.*?)\)#', $description, $match);
	$path = pathinfo($match['1']);
	$str = $path['filename'];
	$finalstr = '!['.$str.']';
	$desc_str = str_replace($match['0'],'',$description);
	$finaldesc_str = str_replace($finalstr,'',$desc_str);
	$issue_id = $value['id'];
	$iid =  $value['iid'];
	$project_id  =  $value['project_id'];
	$title  =  $value['title'];
	$assignee = $value['assignee']['name'];
	$user_notes_count = $value['user_notes_count'];	
	$issue_web_url = $value['web_url'];
	$Subsystems_value = 'Release planner';
	$Planned_Release_values = 'Not Planned Yet';
	$severity_id = '981042000000007007';
		/* if($priority == 'High'){
			$severity_id = '981042000000007005';
		}else if($priority == 'Medium'){
			$severity_id = '981042000000007003';
		}else if($priority == 'Low'){
			$severity_id = '981042000000007007';
		} */
	if (in_array($issue_id, $Gitlabarray)){
	 	$bug_id = array_search ($issue_id, $Gitlabarray);	
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
										'uploaddoc' =>$uploaddoc,
										'status_id' =>$status_id,
										'CHAR1' =>'632412251',
										'CHAR2' =>'Device',
										'CHAR4' =>$Subsystems_value, // not available
										'CHAR5' =>$Planned_Release_values, // not available
										'LONG1' => $issue_id
									 ); 
									 $update_bug_request_url  = 'https://projectsapi.zoho.com/restapi/portal/'.$portal_id.'/projects/'.$zohoProjectid.'/bugs/'.$bug_id.'/';	 
					$updateBugResponse = prepare_curl_statement($update_bug_request_url,$Post_method_name,$CreateBug_request_parameters,$uploaddoc);
									echo "<pre>";
									print_r($updateBugResponse);
									echo "<pre>";
				
			}else{
 				$CreateBug_request_parameters = array(
										'authtoken' => $AuthToken,
										'title' =>$title,
										'description' =>$finaldesc_str,
										'assignee' =>$created_person, // not available 
										'severity_id' =>$severity_id, // not available 
										'status_id' =>$status_id,
										'CHAR1' =>'632412251',
										'CHAR2' =>'Device',
										'CHAR4' =>$Subsystems_value, // not available
										'CHAR5' =>$Planned_Release_values, // not available
										'LONG1' => $issue_id
									);  
		$update_bug_request_url  = 'https://projectsapi.zoho.com/restapi/portal/'.$portal_id.'/projects/'.$zohoProjectid.'/bugs/'.$bug_id.'/';
		$updateBugResponse = zohoApiResponce($AuthToken,$update_bug_request_url,$Post_method_name,$CreateBug_request_parameters) ;	
								echo "<pre>";
									print_r($updateBugResponse);
									echo "<pre>";									
			}
 }else{	
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
									$CreateBugResponse = prepare_curl_statement($create_bug_request_url, 'POST', $CreateBug_request_parameters, $uploaddoc);
			
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
					echo "<pre>";
					print_r($CreateBugResponse);
					echo "</pre>";
		}
		$BugResponse_id =  $CreateBugResponse['bugs']['0']['id_string'];
		if(!empty($user_notes_count)){
			$notes_url = $url.$project_id.'/issues/'.$iid.'/notes/';
			$issue_parameters = array(
					'private_token' =>$private_token,
				);
			$issue_note = zohoApiResponce(NULL,$notes_url,$Get_method_name,$issue_parameters);
			foreach($issue_note as $commntvalue){
				$commnet = $commntvalue['body'];
				$Bugcomments_request_parameters = array(
										 'authtoken' => $AuthToken,
										 'content' =>$commnet,
									 );	
				$BugCommentsRequest_url = 'https://projectsapi.zoho.com/restapi/portal/'.$portal_id.'/projects/'.$zohoProjectid.'/bugs/'.$BugResponse_id.'/comments/';
				$CommentsBugResponse = zohoApiResponce($AuthToken,$BugCommentsRequest_url,$Post_method_name,$Bugcomments_request_parameters);

					echo "<pre>";
					print_r($CommentsBugResponse);
					echo "</pre>";				
			}
		} 
	}
	
}
?>
