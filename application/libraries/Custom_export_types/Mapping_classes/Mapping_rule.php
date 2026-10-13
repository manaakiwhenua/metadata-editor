<?php

class Mapping_rule
{
    public readonly ?string $id;
    public readonly ?string $source;
    public readonly ?array $sources;
    public readonly ?string $target;
    public readonly ?array $targets;
    public readonly ?string $transformer;
    public readonly ?array $value_map;
    public readonly ?string $mode;
    public readonly array $filter;
    public readonly array $parameters;
    public readonly array $field_mappings;
    public readonly ?string $default_value;
    public readonly array $original_raw_fields;

    public function __construct(
        array $mapping_fields
    ) {
        $this->id = $mapping_fields['id'] ?? null;
        $this->source = $mapping_fields['source'] ?? null;
        $this->sources = $mapping_fields['sources'] ?? null;
        $this->target = $mapping_fields['target'] ?? null;
        $this->targets = $mapping_fields['targets'] ?? null;
        $this->transformer = $mapping_fields['transformer'] ?? null;
        $this->mode = $mapping_fields['mode'] ?? null;
        $this->filter = $mapping_fields['filter'] ?? [];
        $this->parameters = $mapping_fields['parameters'] ?? [];

        //Field mappings is another, inner array of Mapping_rule, for the fields of objects within the main mapping rule.
        $this->field_mappings = array_map(
            fn($field_mapping) => new Mapping_rule($field_mapping),
            $mapping_fields['field_mappings'] ?? [],
        );

        $this->default_value = $mapping_fields['default_value'] ?? null;
        $this->original_raw_fields = $mapping_fields;

        $this->validate_mapping_rule();
    }

    private function validate_mapping_rule(): void
    {
        if ($this->transformer && $this->field_mappings) {
            throw new Exception("Datacite mapping error: Top-level transformation cannot be applied when field mappings are present for rule: " . $this->id);
        }

        if (!$this->target && !$this->targets) {
            throw new Exception("Datacite mapping error: Either target or targets must be set for rule: " . $this->id);
        }
    }
}
