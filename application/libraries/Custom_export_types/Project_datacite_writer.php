<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'application/libraries/Custom_export_types/Mapping_classes/Schema_transform_mapping_rules.php';

class Project_datacite_writer implements IProject_export_writer
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->ci = &get_instance();
		$this->ci->load->library("Custom_export_types/Project_json_writer");
		$this->ci->load->library("Custom_export_types/Project_export_yaml_map_parser");
		$this->ci->load->config('export_config');
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
