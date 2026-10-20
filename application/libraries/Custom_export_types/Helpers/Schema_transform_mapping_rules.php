<?php

require_once APPPATH . 'libraries/Custom_export_types/Helpers/Mapping_rule.php';
require_once APPPATH . 'libraries/Custom_export_types/Helpers/Mapping_metadata.php';

class Schema_transform_mapping_rules
{
    public readonly array $mapping_rules;
    public readonly Mapping_metadata $map_metadata;

    public function __construct(array $map_metadata = [], array $mapping_rules = [], array $mapping_defaults = [])
    {
        $this->map_metadata = new Mapping_metadata($map_metadata, $mapping_defaults);
        $this->mapping_rules = array_map(fn($mapping_rule) => new Mapping_rule($mapping_rule, $mapping_defaults), $mapping_rules); // Convert each mapping array to a Mapping rule object
    }
}
