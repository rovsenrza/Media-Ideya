# Homepage CTA — intro frame

- fileKey: `26q72HBLqm2BrQP1NnVPqy`
- nodeId: `84:609`
- fetched: `2026-08-30`

## Specs

- Canvas: `1920 × 960`, background `#112331`.
- Right figure layer: x `945`, y `-319`, `2082 × 1259`.
- Left figure layer: x `-499`, y `118`, `1521 × 822`.
- Both source images fill these layer bounds (`object-fit: fill`); applying `contain` separates the fingertips and is incorrect.
- The Figma export pins both fingertips to `y=475px`; no horizontal figure drift is permitted before the orb transition.
- Approved placement adjustment: left source layer `translateX(-5%)`, right source layer `translateX(5%)`.
- Used only by the homepage CTA transition.

## Motion reference

- Local, non-versioned reference: `.feedback/анкета 2.mp4`.
- Sampled frames: dark statues through ~2s, centre light orb at ~2.5s, full transition at ~3s, final CTA at ~4s.

## Assets

- `templates/MediaIdeya/images/home-cta/figure-left.png`
- `templates/MediaIdeya/images/home-cta/figure-right.png`
