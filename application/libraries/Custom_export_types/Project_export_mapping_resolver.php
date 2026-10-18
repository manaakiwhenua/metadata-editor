<?php

require_once APPPATH . 'libraries/Custom_export_types/Helpers/Nested_path_helper.php';
require_once APPPATH . 'libraries/Custom_export_types/Project_export_transformer_manager.php';
require_once APPPATH . 'libraries/Custom_export_types/Helpers/Mapping_rule.php';
require_once APPPATH . 'libraries/Custom_export_types/Helpers/Schema_transform_mapping_rules.php';

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

        if ($rule->filter && is_array($value) && array_is_list($value)) {
            $value = $this->filter_values($value, $rule->filter);
        }

        if (!$rule->inner_object_mappings) {
            return $this->transform($rule, $value);
        }

        // If there ARE inner object mappings, the result value is an object that needs its fields to be mapped into specific target object fields before return.
        $value = $this->apply_inner_object_mappings($rule, $value);

        return ($value === [] || $value === '') ? null : $value;
    }

    private function get_value_from_source(Mapping_rule $rule, mixed $scope): mixed
    {
        $incoming_value_json_path = $rule->source;
        $path_delimiter = $this->map->map_metadata->path_delimiter;

        if ($incoming_value_json_path === self::RULE_SELF_VALUE) {
            $incoming_value = $scope; // In this situation, we're dealing with an inner object mapping that uses the incoming data as its value
        } else {
            $incoming_value = $this->nested_path_helper->get_value_from_path($scope, $incoming_value_json_path, $path_delimiter);
        }
        return $incoming_value;
    }

    // We are only filtering arrays containing objects, as filtering requires key-value pairs to match against. Arrays containing simple values are ignored.
    private function filter_values(array $incoming_array, array $filter_key_value)
    {
        $filtered_items = [];

        foreach ($incoming_array as $item) {
            if (is_array($item)) {
                $include_item = $this->check_if_value_matches_all_filters($item, $filter_key_value);

                if ($include_item) {
                    $filtered_items[] = $item;
                }
            }
        }
        return $filtered_items;
    }

    private function check_if_value_matches_all_filters(array $item, array $filter_key_value): bool
    {
        $include_item = true;

        foreach ($filter_key_value as $filter_key => $filter_value) {
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

    private function apply_inner_object_mappings(Mapping_rule $rule, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (!is_array($value) || !array_is_list($value)) {
            $value = [$value];
        }

        $result_object_array = [];

        foreach ($value as $array_item) {
            $result_object = $this->build_field_mapped_object($rule->inner_object_mappings, $array_item);
            if (!empty($result_object)) {
                $result_object_array[] = $result_object;
            }
        }

        if ($rule->mode !== self::MODE_SINGLE_OBJECT) {
            return $result_object_array ?? null;
        }

        // More than one object should cause an exception when the mode is single object, but zero objects is okay, as optional fields exist
        if (count($result_object_array) > 1) {
            throw new Exception("Mapping rule expected a single object, but produced " . count($result_object_array) . " objects");
        }

        return $result_object_array[0] ?? null;
    }

    private function build_field_mapped_object(array $inner_object_mappings, mixed $row): array
    {
        $output_object = [];
        $path_delimiter = $this->map->map_metadata->path_delimiter;

        foreach ($inner_object_mappings as $field_mapping) {
            $value = $this->apply_mapping_rule($field_mapping, $row);
            $target_path = $field_mapping->target;

            if ($value !== null && $target_path !== null) {
                $field_value = $this->nested_path_helper->build_object_from_path($value, $target_path, $path_delimiter);
                $output_object = $this->nested_path_helper->merge_output_arrays($output_object, $field_value);
            }
        }

        return $output_object;
    }
}
