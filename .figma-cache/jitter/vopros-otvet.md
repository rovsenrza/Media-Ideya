# FAQ motion (Jitter: вопрос ответ.mp4)

- Source: `.figma-cache/jitter/vopros-otvet.mp4` + `faq-frames/` (95 @ 30fps, ~3.17s)
- Section: `mi-faq` — Figma 1:97

## Timeline (ms)

| ms | What |
|----|------|
| 0–250 | Dark + radial glow only |
| 300–1500 | Heading + label letters in **parallel** (~75ms/char) |
| 1500–2000 | Title hold |
| 2000–3100 | FAQ items fade + slide L→R, staggered top→bottom |

## Implementation

1. AOS on section enter → `.aos-animate`
2. JS splits `.mi-faq__heading` + `.mi-faq__label` into `.mi-faq__char` with shared `--mi-char-i`
3. Items: `translateX(-48px)` + opacity, delays from 1550ms + 140ms step
4. Glow: Ellipse 9 radial behind content

## Files

- `modules/faq.tpl`
- `css/components/faq.css`
- `js/pages/home.js`
