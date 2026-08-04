# About motion (Jitter: о нас.mp4) — AOS, not scroll-scrub

- Source: `.figma-cache/jitter/o-nas.mp4` + `about-frames/`
- Trigger: `IntersectionObserver` → `.aos-animate` once section is ~20% in view
- Reveals are **time-based** (CSS transition-delay), matching video ms

## Delays (from intersect)

| Element | delay | duration |
|---------|-------|----------|
| Colonnade | 0ms | 600ms |
| «О нас» | 700ms | 700ms |
| MEDIA IDEYA | 1000ms | 800ms |
| Desc | 1400ms | 900ms |
| Stat 1/2/3 | 1800 / 2200 / 2600ms | 700ms |
| CTA + glow | 3200ms | 800ms |

## Files

- `modules/about.tpl` — `data-aos="about"`, normal section (no pin/track)
- `css/components/about.css` — AOS hidden → animate rules
- `js/pages/home.js` — observer → `aos-animate` (disconnect after play)
