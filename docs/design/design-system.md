# Design system

## Direction

An original editorial engineering portfolio: deep forest-charcoal canvas, muted green surfaces, mint accent, high-contrast typography, understated rule lines, and generous whitespace. A light theme inverts the surface/text roles while retaining a darkened green accent. This avoids copying reference site branding/compositions.

## Tokens and type

CSS custom properties in `resources/css/app.css` define background, surfaces, text, muted text, line, accent, serif display face, sans body face, and monospace labels. System font stacks avoid an external font dependency and layout shifts. Fluid headings use `clamp`; content width is capped and mobile gutters shrink to 1rem.

## Layout and interaction

Shared header/footer, small uppercase mono section labels, oversized editorial headings, restrained project cards, and structured case studies create hierarchy. Theme control is keyboard-operable and persists a preference in local storage. Transitions are subtle and disabled for reduced motion. Responsive breakpoints adapt cards and navigation for narrow screens.

No Lighthouse scores are claimed; measure after production build and browser audit.

Performance acceptance targets: Lighthouse Performance ≥90, Accessibility ≥95, Best Practices ≥95, and SEO ≥95. These are goals only; record actual device, browser, build, and score before making a compliance claim.
