<?php
/*
	Downloads one and only one docket. 
	You have to give the docket_id as the command line argument... 
	to get a docket id, go to a regulation.gov docket, and click "show more details" 
	on the right side menu (currently above the comments section) 
	and it should be listed there.  

*/

	require 'vendor/autoload.php';
        require_once('throttling.function.php');
	require_once('recurse_and_mirror_documents.function.php');
	require_once('recurse_and_mirror_docket.function.php');
	
	use Google\Cloud\Core\ServiceBuilder;
	use Google\Cloud\Storage\StorageClient;
	use Symfony\Component\Yaml\Yaml;



	if(!isset($argv[1])){
		echo "Usage: php cli.download_one_docket.php {docket_id_here}\n";
		exit();
	}

	$this_docket_id = $argv[1];

	$arg_array = explode('-',$this_docket_id);
	if(count($arg_array) != 3){
		echo "Error: you gave $this_docket_id, but we are expecting something in the form ABC-2018-XXXX where ABC is the agency, then the year, and then four digits\n";
		exit();
	}


	//need credentials because we will be occasioanlly getting documents..
        $yaml_data = Yaml::parseFile('./regulations.gov.api.yaml');
        $regulations_gov_api_key = $yaml_data['regulations_gov_api_key'];

        $project_id = 'geekoffthestreet-200406';
        $bucket_id = 'geek_off_the_street';


	echo "Dowloading Docket $this_docket_id\n";

        $result_url_array = recurse_and_mirror_docket(
                                                $this_docket_id,
                                                $regulations_gov_api_key,
                                                $project_id,
                                                $bucket_id);

	

