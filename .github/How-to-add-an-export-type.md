## Adding a New Export Type to the Metadata Editor

### Adding a Simple Export Type

This section explains how to add an export when all you need to do is convert from one data format to another using a library (e.g. HTML to Markdown).

#### Before You Begin
If you plan to convert a pre-existing World Bank data type, such as HTML (`application/libraries/Html_report.php`) or JSON (`application/libraries/Project_json_writer.php`) using a library, install your new library using the 'composer' command. For example, we installed [thephpleague's html-to-markdown library](https://github.com/thephpleague/html-to-markdown) with `composer require league/html-to-markdown`.

#### Adding the Export Type
1. Create a writer file for your export in `application/libraries/Custom_export_types`. You'll notice `Project_markdown_writer.php` in this libraries section. Model your writer file on that, including implementing the `IProject_export_writer` interface.

2. We're going to assume that you're adding a simple export that just downloads a file in your export format. Open the `application/controllers/api/Editor.php` file. You'll see the `project_export_get` endpoint, which contains `project_export_controller` and uses `project_export_writer_factory` to retrieve the correct writer class. The currently available export options for this endpoint are: `exclude_private_fields=1`: omit private metadata fields. This currently defaults to true. See the function `Editor->process_export_options()`.

3. Add your new writer to `project_export_writer_factory`'s `$writerMap`, which will allow it to be loaded by the endpoint.

4. If you want your export to be included in the overall zip folder download, you will need to add your new writer to `ProjectPackage`. Add a stage to `ProjectPackage` in `run_stage()` and add your case name to `get_export_stages()` and the file extension to `resolve_core_metadata_paths()`. Add to `create_info_json()` to add to the json info file.

5. Add a language key for the export in `project_lang.php`, near the 'Export Json' line. This is the key name you'll use for the export button display. Adding a key here allows it to be translated into other languages. A key name like 'download_[export type]' is fine.

6. Add an export button in `header.php`, following the format of the other buttons. This will cause the export to show in the project menu.

7. Add a click action for this export button. As we're doing a simple download, the URL looks like this `onLinkClick(base_url + '/api/editor/project_export/' + dataset_id +'/[export_type]'` and contains the query parameter. `Editor/project_export` corresponds to `Editor->project_export_get`. The export type will be the `format` parameter for the endpoint.

### Adding a Complex Export Type

Here, we'll cover the additonal steps required to add a more complex export, such as when you are converting from a particular schema to another schema (e.g. BSI Core to DataCite 4.7).

#### Before You Begin
You will need to create a yaml or json mapping file that defines which fields from the source schema map to which fields in the target schema. This mapping file will be used by your custom writer to transform the data. We have an example in `application/libraries/Custom_export_types/Datacite/BSI_Core_to_DataCite_4.7_map.yaml`. This is in yaml, but you can also use json if you prefer. Please note the structure of the example mapping file, as your new mapping file needs to follow the expected format. The mapping files are used by `Project_export_mapping_resolver` via the classes in `Mapping_classes`. There are validations in `Project_export_mapping_resolver` that will let you know regarding some issues with your mapping file.

Basic structure of a mapping file:

```yaml
profile:
  name: "BSI Core to DataCite 4.7"

defaults:
  default_value_id_1: "default_value"

mappings:
#    Basic field mapping example, with a transformer function
 - id: "mapping_rule_1"
   source: "source_field_1"
   target: "target_field_1"
   transformer: "transformer_function_1"

#    Nested field mapping example - when the fields of an object need to be mapped individually
 - id: "mapping_rule_2"
   source: "source_field_2"
   target: "target_field_2"
   field_mapping: 
     - source: "source_sub_field_1"
       target: "target_sub_field_1"
       transformer: "transformer_function_2"

#  Basic mapping example with default value
 - id: "mapping_rule_3"
   source: null 
   target: "target_field_3"
   default_value: "default_value_id_1"    

```

1. Please first review and apply the steps for adding a simple export type, but use `project_datacite_writer` as your reference point.
2. We're going to assume for this example that you will be using the Metadata Editor json as your starting point, and that you will be exporting to your custom schema, also in json. Implement the `get_project_json()` method in your custom writer to retrieve the project json (this export is provided by the World Bank Metadata Editor).
3. Implement a version of `project_datacite_writer`'s `map_to_datacite_json()` method in your custom writer (e.g. `map_to_custom_schema_json()`). If you have a yaml file for your mappings, load it using  `project_export_yaml_map_parser` (otherwise, load the json). Then, pass the mapping object to `schema_transform_mapping_rules`.
4. Try out your pathway by exporting a project using the button you added in the UI. You should receive an error regarding unknown transformers.
5. Implement any custom transformers you need by adding them to `application/libraries/Custom_export_types/Transformers` and registering them in the `Project_export_transformer_manager`.
6. Once your custom transformers are implemented and registered, try exporting a project again. The errors regarding unknown transformers should be resolved and you should receive a json download.
7. Adapt as needed for formats other than json.

