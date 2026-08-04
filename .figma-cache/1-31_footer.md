# Figma cache — Footer (форма + подвал)
- fileKey: kjgO8zApNk7weuAFgIdCPb
- nodeId: 1:31
- fetched: 2026-08-04
- url: https://www.figma.com/design/kjgO8zApNk7weuAFgIdCPb?node-id=1-31

## Specs
- BG: gradient #0d2f4c → #112331 @ 45.269%
- Brand watermark: MEDIA IDEYA, 325.262px SemiBold, #0d304c, opacity 0.5, uppercase

### форма (1:33) — onfooter CTA
- py 120, gap 40, rounded-tl/tr 60px
- Title: 96 Medium white, center
- Sub: 20 Light white, gap 24 from title
- Button: #4385bb, px 48 py 24, radius 100, 20 Medium
- Hand R: 710×768, left 1431, top -142; object-bottom; mix-blend screen; max-width none
- Hand L: 630×768, left -143, top -142; object-bottom; mix-blend screen; max-width none
- Brand watermark 1:32: bottom 254 + translateY(100%), 325.262px, #0d304c opacity 0.5
- Bar: px 100 py 40 gap 100; inner 1720 (no double gutter from shell)
- reset img height must stay `auto` (height:100% breaks hands)

### подвал (1:40)
- border-top: rgba(67,133,187,0.5)
- px 100, py 40, gap 100 (contacts ↔ legal)
- Contacts gap 120; labels 16 Regular opacity 0.5; values 20 Medium
- Address 2 lines
- Email underlined
- Social: 3 circles 64px, gap ~16, label right-aligned
- Legal: 16 Regular opacity 0.5, space-between

## Assets (local)
- templates/MediaIdeya/images/footer/*
