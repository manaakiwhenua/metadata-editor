<?php

class Url_transformers
{
    public static function strip_to_doi_identifier(string $input): string
    {
        $input = trim($input);
        $input = strtolower($input);

        // Strip to the bare DOI id which is in the form of "10.xxxx/xxxxx"
        $doi_id_regex = '/^10\.\d{4,9}\/[-._;()\/:A-Z0-9]+$/i';
        $doi_url_regex = '/^(doi:|https:\/\/doi\.org\/|http:\/\/doi\.org\/)/i';
        $input = preg_replace($doi_url_regex, '', $input);

        if (!preg_match($doi_id_regex, $input)) {
            throw new Exception("Invalid DOI identifier: " . $input);
        }

        return $input;
    }

    public static function normalise_orcid(string $input): string
    {
        $input = trim($input);
        $input = strtolower($input);

        $orcid_regex_including_url = '/^(https:\/\/orcid\.org\/)?\d{4}-\d{4}-\d{4}-\d{4}$/i';

        if (!preg_match($orcid_regex_including_url, $input)) {
            throw new Exception("Invalid ORCID identifier: " . $input);
        }

        return $input;
    }
}
