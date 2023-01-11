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
	$test_docket_id = 'NOAA-NMFS-2018-0028'; //this is docket does not have many comments...
	$test_docket_id = 'DEA-2016-0015'; //this is the real target...	

	//accept the docket as a command line argument
	if(isset($argv[1])){
		$manual_docket_id = $argv[1];
	}


	//then this is test mode, as the file was called directly...
	$result_url_array = recurse_and_mirror_docket(
						$manual_docket_id,
						$regulations_gov_api_key,
						$test_project_id,
						$test_bucket_id);



}



	function recurse_and_mirror_docket($docket_id,$regulation_gov_api_key,$project_id,$bucket_string){

		$has_more = true;
	
		$current_page = 0;
		while($has_more){

			echo "Getting page $current_page...\n";

			$json_data = get_one_docket_page($docket_id,$regulation_gov_api_key,$project_id,$bucket_string,$current_page);
			
			if(count($json_data['documents']) < 1000){
				//then we are done...
				$has_more = false;
			}

			$current_page = $current_page + 1000;

			//just do one for now..
			//$has_more = false;

		}

	}

	function get_one_docket_page($docket_id,$regulation_gov_api_key,$project_id,$bucket_string,$page_num){

		$url = "https://api.data.gov:443/regulations/v3/documents.json?api_key=$regulation_gov_api_key&countsOnly=0&dktid=$docket_id&rpp=1000&po=$page_num";


		check_throttle(); //this might pause for an hour, to respect rate limit. 
		$json_text = file_get_contents($url);
                if($http_response_header[0] == 'HTTP/1.1 429 Too Many Requests'){
                        echo "Wait 70 min\n";
                        sleep(4200);
                        //lets start over from scratch...
			return(get_one_docket_page($docket_id,$regulation_gov_api_key,$project_id,$bucket_string,$page_num));
                }



		$Storage = new StorageClient([
    			'projectId' => $project_id,
		]);

		$Bucket = $Storage->bucket($bucket_string);

		$file_name = "docket.$docket_id.$page_num.json";

		$my_storage_object = $Bucket->upload(
			$json_text,
    			[
        			'predefinedAcl' => 'publicRead',
        			'name' => $file_name,
        			'validate' => true,
    			]
		);
	
		//write it to the local cache.
		file_put_contents("./data/$file_name",$json_text);
	
		//do we have pdf/html?
		$json_data = json_decode($json_text,true);

		//in case we need data later...
		//$info = $my_storage_object->info();

		$return_url = "https://storage.googleapis.com/$bucket_string/$file_name";

		return($json_data);

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

