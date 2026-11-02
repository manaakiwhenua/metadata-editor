<?php

/** Have to write our own assertion functions */
function assert_eq($expected, $actual, $label)
{
    if ($expected !== $actual) {
        fwrite(STDERR, "FAILED: {$label}: expected " . var_export($expected, true) . ', got ' . var_export($actual, true) . PHP_EOL);
        exit(1);
    }
    echo "Passed: {$label}\n";
}

function assert_throws(callable $fn, $label)
{
    global $assertions;
    $assertions++;
    try {
        $fn();
    } catch (Exception $e) {
        echo "Passed: {$label}: Caught expected exception: '" . $e->getMessage() . "'" . PHP_EOL;
        return;
    }
    fwrite(STDERR, "FAIL {$label}: expected an exception" . PHP_EOL);
    exit(1);
}

function run_mapping(array $mapping_metadata, array $mapping_rules, array $project, array $mapping_defaults = []): array
{
    $map = new Schema_transform_mapping_rules($mapping_metadata, $mapping_rules, $mapping_defaults);
    $resolver = new Project_export_mapping_resolver($map, new Project_export_transformer_manager(), new Nested_path_helper());
    return $resolver->apply_export_mappings($project);
}
