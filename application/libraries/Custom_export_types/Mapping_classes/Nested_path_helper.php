<?php

class Nested_path_helper
{
    public function __construct() {}

    public function get_value_from_path(mixed $incoming_data, string | null $path, string $path_delimiter): mixed
    {
        if ($path === null) {
            return null;
        }

        $path_parts = explode($path_delimiter, $path);
        $value = $incoming_data;
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

    public function build_object_from_path(mixed $input_value, string $output_path, string $path_delimiter): array
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

    public function merge_output_arrays(array $base, array $addition, string $path = ''): array
    {
        if (array_is_list($base) && array_is_list($addition)) {
            return array_merge($base, $addition);
        }
        foreach ($addition as $key => $value) {
            $here = ltrim("$path.$key", '.');
            if (!array_key_exists($key, $base)) {
                $base[$key] = $value;
            } elseif (is_array($base[$key]) && is_array($value)) {
                $base[$key] = $this->merge_output_arrays($base[$key], $value, $here);
            } else {
                throw new Exception("Mapping rules collide at: " . $here);
            }
        }
        return $base;
    }
}
