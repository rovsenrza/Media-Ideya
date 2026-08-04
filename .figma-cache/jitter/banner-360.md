# Jitter — Banner animation
- source: `/Users/User/Downloads/Рекламное агентство 360.mp4`
- cached: `.figma-cache/jitter/banner-360.mp4`
- duration: ~7.97s @ 30fps, 1280×720

## Timeline (from frames)
- 0–2s: assembled hero (columns + statue + sky)
- ~1–3s: title lines reveal / stagger (“Рекламное…” → full 3 lines)
- ~3–5s: hold / idle
- ~5–7s: columns pull outward + fade; statue softens
- ~7–8s: clear to solid dark (next-section handoff)

## Implementation
- Entrance: columns from sides, statue up+fade, title lines stagger, scroll fade-in
- Idle: scroll bob, cloud drift
- Scroll-exit: `--mi-hero-scroll` drives columns out, fades title/statue/sky
- `prefers-reduced-motion`: static final state
