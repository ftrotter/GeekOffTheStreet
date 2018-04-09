<?php
	require 'vendor/autoload.php';
	require_once('throttling.function.php');
	use Google\Cloud\Core\ServiceBuilder;
	use Google\Cloud\Storage\StorageClient;
	use Symfony\Component\Yaml\Yaml;

	putenv('GOOGLE_APPLICATION_CREDENTIALS=./google_keyfile.json');

if($argv[0] == basename(__FILE__)){
	
	$yaml_data = Yaml::parseFile('./regulations.gov.api.yaml');	
	$regulations_gov_api_key = $yaml_data['regulations_gov_api_key'];

	$test_project_id = 'geekoffthestreet-200406';
	$test_bucket_id = 'geek_off_the_street_test';
	$test_document_id = 'DEA-2016-0015-6867'; //this is a comment ...	
	$test_document_id = 'DEA-2016-0015-0006'; //this is a central document... 

	//then this is test mode, as the file was called directly...
	$result_url_array = recurse_and_mirror_documents(
						$test_document_id,
						$regulations_gov_api_key,
						$test_project_id,
						$test_bucket_id);

	$right_answer_urls = [
		"https://storage.googleapis.com/geek_off_the_street_test/doc.DEA-2016-0015-0006.json",
		"https://storage.googleapis.com/geek_off_the_street_test/doc.DEA-2016-0015-0006.pdf",
		"https://storage.googleapis.com/geek_off_the_street_test/doc.DEA-2016-0015-0006.html",	
		];
	
	foreach($result_url_array as $pos => $result_url){
		$right_answer_url = $right_answer_urls[$pos];
		if($right_answer_url == $result_url){
			echo "Success!! got $result_url which is what I expected\n";
		}else{
			echo "Fail!! got \n$result_url\nexpected\n$right_answer_url\n";
		}
	}
}



	function recurse_and_mirror_documents($document_id,$regulation_gov_api_key,$project_id,$bucket_string){

		$url = "https://api.data.gov:443/regulations/v3/document.json?api_key=$regulation_gov_api_key&documentId=$document_id";

                check_throttle(); //this might pause for an hour, to respect rate limit.
		$json_text = file_get_contents($url);
		if($http_response_header[0] == 'HTTP/1.1 429 Too Many Requests'){
			echo "Wait 70 min\n";
			sleep(4200);
			//lets start over from scratch...
			return(recurse_and_mirror_documents($document_id,$regulation_gov_api_key,$project_id,$bucket_string));
		}

		$Storage = new StorageClient([
    			'projectId' => $project_id,
		]);

		$Bucket = $Storage->bucket($bucket_string);

		$file_name = "doc.$document_id.json";

		$my_storage_object = $Bucket->upload(
			$json_text,
    			[
        			'predefinedAcl' => 'publicRead',
        			'name' => $file_name,
        			'validate' => true,
    			]
		);

		//save a local copy...
		file_put_contents("./data/$file_name",$json_text);
		
		//do we have pdf/html?
		$json_data = json_decode($json_text,true);

		$return_array = [];

		if(isset($json_data['fileFormats'])){
		
			foreach($json_data['fileFormats'] as $content_url){
				$parse_url = parse_url($content_url);
				parse_str($parse_url['query'],$query_array);
				$file_type = $query_array['contentType']; //this will tell us if it is a pdf or html or whathaveyou...

				$this_file_name = "doc.$document_id.$file_type";
				$with_key_url = $content_url . "&api_key=$regulation_gov_api_key";

		                check_throttle(); //this might pause for an hour, to respect rate limit.
				$file_data = file_get_contents($with_key_url);
                		if($http_response_header[0] == 'HTTP/1.1 429 Too Many Requests'){
                        		echo "Wait 70 min\n";
                        		sleep(4200);
                        		//lets start over from scratch...
                        		return(recurse_and_mirror_documents($document_id,$regulation_gov_api_key,$project_id,$bucket_string));
                		}

				$tmp_storage_object = $Bucket->upload(
					$file_data,
    					[
        					'predefinedAcl' => 'publicRead',
        					'name' => $this_file_name,
        					'validate' => true,
    					]
				);

				//save a local copy..
				file_put_contents("./data/$this_file_name",$file_data);


				$file_url = "https://storage.googleapis.com/$bucket_string/$this_file_name";
				$return_array[] = $file_url;
			}

		}
		//in case we need data later...
		//$info = $my_storage_object->info();

		$return_url = "https://storage.googleapis.com/$bucket_string/$file_name";

		array_unshift($return_array,$return_url);

		return($return_array);

	}

/*
Here is the kind of data we can get from the ->info call if we need...

array (
  'kind' => 'storage#object',
  'id' => 'geek_off_the_street_test/this_file1523088605/1523088605872928',
  'selfLink' => 'https://www.googleapis.com/storage/v1/b/geek_off_the_street_test/o/this_file1523088605',
  'name' => 'this_file1523088605',
  'bucket' => 'geek_off_the_street_test',
  'generation' => '1523088605872928',
  'metageneration' => '1',
  'contentType' => 'application/octet-stream',
  'timeCreated' => '2018-04-07T08:10:05.825Z',
  'updated' => '2018-04-07T08:10:05.825Z',
  'storageClass' => 'NEARLINE',
  'timeStorageClassUpdated' => '2018-04-07T08:10:05.825Z',
  'size' => '15',
  'md5Hash' => '99dpepS+YqBWVQ02JaLZnw==',
  'mediaLink' => 'https://www.googleapis.com/download/storage/v1/b/geek_off_the_street_test/o/this_file1523088605?generation=1523088605872928&alt=media',
  'acl' =>
  array (
    0 =>
    array (
      'kind' => 'storage#objectAccessControl',
      'id' => 'geek_off_the_street_test/this_file1523088605/1523088605872928/user-geekoffthestreet@geekoffthestreet-200406.iam.gserviceaccount.com',
      'selfLink' => 'https://www.googleapis.com/storage/v1/b/geek_off_the_street_test/o/this_file1523088605/acl/user-geekoffthestreet@geekoffthestreet-200406.iam.gserviceaccount.com',
      'bucket' => 'geek_off_the_street_test',
      'object' => 'this_file1523088605',
      'generation' => '1523088605872928',
      'entity' => 'user-geekoffthestreet@geekoffthestreet-200406.iam.gserviceaccount.com',
      'role' => 'OWNER',
      'email' => 'geekoffthestreet@geekoffthestreet-200406.iam.gserviceaccount.com',
      'etag' => 'CKCGwprbp9oCEAE=',
    ),
    1 =>
    array (
      'kind' => 'storage#objectAccessControl',
      'id' => 'geek_off_the_street_test/this_file1523088605/1523088605872928/allUsers',
      'selfLink' => 'https://www.googleapis.com/storage/v1/b/geek_off_the_street_test/o/this_file1523088605/acl/allUsers',
      'bucket' => 'geek_off_the_street_test',
      'object' => 'this_file1523088605',
      'generation' => '1523088605872928',
      'entity' => 'allUsers',
      'role' => 'READER',
      'etag' => 'CKCGwprbp9oCEAE=',
    ),
  ),
  'owner' =>
  array (
    'entity' => 'user-geekoffthestreet@geekoffthestreet-200406.iam.gserviceaccount.com',
  ),
  'crc32c' => 'T5bKeg==',
  'etag' => 'CKCGwprbp9oCEAE=',
)
*/

