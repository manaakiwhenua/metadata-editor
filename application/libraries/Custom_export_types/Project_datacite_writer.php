<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

class Project_datacite_writer implements IProject_export_writer
{

	const BSI_CORE_TO_DATACITE = 'application/libraries/Custom_export_types/Datacite/BSI_Core_to_DataCite_4.7_map.yaml';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->ci = &get_instance();
		$this->ci->load->library("Custom_export_types/Project_json_writer");
		$this->ci->load->library("Custom_export_types/Project_export_yaml_map_parser");
	}

	public function export_type(): string
	{
		return "datacite";
	}

	public function file_extension(): string
	{
		return "json";
	}

	/**
	 * Save Datacite JSON to output file
	 * @param string $project_id - Project ID
	 * @param string $output_file - Output file path
	 * @param array $options - Options
	 * @return void	 
	 *  
	 */
	public function generate(string $project_id, string $output_file, array $options = array()): void {}
}
