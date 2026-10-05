<?php

use Symfony\Component\Yaml\Yaml;

require_once 'application/libraries/BSI_export_types/Project_export_transformer_manager.php';
require_once 'application/libraries/BSI_export_types/Datacite/Mapping_classes/Datacite_rule.php';
require_once 'application/libraries/BSI_export_types/Datacite/Mapping_classes/Datacite_mapping_rules.php';

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


    /**
     * Apply mapping rules to the project JSON and generate result JSON
     *
     * @param Datacite_rule[] $mapping_rules - Array of mapping rules
     * @param string $path_notation - Path notation for accessing nested values
     * @param array $project_json - Project JSON data
     * @return array - Generated JSON
     */
    public function apply_mappings(array $mapping_rules, string $path_notation, array $project_json): array
    {
        $result_json = [];
        return $result_json;
    }
}
