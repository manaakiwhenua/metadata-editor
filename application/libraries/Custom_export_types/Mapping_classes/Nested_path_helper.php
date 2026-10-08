<?php

class Nested_path_helper
{
    public function __construct() {}

    public function get_value_from_path(array $project_json, string | null $path, string $path_delimiter): mixed
    {
        if ($path === null) {
            return null;
        }

        $path_parts = explode($path_delimiter, $path);
        $value = $project_json;
        foreach ($path_parts as $part) {
            if (is_array($value) && array_key_exists($part, $value)) {
                $value = $value[$part];
            } else {
                $value = null;
                break;
            }
        }
        return $value;
    }

    public function build_object_from_path(array | string $input_value, string $output_path, string $path_delimiter)
    {

        $path_parts = explode($path_delimiter, $output_path);
        $result = [];
        $current = &$result;

        foreach ($path_parts as $index => $part) {
            if ($index === count($path_parts) - 1) {
                $current[$part] = $input_value;
            } else {
                if (!isset($current[$part]) || !is_array($current[$part])) {
                    $current[$part] = [];
                }
                $current = &$current[$part];
            }
        }

        return $result;
    }
}
