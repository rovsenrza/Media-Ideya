# Media-Ideya — Codex workflow

These instructions apply to every task in this repository.

## Communication

- Keep updates and final handoff short: state the result and relevant paths.
- Do the work before explaining it; provide detail only when requested.

## Project boundaries

- CMS: DataLife Engine 20.0.
- Active theme: `templates/MediaIdeya/`.
- Make theme work in `templates/MediaIdeya/`; do not modify `engine/` core unless the task explicitly requires it.
- Preserve DLE tags such as `{headers}`, `{content}`, `{AJAX}`, `{THEME}` and use `{include file="modules/<name>.tpl"}` for template modules.
- In DLE static-page conditions, separate multiple names with commas (`[not-static=foo,bar]`); availability conditions use pipes.
- Never commit runtime secrets or local configuration, including `engine/data/` and `.env` files.
- Inner-page editorial content is installed by `scripts/mi-p4-pages-content.php`: dry-run first, then `--apply` only when the task authorizes local DB changes.
- P0/P1 are legacy bootstrap scripts. Never rerun them after P4; P0 deletes categories above id 2 and both replace the xfield registry.

## Design source and Figma

- Active Figma file key: `xwP9bW8ocVlkXfKa0HwLVf`. Treat older file keys as legacy references only.
- Design breakpoint: 1920px. Full-bleed backgrounds must remain full-bleed above that width; `--mi-page-width` is for inner content only.
- For UI work, check `.figma-cache/INDEX.md` and the referenced cache file before calling Figma. Use the cache when it covers the target node.
- When a cache entry is missing or stale, query Figma (`get_design_context`, and `get_metadata` only when needed), store a durable summary in `.figma-cache/<nodeId-with-dash>_<slug>.md`, and update `.figma-cache/INDEX.md`.
- Copy Figma measurements and visual tokens exactly; do not invent approximate values. Download expiring Figma assets promptly into `templates/MediaIdeya/images/` and commit the local assets.
- Read `.figma-cache/INDEX.md` for the current page/node map; update it whenever the active design changes.

## Module architecture

Every UI unit is a separate module, never a monolith.

```
templates/MediaIdeya/
  modules/                 # markup modules
  css/base/                # reset, tokens, typography
  css/layout/              # shell, containers, grids
  css/components/          # reusable module styles
  css/pages/               # page-only styles
  js/components/           # reusable interactions
  js/pages/                # page-only scripts
  js/main.js               # shared initialisation
```

- A header, footer, card, button, or section title gets its own `modules/` file and matching component CSS (and JS when needed).
- `main.tpl` is the shell only: includes plus shared assets. Keep page CSS/JS conditional and avoid loading unused assets.
- Put shared values in `--mi-*` CSS variables. Put reusable styles in `css/components/`; page-specific styles only in `css/pages/`.

## DLE content ownership

- Categories 2, 4, 6 and 8 own Cases, Services, About singleton copy and Contacts respectively.
- Categories 10, 11 and 12 own About team roles, reviews and gratitude documents.
- Keep motion-bound slot counts and all structural HTML in templates. Expose only editorial text and content images through DLE fields.
- Merge additions into `engine/data/xfields.json`; never replace the registry or remove fields owned by another page.
- Repeatable page content is ordered by publication date ascending unless its template explicitly says otherwise.

## Assets and quality

- Prefer WebP where suitable, include intrinsic `width`/`height` or `aspect-ratio`, lazy-load below-the-fold media, and keep the hero/LCP asset eager with `fetchpriority="high"`.
- Use responsive `srcset`/`sizes` when appropriate. When the task explicitly requires original Figma images, keep the downloaded source intact and add optimized derivatives only as a separate delivery layer.

## Before handoff

- Inspect the relevant diff and status. Do not overwrite unrelated working-tree changes.
- Verify module separation, DLE tag integrity, and Figma fidelity for UI changes.
- Hosting is inactive. Keep implementation and verification local; deployment and production publishing stay disabled until the user explicitly re-enables them.
- Commit only when requested. A commit does not authorize push or deployment.
