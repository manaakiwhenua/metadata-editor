<?php

require_once APPPATH . 'libraries/Custom_export_types/Mapping_classes/Nested_path_helper.php';
require_once APPPATH . 'libraries/Custom_export_types/Project_export_transformer_manager.php';
require_once APPPATH . 'libraries/Custom_export_types/Mapping_classes/Mapping_rule.php';
require_once APPPATH . 'libraries/Custom_export_types/Mapping_classes/Schema_transform_mapping_rules.php';

class Project_export_mapping_resolver
{
    private const RULE_SELF_VALUE = '@value';
    private const MODE_SINGLE_OBJECT = 'single_object';

    public function __construct(
        private Schema_transform_mapping_rules $map,
        private Project_export_transformer_manager $transformer_manager,
        private Nested_path_helper $nested_path_helper
    ) {}

    /**
     * Apply mapping rules to the project JSON and generate result JSON
     * @param array $incoming_json - The incoming project JSON data
     * @return array - The resulting JSON after applying the mapping rules
     */
    public function apply_export_mappings(array $incoming_json): array
    {
        $mapping_rules = $this->map->mapping_rules;
        $result_json = [];
        $path_delimiter = $this->map->map_metadata->path_delimiter;

        foreach ($mapping_rules as $rule) {

            try {
                $output_value = $this->apply_mapping_rule($rule, $incoming_json);
            } catch (Exception $e) {
                throw new Exception("Failed to apply mapping rule: " . ($rule->id ?? $rule->source) . ". " . $e->getMessage());
            }

            if ($output_value === null) {
                continue;
            }

            $output = $this->nested_path_helper->build_object_from_path($output_value, $rule->target ?? null, $path_delimiter);
            $result_json = $this->nested_path_helper->merge_output_arrays($result_json, $output);
        }
        return $result_json;
    }

    /**
     * Apply a single mapping rule. This function is used recursively to apply mapping rules at different levels of the incoming JSON structure. The incoming scope is either the whole project JSON, or a value within the JSON, depending on the level of recursion.
     */
    private function apply_mapping_rule(Mapping_rule $rule, mixed $scope): mixed
    {
        $value = $this->get_value_from_source($rule, $scope);
        $value ??= $rule->default_value; // Apply default value if the current value is null

        if ($rule->filter && is_array($value)) {
            $value = $this->filter_values($value, $rule->filter);
        }

        if (!$rule->field_mappings) {
            return $this->transform($rule, $value);
        }

        // If there ARE field mappings, the result value is an object that needs its fields to be mapped into specific datacite object fields before return.
        $value = $this->apply_field_mappings($rule, $value);

        return $value;
    }

    private function get_value_from_source(Mapping_rule $rule, mixed $scope): mixed
    {
        $incoming_value_json_path = $rule->source;
        $path_delimiter = $this->map->map_metadata->path_delimiter;

        if ($incoming_value_json_path === self::RULE_SELF_VALUE) {
            $incoming_value = $scope; // In this situation, we're dealing with a field mapping that uses the incoming data as its value
        } else {
            $incoming_value = $this->nested_path_helper->get_value_from_path($scope, $incoming_value_json_path, $path_delimiter);
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

    private function transform(Mapping_rule $rule, mixed $result_value): mixed
    {
        if (!$rule->transformer) {
            return $result_value;
        }

        $result_value = $this->transformer_manager->call(
            $rule->transformer,
            $result_value,
            $rule->parameters
        );

        return $result_value ?? null;
    }

    private function apply_field_mappings(Mapping_rule $rule, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (!is_array($value) || !array_is_list($value)) {
            $value = [$value];
        }

        $result_object_array = [];

        foreach ($value as $array_item) {
            $result_object = $this->build_field_mapped_object($rule->field_mappings, $array_item);
            if (!empty($result_object)) {
                $result_object_array[] = $result_object;
            }
        }

        if ($rule->mode !== self::MODE_SINGLE_OBJECT) {
            return $result_object_array ?? null;
        }

        if (count($result_object_array) !== 1) {
            throw new Exception("Mapping rule expected a single object, but produced " . count($result_object_array) . " objects");
        }

        return $result_object_array[0] ?? null;
    }

    private function build_field_mapped_object(array $field_mappings, mixed $row): array
    {
        $output_object = [];
        foreach ($field_mappings as $field_mapping) {
            $value = $this->apply_mapping_rule($field_mapping, $row);
            $target_path = $field_mapping->target;

            if ($value !== null && $target_path !== null) {
                $output_object[$target_path] = $value;
            }
        }

        return $output_object;
    }
}
