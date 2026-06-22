# Docker Compose Development

This setup runs the Metadata Editor locally with PHP/Apache, a PHP queue worker,
MySQL, Mailpit, and the companion FastAPI data-processing service.

## Prerequisites

- Docker Compose v2.
- The FastAPI service (https://github.com/worldbank/metadata-editor-fastapi) checked out next to this repository at
  `../metadata-editor-fastapi`, or set `FASTAPI_CONTEXT` in `.env`.

## FastAPI Build Note

- The companion `metadata-editor-fastapi` repository currently does not include
  a top-level `Dockerfile`.
- This project provides an inline Dockerfile in `docker-compose.yml` for the
  `fastapi` service build.
- If you see `failed to read dockerfile: open Dockerfile: no such file or
  directory`, verify that `FASTAPI_CONTEXT` points to your cloned
  `metadata-editor-fastapi` path.

## Run

```sh
docker compose up --build
```

Open:

- App: http://localhost:8080
- Mailpit: http://localhost:8025
- FastAPI: http://localhost:8000
- MySQL from host: `localhost:3307`

On the first run, open http://localhost:8080/index.php/install and use the
installer to create the database tables and initial admin user.

## Viewing the database
The Metadata Editor docs [recommend using MySQL Workbench](https://worldbank.github.io/metadata-editor-docs/tech_installation_windows.html#download-mysql) for viewing the database. 

You can also use your preferred database viewing tool, such as [Datagrip](https://www.jetbrains.com/datagrip/).

1. Download MySQL Workbench onto your Windows host by using the [MySQL installer](https://dev.mysql.com/downloads/workbench/). 
2. Ensure you have selected 'MySQL Workbench' within the installer options (the installer defaults to just installing MySQL Server). You don't need MySQL Server itself, just MySQL Workbench
3. Open MySQL Workbench and click the + next to MySQL Connections. Use these settings:
```
      Connection Name: metadata-editor-local
      Connection Method: Standard (TCP/IP)
      Hostname: 127.0.0.1
      Port: 3307 
      Username: metadata_editor (or equivalent from .env file)
      Click Store in Vault and enter password:
      metadata_editor (or equivalent from .env file)
```
4. Click Test Connection, then OK.
5. Select `metadata-editor-local` under your connections
6. In the left-hand pane, select the `Schemas` pane. You should then be able to see the metadata_editor database tables.

#### View the data for a particular metadata record
Note that the data for most types of records is within the editor_projects.metadata column. The data has been encoded and PHP serialised (see application/models/Editor_model.php, encode_metadata()). 

To view this data within the database (it's still serialized, but is more human-readable):
```sql
SELECT id,
CONVERT(FROM_BASE64(metadata) USING utf8mb4) AS metadata_serialized
FROM editor_projects
WHERE id = "add ID number here - refer to editor_projects for your record's id number."
```

## Notes

- PHP and FastAPI both mount `app-datafiles` at `/var/www/html/datafiles`.
  Keep that shared absolute path aligned because PHP sends `realpath(...)`
  file paths to FastAPI.
- Local database state is kept in the `db-data` volume. To reset everything:

```sh
docker compose down -v
```
