# Кейсы popup CTA — nodes `47:710`–`47:715`

- Source: user-provided file `26q72HBLqm2BrQP1NnVPqy`.
- Node `47:710`: dark CTA card is `1560×395`, `30px` radius and `40px` padding. Its background is `#112331` with a blue radial highlight centered at roughly `88% 80%`.
- Copy stack (`47:711`–`47:713`): `525px` wide; title is `40px/1.2` medium, body is `20px/1.5` light. CTA button (`47:714`) uses `#4385BB`.
- Statue (`47:715`): `201×395`, positioned `127px` from the CTA's right edge. Figma applies a horizontal mirror (`rotate(180deg) scaleY(-1)`), so the face enters from the right edge and looks left. Reuse the existing original `images/pages/cases/head.png` asset with `object-position: 58% center` and `scaleX(-1)`; its far-right source area is transparent. Exported MCP raster returned an empty placeholder.
- Modal frame reference: fixed `1720px` desktop composition — `80px` padding, `680px` media column, `60px` gap, `820px` content column. Do not stretch the dialog to the viewport beyond that composition.
