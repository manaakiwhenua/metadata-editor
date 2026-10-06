<?php

require_once 'application/libraries/Custom_export_types/Mapping_classes/Mapping_rule.php';
require_once 'application/libraries/Custom_export_types/Mapping_classes/Mapping_metadata.php';

class Schema_transform_mapping_rules
{
    public readonly array $mapping_rules;
    public readonly Mapping_metadata $map_metadata;

    public function __construct(array $map_metadata = [], array $mapping_rules = [])
    {
        $this->map_metadata = new Mapping_metadata($map_metadata);
        $this->mapping_rules = array_map(fn($mapping_rule) => new Mapping_rule($mapping_rule), $mapping_rules); // Convert each mapping array to a Rule object
    }
}
