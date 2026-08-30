# Homepage CTA — intro frame

- fileKey: `26q72HBLqm2BrQP1NnVPqy`
- nodeId: `84:609`
- fetched: `2026-08-30`

## Specs

- Canvas: `1920 × 960`, background `#112331`.
- Right figure layer: x `945`, y `-319`, `2082 × 1259`.
- Left figure layer: x `-499`, y `118`, `1521 × 822`.
- The source images retain their native aspect ratio; stretching the original rasters separates the fingertips vertically.
- The native-proportion layers are calibrated to the Figma export fingertip axis (`y=475px`).
- Approved placement adjustment: left source layer `translateX(-5%)`, right source layer `translateX(5%)`.
- Intro motion: both layers converge to their Figma bounds over `1850ms` before the orb transition.
- Final contact alignment (nodes `84:612` / `84:611`) is in the static layer positions: left top `101px`, right top `-395px` on the 1920px canvas. Motion is X-axis only.
- Used only by the homepage CTA transition.

## Motion reference

- Local, non-versioned reference: `.feedback/анкета 2.mp4`.
- Sampled frames: dark statues through ~2s, centre light orb at ~2.5s, full transition at ~3s, final CTA at ~4s.

## Assets

- `templates/MediaIdeya/images/home-cta/figure-left.png`
- `templates/MediaIdeya/images/home-cta/figure-right.png`
