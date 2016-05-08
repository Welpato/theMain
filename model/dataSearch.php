<?php
class dataSearch{
	private $apiKey = '';//Set here the api key
	private $season = 'SEASON2016';
	private $region;
	
	/*
	 * The function base that returns to ajax request all data based on summoners name informed
	 */
	function returnSummonerPerfil($summonerName, $region){		
		$this->region = $region;//set the region selected
		
		//Summoner Basic Data
		//Verify if the summoner name exists and if exists return him basic data
		$resultSummoner = $this->returnSummonerBasicData($summonerName);
		if(isset($resultSummoner['status']['message'])){
				return $resultSummoner;
		}//if(isset($resultSummoner['message'])){
		//Summoner Basic Data
		
		//Main champions data
		//return the 3 main champions based on the mastery points.
		$resultMastery= $this->returnMasterySummoner($resultSummoner[strtolower($summonerName)]['id']);

		$resultSummoner[strtolower($summonerName)]['summonerMastery'] = $resultMastery;
		//Main champions data
		//Matches data and main role
		$arrChampionRole = $this->returnStats($resultSummoner[strtolower($summonerName)]['id'],
								$resultMastery[0]['championId'].','.$resultMastery[1]['championId'].','.$resultMastery[2]['championId'].',');
		$resultSummoner[strtolower($summonerName)]['wins'] = $arrChampionRole['wins'];
		$resultSummoner[strtolower($summonerName)]['losses'] = $arrChampionRole['losses'];
		$resultSummoner[strtolower($summonerName)]['playAs'] = $arrChampionRole['playAs'];
		$resultSummoner[strtolower($summonerName)]['mainRole'] = $arrChampionRole['mainRole'];
		//Matches data and main role
		
		//Counter champions, and more the wins and losses total
		$i = 0;
		foreach($resultSummoner[strtolower($summonerName)]['summonerMastery'] as $c){
			$resultSummoner[strtolower($summonerName)]['summonerMastery'][$i]['wins'] = $arrChampionRole['champions'][$c['championId']]['wins'];
			$resultSummoner[strtolower($summonerName)]['summonerMastery'][$i]['losses'] = $arrChampionRole['champions'][$c['championId']]['losses'];
			if(empty($arrChampionRole['champions'][$c['championId']]['counter'])){
				$resultSummoner[strtolower($summonerName)]['summonerMastery'][$i]['counter']['championName'] = 'Not played';
				$resultSummoner[strtolower($summonerName)]['summonerMastery'][$i]['counter']['championSquare'] = "http://ddragon.leagueoflegends.com/cdn/6.8.1/img/profileicon/1.png";

				$resultSummoner[strtolower($summonerName)]['summonerMastery'][$i]['counter']['losses'] = 0;
				$resultSummoner[strtolower($summonerName)]['summonerMastery'][$i]['counter']['deaths'] = 0;
			}else{
				$counter = $this->returnChampionName(array_values(array_keys($arrChampionRole['champions'][$c['championId']]['counter'],
						max($arrChampionRole['champions'][$c['championId']]['counter'])))[0]);
				$resultSummoner[strtolower($summonerName)]['summonerMastery'][$i]['counter']['championName'] = $counter['name'];
				$resultSummoner[strtolower($summonerName)]['summonerMastery'][$i]['counter']['championSquare'] = "http://ddragon.leagueoflegends.com/cdn/6.8.1/img/champion/".$counter['key'].".png";
				$counter = max($arrChampionRole['champions'][$c['championId']]['counter']);
				$resultSummoner[strtolower($summonerName)]['summonerMastery'][$i]['counter']['losses'] = $counter['losses'];
				$resultSummoner[strtolower($summonerName)]['summonerMastery'][$i]['counter']['deaths'] = $counter['deaths'];
			}//if(empty($arrChampionRole['champions'][$c['championId']]['counter'])){
			$i++;
		}//foreach($resultSummoner['summonerMastery'] as $c){
		//Counter champions, and more the wins and losses total
		
		return $resultSummoner;
	}//function returnSummonerPerfil($summonerName = "", $summonerId = ""){
	
	/*
	 * Returns the summoner basic data based on the summonername informed
	 */
	function returnSummonerBasicData($summonerName){
		//Basic stats of the summoner
		$headers = array('Content-Type: application/json');

		$url = "https://".$this->region.".api.pvp.net/api/lol/".$this->region."/v1.4/summoner/by-name/".$summonerName."?api_key=".$this->apiKey;			

		
		// Open connection
		$ch = curl_init();
		
		// Set the url, number of GET vars, GET data
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		// Execute request
		$result = curl_exec($ch);
		
		// Close connection
		curl_close($ch);
		
		// get the result and parse to JSON
		return json_decode($result, true);
		//Basic stats of the summoner
	}//function returnSummonerBasicData($summonerName){
	
	/*
	 * Returns the summoner mastery champions in crescent order
	 * summonerId is the id of the summoner were will return the champions mastery's
	 * count is how many champions will return
	 */
	function returnMasterySummoner($summonerId,$count =3){
		$headers = array('Content-Type: application/json');
		if($this->region == 'las'){
			$r = "la1";
		}else if($this->region == 'lan'){
			$r ="la2";
		}else if($this->region == 'ru'){
			$r = "ru";
		}else{
			$r =$this->region.'1';
		}//if($this->region == 'LAS'){
		$url ="https://".$this->region.".api.pvp.net/championmastery/location/".$r."/player/".$summonerId."/topchampions?count=".$count."&api_key=".$this->apiKey;
		
		$ch = curl_init();
		
		// Set the url, number of GET vars, GET data
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		// Execute request
		$result = curl_exec($ch);
		
		// Close connection
		curl_close($ch);
		
		// get the result and parse to JSON
		$resultMastery = json_decode($result, true);
		//get the champions id returned and search for them name and insert the champion "profile image"
		$i = 0;
		foreach($resultMastery as $champion){
			$c = $this->returnChampionName($champion['championId']);
			$resultMastery[$i]['championName'] = $c['name'];
			$resultMastery[$i]['championSquare'] = "http://ddragon.leagueoflegends.com/cdn/6.8.1/img/champion/".$c['key'].".png";
			$i++;
		}//foreach($resultMastery as $champion){
		
		return $resultMastery;
	}//function returnMasterySummoner($summonerId,$count =3){
	
	/*
	 * Return the champion data based on the champion Id
	 */
	function returnChampionName($championId){
		$headers = array('Content-Type: application/json');
		$url ="https://global.api.pvp.net/api/lol/static-data/".$this->region."/v1.2/champion/".$championId."?api_key=".$this->apiKey;
		
		$ch = curl_init();
		
		// Set the url, number of GET vars, GET data
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		// Execute request
		$result = curl_exec($ch);
		
		// Close connection
		curl_close($ch);
		
		// get the result and parse to JSON
		$resultChampion = json_decode($result, true);
		
		return $resultChampion;
	}//function returnChampionPerfil($championId){
	
	/*
	 * Return a match list of the summoner id
	 * Where was played with the champions in the championsId list 
	 */
	function returnMatchListChampions($summonerId,$championsId){
		$headers = array('Content-Type: application/json');
		$url ="https://".$this->region.".api.pvp.net/api/lol/".$this->region."/v2.2/matchlist/by-summoner/".$summonerId."?championIds=".$championsId."&rankedQueues=TEAM_BUILDER_DRAFT_RANKED_5x5,RANKED_SOLO_5x5,RANKED_TEAM_5x5&seasons=".$this->season."&api_key=".$this->apiKey;
		
		$ch = curl_init();
		
		// Set the url, number of GET vars, GET data
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		// Execute request
		$result = curl_exec($ch);
		
		// Close connection
		curl_close($ch);
		
		// get the result and parse to JSON
		$resultMatchListChampions = json_decode($result, true);
		return $resultMatchListChampions;
	}//function returnMatchListChampions($summonerId,$championsId){
	
	/*
	 * Return the match data
	 */
	function returnMatch($match){
		$headers = array('Content-Type: application/json');
		$url ="https://".$this->region.".api.pvp.net/api/lol/".$this->region."/v2.2/match/".$match."?includeTimeline=FALSE&api_key=".$this->apiKey;
		
		$ch = curl_init();
		
		// Set the url, number of GET vars, GET data
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		// Execute request
		$result = curl_exec($ch);
		
		// Close connection
		curl_close($ch);
		
		// get the result and parse to JSON
		$resultMatch = json_decode($result, true);
		return $resultMatch;
	}//function returnMatch($match){
	
	/*
	 * Calculate and return
	 * Total Wins and Losses, Main Role, Counter Champions
	 */
	function returnStats($summonerId,$championsId){
		//search the match data
		$resultMatchListChampions = $this->returnMatchListChampions($summonerId, $championsId);
		//prepare array's
		$arrayReturn = array();		
		$arrayStatics = array('playAs'=>array('Support'=>array('lanePoints'=>0,
														   		'wins'=>0,
																'losses'=>0),
											  'AD Carry'=>array('lanePoints'=>0,
														   		'wins'=>0,
																'losses'=>0),
											  'Midlaner'=>array('lanePoints'=>0,
														   		'wins'=>0,
																'losses'=>0),
											  'Jungler'=>array('lanePoints'=>0,
														   		'wins'=>0,
																'losses'=>0),
											  'Toplaner'=>array('lanePoints'=>0,
														   		'wins'=>0,
																'losses'=>0)
											),
							'losses'=> 0,
							'wins'=> 0,
							'champions' =>array(),
							'mainRole' => ''
							);		
		while($championsId != false){
			$id = substr($championsId, 0,strpos($championsId, ','));
			$championsId = substr($championsId,strpos($championsId, ',')+1);
			$arrayStatics['champions'][$id] = array('wins' => 0,
													'losses' => 0,
													'counter' =>array()
													);
		}//while(strlen($champion)>0){
		
		//Performs the calculations
		foreach($resultMatchListChampions['matches'] as $v){
			$match = $this->returnMatch($v['matchId']);
			$participantId =array_search($summonerId,(array_column(array_column($match['participantIdentities'], 'player'),'summonerId')));//return the summoner participantId in this match
			$role = $v['role'];
			$lane = $v['lane'];
			$mResult = '';
			$lanePoints = 0;
			if($match['participants'][$participantId]['stats']['winner']){
				$mResult = 'wins';
				$lanePoints = 2;
			}else{
				$mResult = 'losses';
				$lanePoints = 1;
			}//if($match['participants'][$participantId]['stats']['winner']){
			if($role == 'DUO_SUPPORT'){
				$arrayStatics['playAs']['Support'][$mResult]++;
				$arrayStatics['playAs']['Support']['lanePoints'] += $lanePoints;
				$arrayCounter = array_keys(array_column(array_column($match['participants'], 'timeline'),'role'),'DUO_SUPPORT');
			}else if($role == 'DUO_CARRY'){
				$arrayStatics['playAs']['AD Carry'][$mResult]++;
				$arrayStatics['playAs']['AD Carry']['lanePoints'] += $lanePoints;
				$arrayCounter = array_keys(array_column(array_column($match['participants'], 'timeline'),'role'),'DUO_CARRY');
			}else if($role == 'JUNGLE'){
				$arrayStatics['playAs']['Jungler'][$mResult]++;
				$arrayStatics['playAs']['Jungler']['lanePoints'] += $lanePoints;
				$arrayCounter = array_keys(array_column(array_column($match['participants'], 'timeline'),'role'),'JUNGLE');
			}else if($lane == 'MIDDLE'){
				$arrayStatics['playAs']['Midlaner'][$mResult]++;
				$arrayStatics['playAs']['Midlaner']['lanePoints'] += $lanePoints;
				$arrayCounter = array_keys(array_column(array_column($match['participants'], 'timeline'),'lane'),'MIDDLE');
			}else if($lane == 'TOP'){
				$arrayStatics['playAs']['Toplaner'][$mResult]++;
				$arrayStatics['playAs']['Toplaner']['lanePoints'] += $lanePoints;
				$arrayCounter = array_keys(array_column(array_column($match['participants'], 'timeline'),'lane'),'TOP');
			}//if($role == 'DUO_SUPPORT' && $lane == 'BOTTOM'){
			$arrayStatics['champions'][$match['participants'][$participantId]['championId']][$mResult]++;
			foreach($arrayCounter as $cId){
				if($cId != $participantId){
					if(isset($arrayStatics['champions'][$match['participants'][$participantId]['championId']]
							['counter'][$match['participants'][$cId]['championId']])){
								if($mResult == 'losses'){
									$arrayStatics['champions'][$match['participants'][$participantId]['championId']]
									['counter'][$match['participants'][$cId]['championId']]['losses']++;
								}//if($mResult == 'losses'){
								$arrayStatics['champions'][$match['participants'][$participantId]['championId']]
								['counter'][$match['participants'][$cId]['championId']]['deaths']+= $match['participants'][$participantId]['stats']['deaths'];
					
					}else{
						if($mResult == 'losses'){
							$arrayStatics['champions'][$match['participants'][$participantId]['championId']]
							['counter'][$match['participants'][$cId]['championId']]['losses'] = 1;
						}else{
							$arrayStatics['champions'][$match['participants'][$participantId]['championId']]
							['counter'][$match['participants'][$cId]['championId']]['losses'] = 0;
						}//if($mResult == 'losses'){
						$arrayStatics['champions'][$match['participants'][$participantId]['championId']]
						['counter'][$match['participants'][$cId]['championId']]['deaths']= $match['participants'][$participantId]['stats']['deaths'];
					}//if(isset($arrayStatics['champions'][$match['participants'][$participantId]['championId']]
					break;
				}//if($cId != $participantId){
			}//foreach($counterPID as $cp){
						
			$arrayStatics[$mResult]++;	
			
		}//foreach($resultMatchListChampions as $v){
		$arrayStatics['mainRole'] = array_values(array_keys($arrayStatics['playAs'], max($arrayStatics['playAs'])))[0];
		return $arrayStatics;
	}//function returnStatsRoleChampions($summonerId,$championsId){
	
	
}//class Summoner{s