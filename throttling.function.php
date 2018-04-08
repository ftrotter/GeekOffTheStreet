<?php

	$GLOBALS['total_api_hits_this_hour'] = 0;

	//helps us to ensure that we do not abuse the API rate-limit..
	function check_throttle(){

		$GLOBALS['total_api_hits_this_hour']++;

		if($GLOBALS['total_api_hits_this_hour'] > 950){
			echo "Throtteling started, I will be gone for an hour\n";
			sleep(3600);	//sleep for one hour.
			$GLOBALS['total_api_hits_this_hour'] = 0;		
		}

	}
