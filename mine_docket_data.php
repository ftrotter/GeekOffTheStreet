<?php
	require 'vendor/autoload.php';
	require_once('throttling.function.php');


	use Google\Cloud\Core\ServiceBuilder;
	use Google\Cloud\Storage\StorageClient;
	use Symfony\Component\Yaml\Yaml;


	require_once('recurse_and_mirror_documents.function.php');

	//need credentials because we will be occasioanlly getting documents..
        $yaml_data = Yaml::parseFile('./regulations.gov.api.yaml');
        $regulations_gov_api_key = $yaml_data['regulations_gov_api_key'];

        $test_project_id = 'geekoffthestreet-200406';
        $test_bucket_id = 'geek_off_the_street';


	$dockets = glob('./data/docket*.json');

	$unique_documents = [];

	foreach($dockets as $this_docket_file){

		echo "Working on $this_docket_file\n";
		$docket_data = json_decode(file_get_contents($this_docket_file),true);
		foreach($docket_data['documents'] as $this_document){
			$doc_id = $this_document['documentId'];
			if(isset($this_document['commentText'])){
				$doc_comment = $this_document['commentText'];
				$unique_document[$doc_id] = $doc_comment;
				echo '.';
			}else{
				//this document is something else... 
				//lets mine it specifically...
					if(file_exists("./data/doc.$doc_id.json")){
						echo 'd'; //this has already been downloaded...
					}else{
						echo 'D';				
				       	 	$result_url_array = recurse_and_mirror_documents(
                                       	         	$doc_id,
                                                	$regulations_gov_api_key,
                                                	$test_project_id,
                                                	$test_bucket_id);
					}
			}
		}
		
	}

