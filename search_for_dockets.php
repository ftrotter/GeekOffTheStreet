<?php
	require 'vendor/autoload.php';
	require_once('throttling.function.php');

	use Google\Cloud\Core\ServiceBuilder;
	use Google\Cloud\Storage\StorageClient;
	use Symfony\Component\Yaml\Yaml;

	putenv('GOOGLE_APPLICATION_CREDENTIALS=./google_keyfile.json');

	$yaml_data = Yaml::parseFile('./regulations.gov.api.yaml');	
	$regulations_gov_api_key = $yaml_data['regulations_gov_api_key'];

	$project_id = 'geekoffthestreet-200406';
	$bucket_id = 'geek_off_the_street';


	if(!isset($argv[1])){
		echo "I need a search term to fetch docket information... as the first and only argument..\n";
		exit();
	}else{
		$search_term = $argv[1];
	}


	//then this is test mode, as the file was called directly...
	$result_url_array = search_dockets(
						$search_term,
						$regulations_gov_api_key,
						$project_id,
						$bucket_id);



	function search_dockets($search_term,$regulation_gov_api_key,$project_id,$bucket_string){


		$search_types = [
			'N', //Notice
			'PR', //proposed rule making
			'FR', //final rule making
			'O', //Other
			'SR', //Supporting and related material
			//'PS', //public submissions.. this would search comments...
				//so we skip it...
			];

		foreach($search_types as $this_search_type){

			$has_more = true;
	
			$current_page = 0;
			while($has_more){

				echo "Getting search type:$this_search_type page: $current_page...\n";

				$json_data = get_one_search_page($search_term,$this_search_type,$regulation_gov_api_key,$project_id,$bucket_string,$current_page);
			
				if(count($json_data['documents']) < 1000){
				//then we are done...
					$has_more = false;
				}

				$current_page = $current_page + 1000;

				//just do one for now..
				//$has_more = false;

			}
		}

	}

	function get_one_search_page($search_term,$this_search_type,$regulation_gov_api_key,$project_id,$bucket_string,$page_num){

		$ue_search_term = urlencode($search_term);

		$url = "https://api.data.gov:443/regulations/v3/documents.json?api_key=$regulation_gov_api_key&countsOnly=0&s=$ue_search_term&dct=$this_search_type&cp=C&rpp=1000&po=$page_num&sb=docId&so=ASC";

                check_throttle(); //this might pause for an hour, to respect rate limit.
		$json_text = file_get_contents($url);
                if($http_response_header[0] == 'HTTP/1.1 429 Too Many Requests'){
                        echo "Wait 70 min\n";
                        sleep(4200);
                        //lets start over from scratch...
			return(get_one_search_page($search_term,$this_search_type,$regulation_gov_api_key,$project_id,$bucket_string,$page_num));
                }


		$Storage = new StorageClient([
    			'projectId' => $project_id,
		]);

		$Bucket = $Storage->bucket($bucket_string);

		$file_name = "search.$ue_search_term.type_$this_search_type.$page_num.json";

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

