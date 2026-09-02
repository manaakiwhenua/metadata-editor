### Adding a New Export Type to the Metadata Editor

#### Before You Begin
If you plan to convert a pre-existing World Bank data type, such as HTML (`application > libraries Html_report.php`) or JSON (`application > libraries > Project_json_writer.php`) using a library, install your new library using the 'composer' command. For example, we installed [thephpleague's html-to-markdown library](https://github.com/thephpleague/html-to-markdown) with `composer require league/html-to-markdown`.

#### Adding the Export Type
1. Create a writer file for your export in `application > libraries`. You'll notice `Project_markdown_writer.php` in this libraries section. Model your writer file on that, including implementing the `IProject_export_writer` interface.

2. We're going to assume that the export you're adding is a simple one that downloads a file in your export format. Open the `api > Editor.php` file. You'll see the `project_export_get` endpoint, which contains `project_export_controller` and uses `project_export_writer_factory` to retrieve the correct writer class. The currently available export options for this endpoint are: `exclude_private_fields=1`: omit private metadata fields. This currently defaults to true. See the function `Editor->process_export_options()`.

3. Add your new writer to `project_export_writer_factory`'s `$writerMap`, which will allow it to be loaded by the endpoint.

4. If you want your export to be included in the overall zip folder download, you will need to add your new writer to `ProjectPackage`. Add a stage to `ProjectPackage` in `run_stage()` and add your case name to `get_export_stages()` and the file extension to `resolve_core_metadata_paths()`. Add to `create_info_json()` to add to the json info file.

5. Add a language key for the export in `project_lang.php`, near the 'Export Json' line. This is the key name you'll use for the export button display. Adding a key here allows it to be translated into other languages. A key name like 'download_[export type]' is fine.

6. Add an export button in `header.php`, following the format of the other buttons. This will cause the export to show in the project menu.

7. Add a click action for this export button. As we're doing a simple download, the URL looks like this `onLinkClick(base_url + '/api/editor/project_export/' + dataset_id +'/[export_type]'` and contains the query parameter. `Editor/project_export` corresponds to `Editor->project_export_get`. The export type will be the `format` parameter for the endpoint.