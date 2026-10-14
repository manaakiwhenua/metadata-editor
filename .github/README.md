# Metadata Editor — platform fork

This is our fork of [`worldbank/metadata-editor`](https://github.com/worldbank/metadata-editor), packaged to build and run on our Kubernetes platform.

For the **application's own** documentation, see the upstream [`README.md`](../README.md). This page covers how **we** work in the fork: which branch is live, and how to name branches so our build/deploy changes stay cleanly separable from upstream (and so fixes can still go back).

## Running it locally

To run the full stack (web + queue worker + MySQL + Mailpit + FastAPI) on your machine, see [`docker/README.md`](../docker/README.md).

## Which branch is live?

**`downstream`** — not `main`.

- **`main`** is a mirror of the World Bank's `main`. We never commit our own work here; it only moves by pulling in upstream updates.
- **`downstream`** is our version of Metadata Editor: `main` **plus** everything we add to run it ourselves (Dockerfile, CI, config). This is the branch CI builds and deploys.

Think of it as: **`downstream` is our `main`; `main` is a copy of World Bank's `main`.**

 Our `main` only ever changes by pulling from World Bank Metadata Editor `main` (upstream). Everything we create for our fork is branched off then merged into `downstream`.

## Syncing 
When World Bank has updates on their `main` branch, we need to bring those changes into our fork's `main` and then pull those changes into `downstream`. We want to keep our fork as up-to-date as possible to minimize merge conflicts.

1. Sync our fork's `main` with the upstream `main`, by using the 'Sync fork' button on GitHub.
2. Locally, update your `main` branch (a local copy of our fork's `main`).
3. Switch to your local `downstream` branch, make sure it is up to date, and merge your updated local `main` into your `downstream`.
4. Resolve any merge conflicts, then commit the merge to your local `downstream`. As `downstream` is protected, you can't push the merge commit directly. You'll need to open a pull request into `downstream` instead.
5. Branch off your local `downstream`, and name the branch something like 'syncing with World Bank [version]'. Note that there might not be a version number if you are bringing in minor fixes that haven't had an official release yet.
6. Open a pull request from this branch into `downstream`. If it's a small change, go ahead and merge. If it's a large change, request a review before merging.

## Naming your branch

Before you start a fix or feature, consider if **this is something the World Bank would want too.**
If it's only for our version of Metadata Editor, branch off `downstream`. If it's something the World Bank would want too, branch off `main`, and eventually open a pull request into `worldbank/metadata-editor main`. If your pull request is accepted, use the syncing process above to then pull those changes into our `main` and `downstream` branches.

Typically, branches off main will be for fixes that we know the World Bank would want. We haven't yet contributed a feature upstream, as we're not sure of their feature preferences. 

| Your change is… | Branch off | Name it | PR into |
|---|---|---|---|
| **A customisation just for our fork** (Docker, CI, config, our dev docs, custom features) | `downstream` | `build/<desc>` | `downstream` |
| **A fix/feature for the app itself** (upstreamable) | `main` | `fix/` `feat/` `docs/` `chore/<desc>` | `worldbank/metadata-editor main` (the upstream branch) |

The **base branch** is important, as that determines whether or not you'll be able to make a pr into World Bank's upstream main.

Use `build/` to indicate something for our build pipeline, or a stable branch that you're going to deploy to the test environment for demo purposes, even when it isn't literally a build file.

```bash
# Something just for us (the usual case):
git switch downstream && git pull
git switch -c build/<short-desc>
#   ...complete the work...  →  open a PR into downstream

# Something worth sending back upstream:
git switch main && git pull
git switch -c fix/<short-desc>        # or feat/ , docs/
#   ...complete the work...  →  open a PR into worldbank/metadata-editor main
```

## Viewing error logs

Metadata Editor stores its web error logs in the `logs/` directory at the root of the project. These are very helpful for troubleshooting, particularly when working on a new export. You can view the logs using:

```bash
# List all the logs
docker compose exec web ls logs

# View the most recent log file. Note that log dates are using UTC, so you need to switch log dates after lunch for New Zealand time. This can trip you up if you have the morning's logs open (yesterday's date, from our pov) and are expecting an error that is instead being recorded in the new, "today" log.

docker compose exec web tail -f logs/log-YYYY-MM-DD.php
# E.g. docker compose exec web tail -f logs/log-2026-10-11.php


```

## Running tests

World Bank Metadata Editor uses simple standalone test scripts (no testing framework), located in the `tests/` directory at the root of the project. You can run them using:

```bash
# Run a specific test script
php tests/Data_structure_csv_import_test.php

# Run all test scripts in the `tests/` directory
php tests/*.php
```


## Where to put fork-only files

Our additions should go in paths upstream doesn't have, so they never cause merge conflicts on re-sync:

- **CI** → `.github/` (this file lives here too)
- **Container build** → `Dockerfile`, `docker-compose.yml`, `docker/`, `.dockerignore` at the repo root
- **Kubernetes manifests** → the separate `k8s-apps-config` repo (not in this fork at all)

Name any fork-only folder by **function, not by us** (`ops/`, `deploy/`), never an org name. Avoid editing files upstream also owns (like the root `README.md`) where you can; that's why this doc is in `.github/` rather than appended to the shared README.

To see everything our fork adds: `git diff main downstream`.
