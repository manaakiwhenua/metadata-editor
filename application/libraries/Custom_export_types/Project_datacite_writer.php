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
	public function generate(string $project_id, string $output_file, array $options = array()): void
	{
		$project_json = $this->get_project_json($project_id, $options);
		$datacite_json = $this->map_to_datacite_json($project_json);
		file_put_contents($output_file, trim(json_encode($datacite_json)) . PHP_EOL);
	}

	private function get_project_json(string $project_id, array	$options = array()): array
	{
		$project_json_filepath = $this->ci->project_json_writer->generate_project_json($project_id, $options);

		if (!is_readable($project_json_filepath)) {
			throw new Exception("Failed to read project JSON export");
		}

		$json_raw = file_get_contents($project_json_filepath);
		$project_json = json_decode($json_raw, true);

		if ($project_json === null && json_last_error() !== JSON_ERROR_NONE) {
			throw new Exception("Invalid JSON export for project");
		}

		return $project_json;
	}

	private function map_to_datacite_json(array $project_json): array
	{

		$datacite_map_raw = $this->ci->project_export_yaml_map_parser->parse_yaml_map($this->ci->config->item('bsi_core_to_datacite'));
		$datacite_map_metadata = $datacite_map_raw['profile'] ?? [];
		$datacite_mapping_rules = $datacite_map_raw['mappings'] ?? [];
		$datacite_defaults = $datacite_map_raw['defaults'] ?? [];

		$datacite_map = new Schema_transform_mapping_rules($datacite_map_metadata, $datacite_mapping_rules, $datacite_defaults);

		$inner_datacite_json = $this->ci->project_export_yaml_map_parser->apply_export_mappings($datacite_map, $project_json);
		$completed_datacite_json = $this->build_full_datacite_structure($inner_datacite_json);

		return $completed_datacite_json;
	}

	private function build_full_datacite_structure(array $data): array
	{
		$additional_datacite_structures = [
			'logs' => $data['logs'] ?? [],
			'relationships' => [],
		];

		return array_merge($data, $additional_datacite_structures);
	}
}
