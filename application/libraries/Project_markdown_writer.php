<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use League\HTMLToMarkdown\HtmlConverter;
use League\HTMLToMarkdown\Converter\TableConverter;
require_once 'application/libraries/IProject_export_writer.php';

class Project_markdown_writer implements IProject_export_writer
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->ci =& get_instance();
		$this->ci->load->library('Html_report');
	}

	public function export_type() {
		return "markdown";
	}

	public function file_extension() {
		return "md";
	}

	public function mime_type() {
		return "text/markdown";
	}

	/**
	 * 
	 * Generate project Markdown
	 * 
	 * @param int $project_id - Project ID
	 * @param array $options - Options
	 * @param string|null $output_file - Optional output file path
	 * @return string - Absolute file path of the generated Markdown file
	 * 
	 */
	public function generate($project_id, $output_file, $options = array())
	{
		// Use generate_for_pdf() to exclude any unnecessary HTML and css
		$html = $this->ci->html_report->generate_for_pdf($project_id, $options);

		$converter = new HtmlConverter(array(
			'strip_tags' => true,
			'hard_break' => true, // Convert <br> to line break only (\n)
			'remove_nodes' => 'script style' // Make sure we remove css script and style tags, on top of using generate_for_pdf(), so we don't have any inline styles in the markdown output
		));
		$converter->getEnvironment()->addConverter(new TableConverter()); // Table converter isn't included by default

		$markdown = $converter->convert($html);
		file_put_contents($output_file, trim($markdown) . PHP_EOL);

		return $output_file;
	}

}

