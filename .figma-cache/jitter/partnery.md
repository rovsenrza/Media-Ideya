# Partners / clients (Jitter: партнеры.mp4)

- Source: `.figma-cache/jitter/partnery.mp4` + `partners-frames/` (183 @ 30fps, ~6.1s)
- Section: `mi-clients` — Figma 1:77 + video eyebrow «Партнеры»

## Timeline (ms)

| ms | What |
|----|------|
| 0–350 | Empty dark |
| 400–1000 | Eyebrow → title → sub (AOS) |
| 1000–2000 | Logo rows blur→sharp stagger |
| 2000–3000 | Hold composed grid |
| 3000–5000 | Logos scroll up inside box; edge fades |
| 5000+ | Logos mostly past fade; title stays |

## Logic

1. **AOS** on section enter (`data-aos="clients"`) — timed reveal
2. **Sticky pin** + track — page scroll scrub `--mi-clients-p` translates `.mi-clients__logos` inside fixed `.mi-clients__box` (506px)
3. Top/bottom gradients mask edges (video fade)
4. Extra logo rows so overflow exists; travel height from measured overflow

## Files

- `modules/clients.tpl`
- `css/components/clients.css`
- `js/pages/home.js`
