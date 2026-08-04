# Banner scroll motion (Jitter: Рекламное агентство 360.mp4)

- Source: `.figma-cache/jitter/banner-360.mp4` + `frames/` (239 @ 30fps, ~7.97s)
- Sticky pin; native scroll only (`--mi-hero-p`, `--mi-line-t`)

## Timeline (measured)

| ms | What |
|----|------|
| 0–1100 | Art only (statue + columns + clouds) |
| 1200–2800 | Title word reveal (timed on load) |
| 2900–3400 | Title crop exit (scroll, 3 lines stagger) |
| 3400–4800 | Mild column drift + statue rise |
| 4800–5800 | Bottom dark cover → services |
| 5800+ | Solid `#112331` |

## Scroll mapping

- Track height: `--mi-hero-scroll: 200vh`
- `p 0–0.35`: title lines crop (`LINE_STAGGER 0.08`, `LINE_SPAN 0.16`)
- `p 0–1`: columns ±120px / statue −320px / fade lifts
- Scroll cue fades with `p`

## Figma 1:30 section gaps

| From → To | ΔY |
|-----------|-----|
| hero → services | 0 |
| services → about | 0 |
| about → clients | 52 |
| clients → faq | 0 |
| faq → articles | 0 |
| articles → footer | 0 |
