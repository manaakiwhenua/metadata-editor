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
        $result = $input_value;

        for ($i = count($path_parts) - 1; $i >= 0; $i--) {
            $result = [$path_parts[$i] => $result];
        }

        return $result;
    }

    public function merge_output_arrays(array $base, array $addition, string $path = ''): array
    {
        $base_is_list = array_is_list($base);
        $addition_is_list = array_is_list($addition);

        if ($base_is_list && $addition_is_list) {
            return array_merge($base, $addition);
        }

        if (($base !== [] && $base_is_list) || ($addition !== [] && $addition_is_list)) {
            throw new LogicException("Cannot merge a list with an object at '$path'");
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
