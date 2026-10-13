<?php

use Symfony\Component\Yaml\Yaml;

class Project_export_yaml_map_parser
{
    public function __construct()
    {
        $this->ci = &get_instance();
    }

    public function parse_yaml_map(string $file_path): array
    {
        if (!file_exists($file_path)) {
            throw new Exception("YAML file not found: " . $file_path);
        }

        $yaml_content = Yaml::parseFile($file_path);
        return $yaml_content;
    }
}
