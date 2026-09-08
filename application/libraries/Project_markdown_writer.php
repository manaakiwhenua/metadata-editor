<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

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
		$this->ci = &get_instance();
		$this->ci->load->library('Html_report');
	}

	public function export_type()
	{
		return "markdown";
	}

	public function file_extension()
	{
		return "md";
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

		$header_level_for_field_label = 5; // Some nested fields have label classes instead of html headers.

		// Use generate_for_pdf() to exclude any unnecessary HTML and css
		$html = $this->ci->html_report->generate_for_pdf($project_id, $options);
		$html = $this->convert_field_label_to_header($html, $header_level_for_field_label);

		$converter = new HtmlConverter(array(
			'strip_tags' => true,
			'hard_break' => true, // Convert <br> to line break only (\n)
			'remove_nodes' => 'script style', // Make sure we remove css script and style tags, on top of using generate_for_pdf(), so we don't have any inline styles in the markdown output
			'header_style' => 'atx', // Use ATX-style headers (e.g., #### Header). Do this for ease of duplicate nested header removal
		));
		$converter->getEnvironment()->addConverter(new TableConverter()); // Table converter isn't included by default
		$markdown = $converter->convert($html);
		$markdown = $this->remove_duplicate_nested_headers($markdown);
		$markdown = $this->replace_multiple_empty_lines_with_single($markdown);
		file_put_contents($output_file, $markdown . PHP_EOL);

		return $output_file;
	}

	private function convert_field_label_to_header(string $html, int $header_level_for_field_label)
	{
		$pattern = "/<div class=\"font-weight-bold field-label\">(.*?)<\/div>/";
		$replacement = "<h" . $header_level_for_field_label . ">$1</h" . $header_level_for_field_label . ">";
		$html = preg_replace($pattern, $replacement, $html);
		return $html;
	}

	private function replace_multiple_empty_lines_with_single(string $text)
	{
		$text = trim($text);
		$at_least_three_empty_lines = "/( ?\r?\n){3,}/"; // Match at least three consecutive empty lines, optionally preceded by a space
		$text = preg_replace($at_least_three_empty_lines, "\n\n", $text);
		return $text;
	}

	private function remove_duplicate_nested_headers(string $text)
	{
		// Remove duplicate nested headers (e.g., "## Header" followed by a newline then "### Header"). Remove only if the two headers are identical except for their header level and whitespace.
		$pattern = "/^(#{1,6})[ \t]*(.+?)[ \t]*(?:\R\R?#{1,6}[ \t]*\\2[ \t]*)+$/m";
		$text = preg_replace($pattern, "$1 $2\n", $text);
		return $text;
	}
}
