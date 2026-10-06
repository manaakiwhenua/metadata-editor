<?php

class Project_export_transformer_manager
{
    protected $transformer_map = array(
        'year_from_date'          => array('Datacite_date_transformers', 'year_from_date'),
        'datacite_date'           => array('Datacite_date_transformers', 'datacite_date'),
        'updated_dates'           => array('Datacite_date_transformers', 'updated_dates'),
        // add the other transformers here
    );

    public function call($name, $input, $parameters = array())
    {
        if (!isset($this->transformer_map[$name])) {
            throw new Exception("Unknown transform in export: " . $name);
        }

        // Call_user_func will call the function passed to it as an array of [class, method] with the given input and parameters
        return call_user_func($this->transformer_map[$name], $input, $parameters);
    }
}
