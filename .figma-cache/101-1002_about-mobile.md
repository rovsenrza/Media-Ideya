# About page — mobile 360px

Source: `26q72HBLqm2BrQP1NnVPqy`, node `101:1002`.

- The mobile page is a fixed 360px composition with a 50px header and 48px breadcrumb row.
- Hero: 800px; title at y149 (32px), cloud/statue composition follows the supplied source frames.
- Team: y800, 671px; 16px horizontal padding, 28px heading, 14px body, horizontally sliding 252px team card.
- Process: y1471, 800px; 28px heading and six centered 37px steps stacked at 57px intervals over the bowl composition.
- Reviews: y2271, 707px; 28px heading and horizontally scrollable 308×442px cards.
- Gratitude: y2978, 650px; 28px heading, 360×435px shelf image, and two-column 114×49px scrolls.
- CTA/form: y3628, 567px; hand artwork at x−120/y40 (600×163), copy starts y233, and 176×53px action button.
- Animations remain driven by the existing about-page script; mobile rules only adapt geometry and typography.

Additional asset references: `101:1015` (gratitude shelves), `101:1040` (process bowl), and `101:1008` (CTA right hand) are 100%-sized, bottom-aligned image layers in Figma. The repository source assets are larger transparent exports, so the gratitude artwork is cover-cropped from the bottom while the bowl remains above the process foreground wash at full opacity. CTA hand layers preserve their source aspect ratio with `object-fit: cover` rather than being stretched.

Hero `101:1116` is a 407.885×417 crop of the original vertical center-statue source. Preserve its aspect ratio and allow its surrounding frame to crop it; never force both image dimensions to 100%, which visibly squashes the statue.
