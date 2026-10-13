<?php

use Symfony\Component\Yaml\Yaml;

require_once APPPATH . 'libraries/Custom_export_types/Mapping_classes/Nested_path_helper.php';
require_once APPPATH . 'libraries/Custom_export_types/Project_export_transformer_manager.php';
require_once APPPATH . 'libraries/Custom_export_types/Mapping_classes/Mapping_rule.php';
require_once APPPATH . 'libraries/Custom_export_types/Mapping_classes/Schema_transform_mapping_rules.php';

class Project_export_yaml_map_parser
{
    private const RULE_SELF_VALUE = '@value';

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
     * @param Schema_transform_mapping_rules $map - The mapping rules and defaults for the export
     * @param array $incoming_json - The incoming project JSON data
     * @return array - The resulting JSON after applying the mapping rules
     */
    public function apply_export_mappings(Schema_transform_mapping_rules $map, array $incoming_json): array
    {
        $transformer_manager = new Project_export_transformer_manager();
        $nested_path_helper = new Nested_path_helper();
        $path_delimiter = $map->map_metadata->path_delimiter;
        $mapping_rules = $map->mapping_rules;
        $mapping_defaults = $map->mapping_defaults;

        $result_json = [];

        foreach ($mapping_rules as $rule) {

            $output_value = $this->apply_mapping_rule($rule, $incoming_json, $path_delimiter, $transformer_manager, $nested_path_helper, $mapping_defaults);
            $output_path = $rule->target ?? null;

            if ($output_value !== null && $output_path) {
                $output = $nested_path_helper->build_object_from_path($output_value, $output_path, $path_delimiter);
                $result_json = $nested_path_helper->merge_output_arrays($result_json, $output);
            }
        }
        return $result_json;
    }

    /**
     * Apply a single mapping rule. This function is used recursively to apply mapping rules at different levels of the incoming JSON structure. The incoming scope is either the whole project JSON, or a value within the JSON, depending on the level of recursion.
     */
    private function apply_mapping_rule(Mapping_rule $rule, mixed $scope, string $path_delimiter, Project_export_transformer_manager $transformer_manager, Nested_path_helper $nested_path_helper, array $mapping_defaults): mixed
    {
        $result_value = $this->get_value_from_source($rule, $scope, $path_delimiter, $nested_path_helper);
        $result_value ??= $this->map_to_default($result_value, $rule->default_value, $mapping_defaults);

        if ($rule->filter && is_array($result_value)) {
            $result_value = $this->filter_values($result_value, $rule->filter);
        }

        if ($result_value === null || is_array($result_value) && empty($result_value)) {
            return null; // Skip this mapping if the incoming value is null after filtering and applying defaults
        }

        if (!$rule->field_mappings) {
            if ($result_value !== null && $rule->transformer) {
                $result_value = $transformer_manager->call(
                    $rule->transformer,
                    $result_value,
                    $rule->parameters
                );
            }
            return $result_value ?? null;
        }

        // If there ARE field mappings, the result value is an object that needs its fields to be mapped into specific datacite object fields before return.
        $result_objects = $this->build_result_objects($rule, $result_value, $path_delimiter, $transformer_manager, $nested_path_helper, $mapping_defaults);
        return $result_objects;
    }

    private function get_value_from_source(Mapping_rule $rule, mixed $scope, string $path_delimiter, Nested_path_helper $nested_path_helper): mixed
    {
        $incoming_value_json_path = $rule->source;

        if ($incoming_value_json_path === self::RULE_SELF_VALUE) {
            $incoming_value = $scope; // In this situation, we're dealing with a field mapping that uses the incoming data as its value
        } else {
            $incoming_value = $nested_path_helper->get_value_from_path($scope, $incoming_value_json_path, $path_delimiter);
        }
        return $incoming_value;
    }

    private function build_result_objects(Mapping_rule $rule, mixed $value, string $path_delimiter, Project_export_transformer_manager $transformer_manager, Nested_path_helper $nested_path_helper, array $mapping_defaults): mixed
    {

        if (!is_array($value) || !array_is_list($value)) {
            $value = [$value];
        }

        $result_objects = array_map(
            fn($row) => $this->build_result_object_from_field_mappings($rule->field_mappings, $row, $path_delimiter, $transformer_manager, $nested_path_helper, $mapping_defaults),
            $value
        );

        // If there is only one result object and the mapping expects a single object, return a single object
        if (count($result_objects) === 1 && $rule->mode === 'single_object') {
            return $result_objects[0];
        }
        return $result_objects;
    }


    private function build_result_object_from_field_mappings(array $field_mappings, mixed $row, string $path_delimiter, Project_export_transformer_manager $transformer_manager, Nested_path_helper $nested_path_helper, array $mapping_defaults): array
    {
        $output_object = [];
        foreach ($field_mappings as $field_mapping) {
            $value = $this->apply_mapping_rule($field_mapping, $row, $path_delimiter, $transformer_manager, $nested_path_helper, $mapping_defaults);
            $target_path = $field_mapping->target;

            if ($value !== null && $target_path !== null) {
                $output_object[$target_path] = $value;
            }
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
            if (!isset($item[$filter_key]) || $item[$filter_key] !== $filter_value) {
                $include_item = false;
                break;
            }
        }
        return $include_item;
    }
}
