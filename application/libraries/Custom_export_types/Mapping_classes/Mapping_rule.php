<?php

class Mapping_rule
{
    public readonly ?string $id;
    public readonly ?string $source;
    public readonly ?array $sources;
    public readonly ?array $target;
    public readonly ?array $targets;
    public readonly ?string $transformer;
    public readonly ?string $mode;
    public readonly array $filter;
    public readonly array $parameters;
    public readonly array $field_mappings;
    public readonly array $original_raw_fields;

    public function __construct(
        array $mapping_fields
    ) {
        $this->id = $mapping_fields['id'] ?? null;
        $this->source = $mapping_fields['source'] ?? null;
        $this->sources = $mapping_fields['sources'] ?? null;
        $this->target = $mapping_fields['target'] ?? null;
        $this->targets = $mapping_fields['targets'] ?? null;
        $this->transformer = $mapping_fields['transform'] ?? null;
        $this->mode = $mapping_fields['mode'] ?? null;
        $this->filter = $mapping_fields['filter'] ?? [];
        $this->parameters = $mapping_fields['parameters'] ?? [];
        $this->field_mappings = $mapping_fields['field_mappings'] ?? [];
        $this->original_raw_fields = $mapping_fields;
    }
}
