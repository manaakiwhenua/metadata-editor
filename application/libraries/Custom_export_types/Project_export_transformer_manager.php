<?php

require_once 'application/libraries/Custom_export_types/Transformers/Date_transformers.php';

class Project_export_transformer_manager
{
    protected $transformer_map = array(
        'year_from_date'          => array('Date_transformers', 'year_from_date'),
        'datacite_date'           => array('Date_transformers', 'datacite_date'),
        'updated_dates'           => array('Date_transformers', 'updated_dates'),
        // add the other transformers here
    );

    public function call(string $name, array | string $input, array $parameters = array()): array | string
    {
        //TODO add graceful handling for unknown transformers
        if (!isset($this->transformer_map[$name])) {
            // throw new Exception("Unknown transform in export: " . $name);
            return ''; // While we're building the transformer map, return empty string for unknown transformers
        }

        // Call_user_func will call the function passed to it as an array of [class, method] with the given input and parameters
        return call_user_func($this->transformer_map[$name], $input, $parameters);
    }
}
