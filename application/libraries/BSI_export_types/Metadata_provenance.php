<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


class Metadata_provenance
{
        /** @var string */
    public $provenance_title;
    /** @var string */
    public $provenance_generation_text;
  
    public function __construct(string $generated_on_local_datetime, string $timezone)
    {
        $this->ci =& get_instance();
        $this->ci->load->helper('language');
        $this->ci->lang->load('project');

        $this->provenance_title = lang("download_provenance_title");
        $this->provenance_generation_text = lang("download_provenance_generation_text");

        $this->provenance_generation_text = str_replace("{date}", $generated_on_local_datetime, $this->provenance_generation_text);
        $this->provenance_generation_text = str_replace("{timezone}", $timezone, $this->provenance_generation_text);
    }

    public function get_provenance_text_markdown()
    {
        $markdown_divider = "\n\n***\n\n";                  
        return $this->provenance_title . $markdown_divider . $this->provenance_generation_text . $markdown_divider;

    }
}
