<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use League\HTMLToMarkdown\HtmlConverter;
use League\HTMLToMarkdown\Converter\TableConverter;

class Project_markdown_writer
{
	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->ci =& get_instance();
		$this->ci->load->model('Editor_model');
		$this->ci->load->library('Html_report');
	}

	/**
	 * 
	 * Export project metadata as Markdown
	 * 
	 * @param int $project_id - Project ID
	 * @param array options - Options
	 * 	- exclude_private_fields - Exclude private fields
	 * 
	 */
	function download_project_markdown($project_id, $options=array())
	{		
		$markdown_path=$this->generate_project_markdown($project_id, $options);

		if(file_exists($markdown_path)){
			$this->ci->load->helper('download');
			$filename = 'project_metadata_' . $project_id . '.md';
			force_download($filename, file_get_contents($markdown_path));
		}
	}
	
	/**
	 * 
	 * Generate project Markdown
	 * 
	 * @param int $project_id - Project ID
	 * @param array options - Options
	 * 	- exclude_private_fields - Exclude private fields
	 */
	function generate_project_markdown($project_id, $options = array())
	{
		set_time_limit(0);

		$exclude_private_fields = isset($options['exclude_private_fields']) ? (int)$options['exclude_private_fields'] : 1; // Default to exclude private fields

		$project = $this->ci->Editor_model->get_row($project_id);
		$project_folder = $this->ci->Editor_model->get_project_folder($project_id);

		if (!$project || !$project_folder || !file_exists($project_folder)) {
			throw new Exception("generate_project_markdown::Project folder not found");
		}

		$filename = trim((string)$project['idno']) !== '' ? trim($project['idno']) : nada_hash($project_id);
		$output_file = $project_folder . '/' . $filename . '.md';

		// Use generate_for_pdf() to exclude any unnecessary HTML and css
		$html = $this->ci->html_report->generate_for_pdf($project_id, array(
			'exclude_private_fields' => $exclude_private_fields,
		));

		$converter = new HtmlConverter(array(
			'strip_tags' => true,
			'hard_break' => true, // Convert <br> to line break only (\n)
			'remove_nodes' => 'script style', // Make sure we remove css script and style tags, on top of using generate_for_pdf(), so we don't have any inline styles in the markdown output
		));
		$converter->getEnvironment()->addConverter(new TableConverter()); // Table converter isn't included by default

		$markdown = $converter->convert($html);
		file_put_contents($output_file, trim($markdown) . PHP_EOL);

		return $output_file;
	}

}

