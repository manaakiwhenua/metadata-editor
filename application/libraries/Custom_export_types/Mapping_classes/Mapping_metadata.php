<?php

class Mapping_metadata
{
    public readonly string $id;
    public readonly string $version;
    public readonly array $source;
    public readonly array $target;
    public readonly string $path_delimiter;
    public readonly array $original_raw_fields;

    public function __construct(array $metadata_array)
    {
        $this->id = $metadata_array['id'] ?? null;
        $this->version = $metadata_array['version'] ?? null;
        $this->source = $metadata_array['source'] ?? [];
        $this->target = $metadata_array['target'] ?? [];
        $this->path_delimiter = $metadata_array['path_delimiter'] ?? '.';
        $this->original_raw_fields = $metadata_array['original_raw_fields'] ?? [];
    }
}
