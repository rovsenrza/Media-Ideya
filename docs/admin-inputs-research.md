# Admin input design research

Date: 2026-08-17

## Sources

- [W3C WCAG 2.2 — Focus Appearance](https://www.w3.org/WAI/WCAG22/Understanding/focus-appearance.html): a keyboard focus indicator needs sufficient size and a 3:1 contrast change; a solid 2px perimeter is the most direct implementation.
- [MDN — `:focus-visible`](https://developer.mozilla.org/en-US/docs/Web/CSS/Reference/Selectors/:focus-visible): use the pseudo-class to show the enhanced focus state for keyboard navigation without applying it to every pointer click.

## Applied decision

The DLE admin controls use a quiet white surface, 1px blue-grey border, 10px radius and a 48px minimum control height. Labels remain outside controls for persistent context. Focus is communicated through a 2px Media Ideya blue outline plus a restrained blue halo, while hover only slightly changes border and surface color. This keeps dense admin forms calm, clear and keyboard-accessible.
