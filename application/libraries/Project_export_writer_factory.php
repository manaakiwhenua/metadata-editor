<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Project_export_writer_factory
{
    /** @var array<string, string> */
    protected $writerMap = array(
        'markdown' => 'Project_markdown_writer',
    );

    /**
     * Constructor
     */
    function __construct()
    {
        $this->ci =& get_instance();
    }

    /**
     * Create an instance of a project export writer based on the specified type.
     *
     * @param string $type - The type of export writer (e.g., "DCAP", "markdown").
     * @return IProject_export_writer - An instance of the corresponding export writer.
     * @throws Exception - If the specified type is not supported.
     */
    function create_writer($type)
    {
        $type = strtolower(trim((string) $type));

        if (!isset($this->writerMap[$type])) {
            throw new Exception("Unsupported project export writer type: " . $type);
        }

        $className = $this->writerMap[$type];
        $this->ci->load->library($className);

        $property = strtolower($className); // Code Igniter automatically converts class names to lowercase property names when loading libraries, so use lowercase to access the instance.
        return $this->ci->$property;
    }
}