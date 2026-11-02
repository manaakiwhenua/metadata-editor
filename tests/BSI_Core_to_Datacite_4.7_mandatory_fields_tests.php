<?php

/**
 * BSI Core to DataCite 4.7 mapping tests.
 * Run => php tests/BSI_Core_to_Datacite_4.7_mandatory_fields_tests.php
 */

define('BASEPATH', __DIR__ . '/../system/');
define('APPPATH', __DIR__ . '/../application/');

require_once APPPATH . 'libraries/Custom_export_types/Project_export_mapping_resolver.php';
require_once __DIR__ . '/Custom_test_helpers/Custom_test_helpers.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Allow YAML parsing via Symfony\Component\Yaml\Yaml in Project_export_yaml_map_parser.php
require_once APPPATH . 'libraries/Custom_export_types/Project_export_yaml_map_parser.php';

$datacite_map_file = APPPATH . 'libraries/Custom_export_types/Datacite/BSI_Core_to_DataCite_4.7_map.yaml';
$bsi_core_mandatory_fields_file = __DIR__ . '/Custom_test_fixtures/BSI_core_json_mandatory_fields_expected.json';
$expected_datacite_file = __DIR__ . '/Custom_test_fixtures/Datacite_4.7_mandatory_fields_expected.json';

$yaml_parser = new Project_export_yaml_map_parser;

$datacite_map_raw =  $yaml_parser->parse_yaml_map($datacite_map_file);
$datacite_map_metadata = $datacite_map_raw['profile'] ?? [];
$datacite_mapping_rules = $datacite_map_raw['mappings'] ?? [];
$datacite_defaults = $datacite_map_raw['defaults'] ?? [];

$bsi_core_mandatory_fields_json = json_decode(file_get_contents($bsi_core_mandatory_fields_file), true, 512, JSON_THROW_ON_ERROR);

$expected_datacite = json_decode(file_get_contents($expected_datacite_file), true, 512, JSON_THROW_ON_ERROR);

// Test mapping of BSI Core mandatory fields to DataCite 4.7 mandatory fields
assert_eq($expected_datacite, run_mapping($datacite_map_metadata, $datacite_mapping_rules, $bsi_core_mandatory_fields_json, $datacite_defaults), 'Mandatory fields mapping');
