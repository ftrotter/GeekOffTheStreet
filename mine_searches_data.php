<?php
	require 'vendor/autoload.php';
        require_once('throttling.function.php');
	require_once('recurse_and_mirror_documents.function.php');
	require_once('recurse_and_mirror_docket.function.php');
	
	use Google\Cloud\Core\ServiceBuilder;
	use Google\Cloud\Storage\StorageClient;
	use Symfony\Component\Yaml\Yaml;


	//need credentials because we will be occasioanlly getting documents..
        $yaml_data = Yaml::parseFile('./regulations.gov.api.yaml');
        $regulations_gov_api_key = $yaml_data['regulations_gov_api_key'];

        $project_id = 'geekoffthestreet-200406';
        $bucket_id = 'geek_off_the_street';


	//lets find all of the search json files... 
	$searches = glob('./data/search*.json');

	$unique_dockets = [];

	foreach($searches as $this_search_file){

		echo "Working on $this_search_file\n";
		$search_data = json_decode(file_get_contents($this_search_file),true);
		foreach($search_data['documents'] as $this_document){
			$docket_id = $this_document['docketId'];
			$unique_dockets[$docket_id] = $docket_id;
		}
		
	}

	//so now we have all of the docket_ids from all of the search files...
	//but we have already downloaded some of them...
	$new_dockets = [];
	foreach($unique_dockets as $this_docket_id){
		$docket_file = "./data/docket.$this_docket_id.0.json";
		if(file_exists($docket_file)){
			//we do nothing, it has already been downloaded!!
		}else{
			$new_dockets[] = $this_docket_id;
		}
	}

	echo count($new_dockets). " new dockets found in search results\n";		

	foreach($new_dockets as $this_docket_id){

		echo "Dowloading Docket $this_docket_id\n";

        	//then this is test mode, as the file was called directly...
        	$result_url_array = recurse_and_mirror_docket(
                                                $this_docket_id,
                                                $regulations_gov_api_key,
                                                $project_id,
                                                $bucket_id);

	}
	

