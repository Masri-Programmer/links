# LittleLink Links Page Agent Guidelines

This directory contains the code for the links landing page (`links.masri-programmer.de`) of Mohamad Masri (Masri Programmer), built on the LittleLink template.

## Architectural Context

- **Tech Stack**: Vanilla HTML5, CSS3, and JavaScript. No build tools, bundlers, or heavy frameworks (e.g. React, Tailwind) are used.
- **Dynamic Content**: Page content (company metadata, contact info, bank details, sections, links, and buttons) is loaded dynamically at runtime from `private.json`.
- **Styling**: Structured via Vanilla CSS across:
  - `css/reset.css` — Standard CSS reset.
  - `css/style.css` — Core layouts, themes, animations, counters, and structure.
  - `css/brands.css` — Button branded styling and button background/hover rules.
- **Performance**: Maintaining a perfect 100/100 Google PageSpeed Insights score is a priority. Avoid adding any dependencies or external scripts that introduce performance bloat.

## Development Rules & Conventions

When modifying the project:
1. **Adding/Modifying Links**:
   - **Do NOT** hardcode links directly into `index.html`.
   - **Do** edit `private.json` to update links, buttons, order, active state, icons, or contact info.
   - Example `private.json` button:
     ```json
     {
       "name": "GitHub",
       "link": "https://github.com/Masri-Programmer",
       "class": "button-github",
       "logo": "images/icons/github.svg",
       "aria_label": "Follow me on GitHub",
       "active": true
     }
     ```
2. **Buttons and Brand Styles**:
   - When adding a new brand button, check if its class exists in `css/brands.css`. If it does not, add the brand CSS styling rules (background color, hover state, accessibility border outlines) to `css/brands.css`.
   - Put brand SVG icons under `images/icons/` and reference them in `private.json`.
3. **Accessibility**:
   - Ensure every button has a descriptive `aria_label` property in `private.json`.
   - Maintain the skip link (`Skip to links`) at the top of the body.
   - For custom brand buttons, ensure contrast complies with standards or add helper outlines/borders for light/dark theme compatibility.
4. **HTML Modifications**:
   - Avoid modifying `index.html` unless adding new global structural blocks (e.g., footers, rating widgets, tracking pixels, or dynamic Javascript visual effects).
