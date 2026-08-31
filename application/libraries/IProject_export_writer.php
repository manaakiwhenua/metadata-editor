<?php
// Note that interfaces in PHP below 8.4 can't specify properties, so export_type, file_extension, and mime_type are implemented as methods
interface IProject_export_writer {
    public function export_type(); // e.g. "json", "markdown", "html"
    public function file_extension(); // e.g. "json", "md", "html"
    public function mime_type(); // e.g. "application/json"
    public function generate(int $project_id, string $output_file, array $options = array()); // returns absolute file path
}
