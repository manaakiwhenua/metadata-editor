<?php

require_once 'application/libraries/BSI_export_types/Datacite/Mapping_classes/Datacite_rule.php';
require_once 'application/libraries/BSI_export_types/Datacite/Mapping_classes/Datacite_map_metadata.php';

class Datacite_mapping_rules
{
    public readonly array $mapping_rules;
    public readonly Datacite_map_metadata $map_metadata;

    public function __construct(array $map_metadata = [], array $mapping_rules = [])
    {
        $this->map_metadata = new Datacite_map_metadata($map_metadata);
        $this->mapping_rules = array_map(fn($mapping_rule) => new Datacite_rule($mapping_rule), $mapping_rules); // Convert each mapping array to a Rule object
    }
}
