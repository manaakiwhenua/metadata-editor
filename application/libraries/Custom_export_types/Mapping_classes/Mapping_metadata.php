<?php

class Mapping_metadata
{
    public readonly string $id;
    public readonly string $version;
    public readonly array $source;
    public readonly array $target;
    public readonly string $path_notation;
    public readonly array $original_raw_fields;

    public function __construct(array $profile_array)
    {
        $this->id = $profile_array['id'] ?? null;
        $this->version = $profile_array['version'] ?? null;
        $this->source = $profile_array['source'] ?? [];
        $this->target = $profile_array['target'] ?? [];
        $this->path_notation = $profile_array['path_notation'] ?? '.';
        $this->original_raw_fields = $profile_array['original_raw_fields'] ?? [];
    }
}
