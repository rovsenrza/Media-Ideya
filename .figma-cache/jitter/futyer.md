# Footer + onfooter motion (Jitter: футер.mp4)

- Source: `.figma-cache/jitter/futyer.mp4` + `footer-frames/` (61 @ 30fps, ~2.03s)
- Section: `mi-footer` — Figma 1:31 (форма 1:33 + подвал 1:40)

## Timeline (ms)

| ms | What |
|----|------|
| 0–80 | Dark; legal starts |
| 0–1100 | Hands enter to parked outside pos |
| 0–2000 | Watermark rises from below → `bottom: 0` (parallel) |
| 1100–2000 | Hands nudge inward by same park distance (no clip) |

## Implementation

- AOS on footer enter (`data-aos="footer"`) via `main.js` (global)
- Hands: `translateX(±120px)` → 0, ~1s
- Title: `.mi-footer__word` stagger
- Bar cols stagger fade-up
- `prefers-reduced-motion` → static composed state

## Files

- `modules/footer.tpl`
- `css/components/footer.css`
- `js/main.js`
