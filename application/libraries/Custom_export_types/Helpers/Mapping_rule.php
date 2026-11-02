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
    public readonly array $inner_object_mappings;
    public readonly ?string $default_value_id;
    public readonly mixed $default_value;
    public readonly array $original_raw_fields;

    public function __construct(
        array $mapping_fields,
        array $mapping_defaults
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

        $this->default_value_id = $mapping_fields['default_value'] ?? null;
        $this->default_value = $mapping_defaults[$this->default_value_id] ?? null;
        $this->original_raw_fields = $mapping_fields;

        //Inner_object_mappings is another, inner array of Mapping_rule, for the fields of objects within the main mapping rule.
        $this->inner_object_mappings = array_map(
            fn($field_mapping) => new Mapping_rule($field_mapping, $mapping_defaults),
            $mapping_fields['inner_object_mappings'] ?? [],
        );

        $this->validate_mapping_defaults();
        $this->validate_targets();
        $this->validate_transformers();
    }

    private function validate_targets(): void
    {
        if (!$this->target && !$this->targets) {
            throw new Exception("Mapping error: Either target or targets must be set for rule: " . $this->id . " With source: " . $this->source);
        }
    }

    private function validate_mapping_defaults(): void
    {
        if ($this->default_value_id !== null && $this->default_value === null) {
            throw new Exception("Mapping error: Default value id '" . ($this->default_value_id ?? 'unknown') . "' is unknown or empty.");
        }
    }

    private function validate_transformers(): void
    {
        if ($this->transformer && $this->inner_object_mappings) {
            throw new Exception("Mapping error: Top-level transformation cannot be applied when inner object mappings are present. Check and correct rule: '" . $this->id . "'.");
        }
    }
}
