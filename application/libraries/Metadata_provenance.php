<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


class Metadata_provenance
{
    /** @var string */
    public $project_owner;
    /** @var DateTimeImmutable */
    public $generated_on;
    /** @var string */
    public $provenance_title;
    /** @var string */
    public $provenance_project_owner;
    /** @var string */
    public $provenance_generation_text;
  
    public function __construct(string $project_owner, int $generated_on_unix_timestamp)
    {
        $this->ci =& get_instance();
        $this->ci->load->helper('language');
        $this->ci->lang->load('project');

        $this->project_owner = $project_owner ?? '';
        $this->generated_on = new DateTimeImmutable("@$generated_on_unix_timestamp");
        $this->provenance_title = lang("download_provenance_title");
        $this->provenance_generation_text = lang("download_provenance_generation_text");

        $this->provenance_project_owner = lang("download_project_owned_by");
        $this->provenance_project_owner = str_replace("{owner}", $this->project_owner, $this->provenance_project_owner);

        $this->provenance_generation_text = str_replace("{date}", $this->generated_on->format("Y-m-d H:i:s"), $this->provenance_generation_text);
    }

    public function get_provenance_text_markdown()
    {
        $markdown_divider = "\n\n***\n\n";                  
        return $this->provenance_title . $markdown_divider . $this->provenance_generation_text . "\n\n" . $this->provenance_project_owner . $markdown_divider;

    }
}
