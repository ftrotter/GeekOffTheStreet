<?php
	
	$search_json_file = "./data/search.todo.json";
	
	if(!file_exists($search_json_file)){
		echo "$search_json_file should be a flat json array of terms to search regulations.gov with.. it does not appear to exist\n";
		exit();
	}


	$searches = json_decode(file_get_contents($search_json_file),true);


	foreach($searches as $this_search){

		$search_cmd = "/usr/bin/php ./search_for_dockets.php \"$this_search\"";
		echo "Searching with $search_cmd\n";
		system($search_cmd);

		$mine_cmd = "/usr/bin/php ./mine_docket_data.php";
		echo "Mining with $mine_cmd\n";
		system($mine_cmd);


	}


