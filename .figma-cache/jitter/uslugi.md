# Services sticky z-stack (Jitter: услуги.mp4)

- Section: `mi-services` / cards `mi-service-card`
- Native scroll only (no wheel hijack)
- Section title sticky under fixed header
- Cards `position: sticky` + rising `z-index` → next card covers previous (z-stack)
- Covered card: `--mi-stack-p` 0→1 → scale 1→0.7, opacity 1→0.7 (JS scrub)
- Gap 40px so next card peeks from bottom while previous is stuck
- Source video cached: `.figma-cache/jitter/uslugi.mp4` + `services-frames/`
