<?php
header("Content-type: text/javascript; charset=iso-8859-1");
$action = "";

if(isset($_REQUEST) && is_array($_REQUEST)){
	if(isset($_REQUEST['action']) && $_REQUEST['action'] != ""){
		$action = $_REQUEST['action'];
	}//if(isset($_REQUEST['action']) && $_REQUEST['action'] != ""){
}//if(isset($_REQUEST) && is_array($_REQUEST)){

$arrayReturn = array();

require_once 'dataSearch.php';

switch ($action){
	case 'searchSummoner':		
		$dataSearch = new dataSearch();
		$arrayReturn = $dataSearch->returnSummonerPerfil($_REQUEST['summonerName'],$_REQUEST['region']);
	break;
}//switch ($action){
echo json_encode($arrayReturn);