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
     * @param array $incoming_json - Incoming project JSON data
     * @return array - Generated JSON
     */
    public function apply_export_mappings(Schema_transform_mapping_rules $map, array $incoming_json): array
    {
        $transformer_manager = new Project_export_transformer_manager();
        $nested_path_helper = new Nested_path_helper();
        $path_delimiter = $map->map_metadata->path_delimiter;
        $mapping_rules = $map->mapping_rules;
        $mapping_defaults = $map->mapping_defaults ?? [];

        $result_json = [];

        foreach ($mapping_rules as $rule) {

            $output_value = $this->apply_mapping_rule($rule, $incoming_json, $path_delimiter, $transformer_manager, $nested_path_helper, $mapping_defaults);
            $output_path = $rule->target['json_path'] ?? null;

            if ($output_value !== null && $output_path) {
                $output = $nested_path_helper->build_object_from_path($output_value, $output_path, $path_delimiter);
                $result_json = array_merge_recursive($result_json, $output); // array_merge_recursive travels down into the paths and adds values at the correct levels            
            }
        }
        return $result_json;
    }

    private function apply_mapping_rule(Mapping_rule $rule, array | string $incoming_json, string $path_delimiter, Project_export_transformer_manager $transformer_manager, Nested_path_helper $nested_path_helper, array $mapping_defaults)
    {
        $incoming_value_json_path = $rule->source;

        if ($incoming_value_json_path === '@value') {
            $incoming_value = $incoming_json; // In this situation, we're dealing with a field mapping that uses the entire incoming JSON as its value
        } else {
            $incoming_value = $nested_path_helper->get_value_from_path($incoming_json, $incoming_value_json_path, $path_delimiter);
        }

        $field_mappings = $rule->field_mappings ?? [];
        $transformer = $rule->transformer ?? null;
        $result_value = $incoming_value;

        if ($result_value === null) {
            $result_value = $this->map_to_default($result_value, $rule->default_value, $mapping_defaults);
        }

        if (isset($rule->filter) && !empty($rule->filter) && is_array($result_value)) {
            $result_value = $this->filter_values($result_value, $rule->filter);
        }

        if ($result_value === null) {
            return null; // Skip this mapping if the incoming value is null after filtering and applying defaults
        }

        if (empty($field_mappings)) {
            if ($result_value && $transformer) {
                $result_value = $transformer_manager->call(
                    $transformer,
                    $result_value
                );
            }
            return $result_value;
        }

        // If there ARE field mappings, the result value is an object that needs its fields to be mapped into specific datacite object fields before return.
        if (!is_array($result_value) || !array_is_list($result_value)) {
            $result_value = [$result_value];
        }

        $result_objects = array_map(
            fn($row) => $this->build_result_object_from_field_mappings($rule->field_mappings, $row, $path_delimiter, $transformer_manager, $nested_path_helper, $mapping_defaults),
            $result_value
        );
        return $result_objects;
    }

    /**
     * Recursively builds a result object from the given field mappings and row data. Each field mapping is resolved, and the resulting values are placed into the output object according to their target paths.
     */
    private function build_result_object_from_field_mappings(array $field_mappings, $row, string $path_delimiter, $transformer_manager, $nested_path_helper, $mapping_defaults): array
    {
        $output_object = [];
        foreach ($field_mappings as $field_mapping) {
            $value = $this->apply_mapping_rule($field_mapping, $row, $path_delimiter, $transformer_manager, $nested_path_helper, $mapping_defaults);
            $target_path = $field_mapping->target ? $field_mapping->target['json_path'] : null;

            if ($value !== null && $target_path !== null) {
                $output_object[$target_path] = $value;
            }
            // $output_object['logs'] = [
            //     'target path' => ($target_path ?? 'null'),
            //     'resolved value' => $value,
            //     'field mapping' => $field_mapping->source ?? null,
            //     'nested_path_helper output_object' => $nested_path_helper->get_value_from_path($row, $field_mapping->source ?? null, $path_delimiter),
            //     'field map default' => $field_mapping->default_value ?? null,
            //     'mapped to default' => $this->map_to_default($nested_path_helper->get_value_from_path($row, $field_mapping->source ?? null, $path_delimiter), $field_mapping->default_value ?? null, $mapping_defaults),

            // ];
        }

        return $output_object;
    }

    private function map_to_default(mixed $incoming_value, ?string $default_value_id, array $mapping_defaults)
    {
        if ($default_value_id !== null && isset($mapping_defaults[$default_value_id]) && $incoming_value === null) {
            $incoming_value = $mapping_defaults[$default_value_id];
        }
        return $incoming_value;
    }

    private function filter_values(array $incoming_array, array $filter_array)
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
