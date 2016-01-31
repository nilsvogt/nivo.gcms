<?php namespace Nivo;

/**
 * GCMS
 * Version 0.2.0
 */
Class GCMS {

	/**
	 * The GCMS-version.
	 * @var string
	 */
	const VERSION = '0.2.0';

	/**
	 * The Cache Directory
	 * @var string
	 */
	public $storage = "/storage/";

	/**
	 * The Cache File
	 * @var string
	 */
	public $storage_file = "contents.txt";

	/**
	 * The Id of the current spreadsheet
	 * @var string
	 */
	protected $spreadsheet_id;

	/**
	 * The Id of the current worksheet of the spreadsheet
	 * @var string
	 */
	protected $worksheet_id;

	/**
	 * URI-Pattern To A Public Worksheet
	 * @var string
	 */
	protected $worksheet_uri = 'https://docs.google.com/spreadsheets/d/{$spreadsheet_id}/export?format=csv&id={$spreadsheet_id}&gid={$worksheet_id}';

	/**
	 * URL To The Worksheet At Hand
	 * @var string
	 */
	protected $current_worksheet_url;

	/**
	 * Return Cached Contents OR Update Contents From Worksheet If Requested
	 * @param string  $storage_file   filename which will be used to get/create cached contens
	 * @param string  $spreadsheet_id   unique identifier of a spreadsheet
	 * @param integer $worksheet_id     unique identifier of a worksheet also known as the gid
	 * @param boolean $allow_update     indicates whether a public sheet is updatable or not
	 * @return array  contents of current worksheet
	 */
	public function getContents ($storage_file, $spreadsheet_id, $worksheet_id = 0, $allow_update = true){
		$this->storage_file = $storage_file;
		$this->spreadsheet_id = $spreadsheet_id;
		$this->worksheet_id = $worksheet_id;
		$this->allow_update = $allow_update;

		// insert document_id and worksheet_id into the worksheet_uri pattern to get the actual worksheet_url
		$this->current_worksheet_url = str_replace(['{$spreadsheet_id}', '{$worksheet_id}'], [$spreadsheet_id, $worksheet_id], $this->worksheet_uri);

		// determine if we need to update the contents
		$request_header = getallheaders();
		if(($this->allow_update && isset($request_header['Pragma']) && $request_header['Pragma'] == 'no-cache') || !file_exists($this->storage . $this->storage_file)){
			// refresh data from spreadsheet if requested
			$contents = $this->refreshContents();
		}else{
			// use cached data
			$contents = unserialize( file_get_contents($this->storage . $this->storage_file) );
		}

		return $contents;
	}

	/**
	 * Request data from current worksheet and parse its contents
	 * @return array  contents of current worksheet
	 */
	protected function refreshContents(){
		// Load The Sheet
		$spreadsheet_data = $this->loadSheetPublic();

		// Parse Data From Dot-Syntax To Multidimensional Array
		$contents = $this->parseData($spreadsheet_data);

		// Make Sure Chache-Dir Exists
		if(!is_dir($this->storage) && !mkdir($this->storage, 0777, true)){
			throw new Exception('Could not create storage directory.');
		}

		// Make Sure Cachefile Exists
		if( ! file_exists($this->storage . $this->storage_file)){
			file_put_contents($this->storage . $this->storage_file, '');
		}

		// Persist A Cached Copy
		file_put_contents($this->storage . $this->storage_file, serialize($contents));

		return $contents;
	}

	/**
	 * Download A Public Worksheet
	 * @return array Contains CSV data
	 */
	protected function loadSheetPublic(){
		$spreadsheet_data = [];
		if (($handle = fopen($this->current_worksheet_url, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				$spreadsheet_data[]=$data;
			}
			fclose($handle);
		}

		return $spreadsheet_data;
	}

	/**
	 * Converts Dot-Syntax To Multi-Dimensional Array
	 * @param  array speadsheet_data Single-Dimensional Array
	 * @return  Array  Multi-Dimensional Array
	 */
	protected function parseData( $spreadsheet_data ){
		$contents = [];
		foreach ($spreadsheet_data as $data) {

			// handle only keys with a leading "$"
			if( ! isset($data[0][0]) || $data[0][0] !== '$') continue;

			$key   = substr($data[0], 1);
			$value = $data[1];
			$array_keys = explode('.', $key);

			if(count($array_keys) === 1){

				// handle indexed array in the first place
				if( isset($contents[ $key ]) && !is_array($contents[ $key ]) ){
					// key already exists so convert to array and append data
					$val = $contents[ $key ];
					// convert to array
					$contents[ $key ] = array( $val, $value );
				}else if( isset($contents[ $key ]) ){
					// array already exists so just append data
					$contents[ $key ][] = $value;
				}else{
					// ordinary string
					$contents[ $key ] = $value;
				}

			}else{
				// map dot-syntax to array
				$ref = &$contents;

				// loop through all nodes
				while($array_keys){
					// current key to handle
					$key = $array_keys[0];

					// nothing there?
					if(!isset($ref[$key])){
						// create array
						$ref[$key] = array();
					}

					$ref = &$ref[$key];

					// last key - figure out if we set value, append value to array or create array
					if( count($array_keys) == 1 ){
						if(!is_array($ref)){
							// key is already occupied - create array and append value
							$tmp = $ref;
							$ref = array($tmp, $ref);
						}else if(empty($ref)){
							// assign simple value
							$ref = $value;
						}else{
							// append to already existing array
							$ref[] = $value;
						}
					}
					array_shift($array_keys);
				}
			}
		}
		return $contents;
	}
}