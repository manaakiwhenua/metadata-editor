<?php

/**
 * BSI Core to DataCite 4.7 mapping tests.
 * Run: php tests/BSI_Core_to_Datacite_4.7_DOI_tests.php
 */

define('BASEPATH', __DIR__ . '/../system/');
define('APPPATH', __DIR__ . '/../application/');

require_once APPPATH . 'libraries/Custom_export_types/Project_export_mapping_resolver.php';
require_once __DIR__ . '/Custom_test_helpers/Custom_test_helpers.php';

// Any mapping rules need to be passed as an array of mapping rules, as 
$doi_rule = [[
    'id' => 'primary_identifier',
    'source' => 'optional_additional_metadata.resourceIdentifier',
    'target' => 'data.attributes.doi',
    'transformer' => 'strip_to_doi_identifier',
]];

$bsi_core_doi_data = ['optional_additional_metadata' => ['resourceIdentifier' => 'https://doi.org/10.1234/ABCD']];

$bsi_core_doi_data_invalid = ['optional_additional_metadata' => ['resourceIdentifier' => 'invalid_doi']];

$expected_resulting_datacite_doi = ['data' => ['attributes' => ['doi' => '10.1234/abcd']]];

$mapping_metadata = ['path_delimiter' => '.'];

// Test DOI mapping with valid DOIs
assert_eq($expected_resulting_datacite_doi, run_mapping($mapping_metadata, $doi_rule, $bsi_core_doi_data), 'DOI mapping');

// Test DOI mapping with invalid DOIs
assert_throws(function () use ($mapping_metadata, $doi_rule, $bsi_core_doi_data_invalid) {
    run_mapping($mapping_metadata, $doi_rule, $bsi_core_doi_data_invalid);
}, 'DOI mapping with missing source should throw');

// Test DOI mapping with DOI not present in the source
assert_eq(
    [],
    run_mapping($mapping_metadata, $doi_rule, ['optional_additional_metadata' => []]),
    'Missing source produces no output'
);
