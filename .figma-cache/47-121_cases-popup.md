# Кейсы popup — nodes `47:121` and `47:422`

- Source: file `xwP9bW8ocVlkXfKa0HwLVf`.
- Viewport frame `47:121`: `1920×960`; popup at `x=100,y=122`, `1720×780`, `60` radius.
- Expanded content reference `47:422`: `1920×2075`; popup `1720×1861`.
- Popup grid: `80` padding, left media `680`, gap `60`, right text `820`. Left media remains fixed; only the right pane scrolls.
- Original cover, statue head, dots, wreaths, arrow and close assets live in `templates/MediaIdeya/images/pages/cases/`.
- Motion source: `.figma-cache/converting to jitter videos/popup.mp4`, `4.10s`, 30fps. Measured right-pane travel is normalized to `900px`: still through `0.10s`, strongest acceleration around `1.10–1.20s`, long ease-out ending `3.70s`, final hold to `4.10s`.
- Implementation uses the measured piecewise samples in `js/pages/cases-page.js`, scaled to the responsive scroll range.
