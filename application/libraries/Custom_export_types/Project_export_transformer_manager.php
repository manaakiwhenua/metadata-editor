<?php

require_once APPPATH . 'libraries/Custom_export_types/Transformers/Date_transformers.php';
require_once APPPATH . 'libraries/Custom_export_types/Transformers/Url_transformers.php';
require_once APPPATH . 'libraries/Custom_export_types/Transformers/String_transformers.php';

class Project_export_transformer_manager
{
    protected $transformer_map = array(
        'year_from_date'          => array('Date_transformers', 'year_from_date'),
        'datacite_date'           => array('Date_transformers', 'datacite_date'),
        'updated_dates'           => array('Date_transformers', 'updated_dates'),
        'strip_to_doi_identifier' => array('Url_transformers', 'strip_to_doi_identifier'),
        'normalise_orcid'          => array('Url_transformers', 'normalise_orcid'),
        'transform_using_value_map' => array('String_transformers', 'transform_using_value_map'),
        // add the other transformers here
    );

    public function call(string $name, mixed $input, array $parameters = array()): mixed
    {
        if (is_null($input)) {
            return null;
        }

        if (!isset($this->transformer_map[$name])) {
            throw new Exception("Datacite mapping error: Unknown transform in Datacite mapping: " . $name);
        }

        // Call_user_func will call the function passed to it as an array of [class, method] with the given input and parameters
        return call_user_func($this->transformer_map[$name], $input, $parameters);
    }
}
