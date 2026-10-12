<?php

class String_transformers
{
    public static function transform_using_value_map(string $input, array $parameters)
    {
        $value_map = $parameters['value_map'] ?? [];
        return $value_map[$input] ?? null;
    }
}
