<?php

class Datacite_specific_transformers
{
    public static function strip_to_doi_identifier(string $input): string
    {
        $input = trim($input);
        $input = strtolower($input);
        $regex = '/^(doi:|https:\/\/doi\.org\/|http:\/\/doi\.org\/)/';
        $input = preg_replace($regex, '', $input);
        return $input;
    }
}
