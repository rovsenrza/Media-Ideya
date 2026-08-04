# About scroll motion (Jitter: о нас.mp4)

- Source: `/Users/User/Downloads/о нас.mp4` → `.figma-cache/jitter/o-nas.mp4`
- Frames: `.figma-cache/jitter/about-frames/` (~241 @ 30fps, ~8.03s)
- Section: `mi-about` — pin/track sticky scrub (native scroll only)

## Timeline (ms → behaviour)

| ms | cover% | What |
|----|--------|------|
| 0–600 | ~0 | Colonnade + clouds; no text |
| 800–1400 | ~0 | «О нас» + MEDIA IDEYA reveal |
| 1600–3200 | ~0 | Desc + stats stagger + CTA |
| 3200–5200 | ~0 | Hold full composition |
| 5200–5800 | ~0 | Content fades out |
| 5900–7400 | 5→100 | Dark curved cover rises |
| 7400–8030 | 100 | Solid #112331 → clients |

## Implementation

- `modules/about.tpl` — track / pin / eyebrow / cover
- `css/components/about.css` — `--mi-about-*` vars
- `js/pages/home.js` — progress scrub mapped to video phases
- Exit: `.mi-about__cover` height from 229px wave tip → 100% pin
- `prefers-reduced-motion` / `.is-static` → full static frame
