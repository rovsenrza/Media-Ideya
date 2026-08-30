# Главная моб — Figma mobile reference

- Source of truth (user-confirmed): `26q72HBLqm2BrQP1NnVPqy`, node `101:519`.
- Fetched from Figma MCP: `2026-08-30`.
- Canvas: `360 × 5554`.
- This file supplies the mobile reference. Existing desktop references in `xwP9bW8ocVlkXfKa0HwLVf` remain the desktop source and animation implementation source.
- Use only the raw source assets surfaced by Figma MCP (`rawImages` and exported SVGs); never render an exported Figma preview as a site asset.

## Shared mobile tokens

- Content gutter: `16px`; content width: `328px`.
- Main heading: `32px`, Roboto Flex Medium, line-height `1.1`.
- Body: `14px`, Roboto Flex Light, line-height `1.5`.
- Mobile button: `14px`, `16px 32px` padding, `53px` high, `100px` radius.
- Page background: `#112331`; light card: `#edf5fd`; blue action: `#4385bb`.

## Main-page sequence

1. Header: `360px` wide with `16px × 12px` padding; 38.64 × 26 logo; request pill and 26px menu icon.
2. Hero: `360 × 800`. Sky gradient `#8db5d5 → #6398c3`; clouds 30% opacity; statue 391 × 481 at x center / y 278; columns 303 × 787 at x -225 and 282 / y -72; heading 284px wide at x 38 / y 96; 64px scroll control at x 148 / y 579.
   - Motion: reuse the desktop title-crop, column-drift and statue-rise timeline over the mobile track's exact `800px` interval; initial frame must remain the coordinates above.
   - Responsive implementation: every Figma coordinate is proportionally scaled from the 360px design between `320px` and `430px`; the scroll-scrub runs over that single stage and does not add a second empty viewport.
   - Nodes `101:525` and `101:524` use the same raw column image. Their Figma layer exports reveal only the inner `78px` canvas edges, so the implementation crops the raw image horizontally inside each `302.77px` layer before the hero-frame clip.
3. Services: Figma node `101:539`, `360 × 800`; 20px top/60px bottom padding, 32px title, 20px gap; cards `328 × 665`, 24px radius. The service media is 232.432 × 200; title/body starts at y224, counter is 24px. All DLE cards remain in the desktop-style sticky stack; the first card is the initial Figma viewport state.
   - Nodes `101:542`, `101:548`, `101:550`, `101:554`, `101:555`, `101:556`: card uses #EDF5FD, 24px radius; media `232.432 × 200` top-right; title 28px / 1.1, count 24px / 1.2, body 14px Light / 1.5, and the standard 14px mobile action button.
   - The mobile stack uses the responsive Figma stage height as both sticky-pin height and per-card scroll interval; it must not fall back to a viewport-height calculation.
   - Nodes `101:542` / `101:549` (refetched 2026-08-30): every service uses the same mobile layout: light card, `10px` outer image/content gap; image frame `232.432 × 200`, `16.216px` radius at top right; content frame below it is full width with `24px` padding and `16px` gap. Header is a title/count row (`28px / 1.1`, `24px / 1.2`); body has `10px` gap; CTA uses the shared mobile button. The DLE image source for each card is its desktop composition and must retain its native `cover/right` crop inside this common mobile image frame.
4. About (`101:566`): `360 × 800`; 70px top / 270px bottom / 16px side padding; centered text group (32px title, 14px body, 16px gap), then stats at a 24px gap. Cloud source positions: 747×304 at (-330,160) and (38,153); 1050×427 at (-303,323). Figma `101:568` adds a 360×371 `#CEE0F0 → #D8EAFA` sky layer at y372. Colonnade is 726×513 at x -183.5 / y419; wave begins y690, 412×110. Stats use 32px numbers, 32×60 wreaths, 4px label gap, and `16px 35px` wrap gap.
5. Clients (`101:717`): `360 × 730`; heading starts y60 with 32px centered title and 14px subtitle; logo area begins y158. Logos are 122×56 / 32px radius and appear as a 2/1/2/1/2/1/2 staggered matrix (left/right at x16/222, centre at x119), y positions 158,226,294,362,429,496,564.
6. FAQ: 16px gutter, 20px gaps; cards use 20px padding/radius; question is 18px.
7. Articles: first horizontal card `308px` wide; image `308 × 185`, 24px radius.
8. CTA: `360 × 800`, centered copy and request button. Footer: 16px horizontal padding, 40px vertical padding.

## Asset

- Header hamburger exported from Figma node `101:532` and saved as `templates/MediaIdeya/images/icons/menu-deep.svg` (26 × 26).
- Hero source images are saved under `templates/MediaIdeya/images/hero/mobile-{cloud,column,statue}.png`.
- About source images from node `101:566` are saved as `templates/MediaIdeya/images/about/mobile-source/2.png` (cloud) and `3.png` (colonnade); these are original Figma fills, not node exports.
