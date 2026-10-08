<?php

use Symfony\Component\Yaml\Yaml;

require_once 'application/libraries/Custom_export_types/Mapping_classes/Nested_path_helper.php';
require_once 'application/libraries/Custom_export_types/Project_export_transformer_manager.php';
require_once 'application/libraries/Custom_export_types/Mapping_classes/Mapping_rule.php';
require_once 'application/libraries/Custom_export_types/Mapping_classes/Schema_transform_mapping_rules.php';

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
     * @param Mapping_rule[] $mapping_rules - Array of mapping rules which give the source, target, and transformation details for mapping input to output
     * @param string $path_delimiter - Path notation for accessing nested values
     * @param array $project_json - Project JSON data
     * @return array - Generated JSON
     */
    public function apply_export_mappings(Schema_transform_mapping_rules $map, array $project_json): array
    {

        $transformer_manager = new Project_export_transformer_manager();
        $nested_path_helper = new Nested_path_helper();
        $path_delimiter = $map->map_metadata->path_delimiter;
        $mapping_rules = $map->mapping_rules;
        $mapping_defaults = $map->mapping_defaults ?? [];

        $result_json = [];
        $result_json['logs'] = [];
        // $result_json['mapping_rules'] = $mapping_rules;
        // $result_json['project_json'] = $project_json;

        foreach ($mapping_rules as $rule) {

            $project_json_path = $rule->source;
            $incoming_value = $nested_path_helper->get_value_from_path($project_json, $project_json_path, $path_delimiter);

            if (isset($rule->filter) && !empty($rule->filter) && is_array($incoming_value)) {
                $incoming_value = $this->filter_incoming_values($incoming_value, $rule->filter);
            }

            if ($incoming_value === null) {
                continue; // Skip this mapping rule if the project value is null after filtering
            }

            // Apply default value from mapping defaults if incoming value is null
            if (isset($rule->default_value) && isset($mapping_defaults[$rule->id]) && $incoming_value === null) {
                $incoming_value = $mapping_defaults[$rule->id];
            }

            $target = $rule->target ?? null;
            $output_path = $target['json_path'] ?? null;
            $output_property = $target['property'] ?? null;
            $transformer = $rule->transformer ?? null;
            $transformed_value = $incoming_value;

            if ($incoming_value && $transformer) {
                $transformed_value = $transformer_manager->call(
                    $transformer,
                    $incoming_value
                );
            }

            $result_json['logs'][] = "Transformed value for {$project_json_path}: " . print_r($transformed_value, true) . "-----" . "Applied transformer: " . ($transformer ?? 'none') . "-----" . "Original value: " . print_r($incoming_value, true);



            if ($transformed_value !== null && $output_path) {
                $output = $nested_path_helper->build_object_from_path($transformed_value, $output_path, $path_delimiter);
                $result_json = array_merge_recursive($result_json, $output); // array_merge_recursive travels down into the paths and adds values at the correct levels            
            }
        }
        return $result_json;
    }

    private function filter_incoming_values(array $incoming_array, array $filter_array)
    {
        $filtered_items = [];

        foreach ($incoming_array as $item) {
            $include_item = $this->check_if_value_matches_all_filters($item, $filter_array);

            if ($include_item) {
                $filtered_items[] = $item;
            }
        }
        return $filtered_items;
    }

    private function check_if_value_matches_all_filters(array $item, array $filter_array): bool
    {
        $include_item = true;

        foreach ($filter_array as $filter_key => $filter_value) {
            if (!isset($item[$filter_key]) || $item[$filter_key] != $filter_value) {
                $include_item = false;
                break;
            }
        }
        return $include_item;
    }
}
