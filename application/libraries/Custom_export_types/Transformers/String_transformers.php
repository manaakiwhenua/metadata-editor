<?php

class String_transformers
{
    public static function transform_using_value_map(string $input, array $value_map)
    {
        return $value_map[$input] ?? null;
    }
}
