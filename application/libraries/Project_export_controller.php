<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'application/libraries/IProject_export_writer.php';

class Project_export_controller
{
    /**
	 * Constructor
	 */
	function __construct()
	{
		$this->ci =& get_instance();
		$this->ci->load->model('Editor_model');
	}

	/**
	 * Download project export
	 *
	 * @param IProject_export_writer $export_writer
	 * @param int $project_id
	 * @param array $options
	 */
    function download_project_export(IProject_export_writer  $export_writer, int $project_id, $options=array())
    {
        $file_path=$this->generate_project_export($export_writer, $project_id, $options);

		if(file_exists($file_path)){
			$this->ci->load->helper('download');
			$filename = 'project_metadata_' . $project_id . '.' . $export_writer->file_extension();
			force_download($filename, file_get_contents($file_path), $set_mime = TRUE); //set_mime detects mime type based on file extension
		}
		else{
			throw new Exception("Download project '" . $export_writer->export_type() . "': File not found: " . $file_path);
		}
    }

	function generate_project_export(IProject_export_writer $export_writer, int $project_id, $options = array())
	{
		set_time_limit(0);

		$project = $this->ci->Editor_model->get_row($project_id);
		$project_folder = $this->ci->Editor_model->get_project_folder($project_id);

		if (!$project || !$project_folder || !file_exists($project_folder)) {
			throw new Exception("Download project '" . $export_writer->export_type() . "': Project folder not found");
		}

		$filename = trim((string)$project['idno']) !== '' ? trim($project['idno']) : nada_hash($project_id);
		$output_file = $project_folder . '/' . $filename . '.' . $export_writer->file_extension();

		return $export_writer->generate($project_id, $output_file, $options);
	}

}