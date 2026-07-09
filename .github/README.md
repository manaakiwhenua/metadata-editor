# Metadata Editor — platform fork

This is our fork of [`worldbank/metadata-editor`](https://github.com/worldbank/metadata-editor), packaged to build and run on our Kubernetes platform.

For the **application's own** documentation, see the upstream [`README.md`](../README.md). This page covers how **we** work in the fork: which branch is live, and how to name branches so our build/deploy changes stay cleanly separable from upstream (and so genuine fixes can still go back).

## Running it locally

To run the full stack (web + queue worker + MySQL + Mailpit + FastAPI) on your machine, see [`docker/README.md`](../docker/README.md).

## Which branch is live?

**`deploy`** — not `main`.

- **`main`** is a mirror of the World Bank's `main`. We never commit our own work here; it only moves by pulling in upstream updates.
- **`deploy`** is our version: `main` **plus** everything we add to run it ourselves (Dockerfile, CI, config). This is the branch CI builds and deploys.

Think of it as: **`deploy` is our `main`; `main` is theirs.**

## Naming your branch

Before you start, ask one question: **would the World Bank want this change too?**

| Your change is… | Branch off | Name it | PR into |
|---|---|---|---|
| **Just to run it here** (Docker, CI, config, our dev docs) | `deploy` | `build/<desc>` | `deploy` |
| **A real fix/feature for the app itself** (upstreamable) | `main` | `fix/` `feat/` `docs/` `chore/<desc>` | `main`, then offer upstream |

The **base branch** is what matters — the prefix just labels the choice. `build/` means "ours," even when it isn't literally a build file.

```bash
# Something just for us (the usual case):
git switch deploy && git pull
git switch -c build/<short-desc>
#   ...work...  →  open a PR into deploy

# Something worth sending back upstream:
git switch main && git pull
git switch -c fix/<short-desc>        # or feat/ , docs/
#   ...work...  →  open a PR into main
```

**The rule in one line:** `main` only ever moves by pulling from upstream; everything we write lands on `deploy`.

## Where fork-only files live

Our additions go in paths upstream doesn't have, so they never cause merge conflicts on re-sync:

- **CI** → `.github/` (this file lives here too)
- **Container build** → `Dockerfile`, `docker-compose.yml`, `docker/`, `.dockerignore` at the repo root
- **Kubernetes manifests** → the separate `k8s-apps-config` repo (not in this fork at all)

Name any fork-only folder by **function, not by us** (`ops/`, `deploy/`), never an org name — that keeps it durable. Avoid editing files upstream also owns (like the root `README.md`) where you can; that's why this doc is in `.github/` rather than appended to the shared README.

To see everything the fork adds: `git diff main deploy`.
