/**
 * Gold Card Engine v2 — Theme Generator Utility
 *
 * Generates a complete metallic palette (8 stops) from a single base color,
 * making it trivial to create new card variants.
 *
 * Usage:
 *   // Generate palette from a hex color
 *   const palette = GoldCardTheme.generate('#D4AF37');
 *
 *   // Apply to a card element
 *   GoldCardTheme.apply(cardElement, palette);
 *
 *   // Or create a theme class
 *   GoldCardTheme.register('bronze', '#CD7F32');
 *
 * Algorithm:
 *   Given baseColor (the --metal-midtone), the 8 stops are computed as:
 *   highlight  → blend(baseColor, white, 0.85)     ≈ near-white with hue
 *   bright     → blend(baseColor, white, 0.60)     ≈ bright metallic
 *   light      → blend(baseColor, white, 0.30)     ≈ light metallic
 *   midtone    → baseColor                          ≈ primary identity
 *   dark       → blend(baseColor, black, 0.25)     ≈ darker
 *   shadow     → blend(baseColor, black, 0.50)     ≈ shadow
 *   deep-shadow → blend(baseColor, black, 0.72)   ≈ deep shadow
 *   abyss      → blend(baseColor, black, 0.88)     ≈ near-black with hue
 */

const GoldCardTheme = (() => {
  'use strict';

  /* ── Color Utilities ────────────────────────────────────── */

  /**
   * Parse a hex color string into {r, g, b} (0-255).
   */
  function hexToRgb(hex) {
    hex = hex.replace(/^#/, '');
    if (hex.length === 3) hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
    const n = parseInt(hex, 16);
    return { r: (n >> 16) & 255, g: (n >> 8) & 255, b: n & 255 };
  }

  /**
   * Convert {r, g, b} back to a hex string.
   */
  function rgbToHex({ r, g, b }) {
    return '#' + [r, g, b].map(v => Math.round(v).toString(16).padStart(2, '0')).join('');
  }

  /**
   * Linearly blend two colors by a factor (0 = color1, 1 = color2).
   * Both inputs can be hex strings or {r,g,b}.
   */
  function blend(c1, c2, factor) {
    const a = typeof c1 === 'string' ? hexToRgb(c1) : c1;
    const b = typeof c2 === 'string' ? hexToRgb(c2) : c2;
    return rgbToHex({
      r: a.r + (b.r - a.r) * factor,
      g: a.g + (b.g - a.g) * factor,
      b: a.b + (b.b - a.b) * factor,
    });
  }

  /**
   * Convert hex color to RGB string "R, G, B" for rgba() usage.
   */
  function hexToRgbStr(hex) {
    const { r, g, b } = hexToRgb(hex);
    return `${Math.round(r)}, ${Math.round(g)}, ${Math.round(b)}`;
  }

  /* ── Palette Generator ───────────────────────────────────── */

  /**
   * Generate a complete metallic palette from a single base color.
   *
   * @param {string} baseHex  The primary metallic color (midtone), e.g. '#D4AF37'
   * @param {object} opts     Optional overrides:
   *   - bgPrimary:   background primary color (hex)
   *   - bgSecondary: background secondary color (hex)
   *   - accent:      accent/bright color (hex)
   *   - glowColor:   glow color as rgba string
   * @returns {object} Complete theme object with all CSS variable values
   */
  function generate(baseHex, opts = {}) {
    const base = hexToRgb(baseHex);

    const palette = {
      '--metal-highlight':     blend(baseHex, '#FFFFFF', 0.85),
      '--metal-bright':        blend(baseHex, '#FFFFFF', 0.60),
      '--metal-light':         blend(baseHex, '#FFFFFF', 0.30),
      '--metal-midtone':       baseHex,
      '--metal-dark':          blend(baseHex, '#000000', 0.25),
      '--metal-shadow':        blend(baseHex, '#000000', 0.50),
      '--metal-deep-shadow':   blend(baseHex, '#000000', 0.72),
      '--metal-abyss':         blend(baseHex, '#000000', 0.88),

      '--primary-color':  baseHex,
      '--secondary-color': blend(baseHex, '#000000', 0.50),
      '--accent-color':   opts.accent || blend(baseHex, '#FFFFFF', 0.60),

      // Background defaults — dark complement of the metallic hue
      '--bg-primary':   opts.bgPrimary   || blend(baseHex, '#0a0a1a', 0.20),
      '--bg-secondary': opts.bgSecondary || blend(baseHex, '#050510', 0.15),
      '--bg-primary-rgb':   hexToRgbStr(opts.bgPrimary   || blend(baseHex, '#0a0a1a', 0.20)),
      '--bg-secondary-rgb': hexToRgbStr(opts.bgSecondary || blend(baseHex, '#050510', 0.15)),
      '--bg-cream':     opts.bgCream     || '#f8f2df',
      '--bg-separator': opts.bgSeparator || blend(baseHex, '#FFFFFF', 0.40),

      // Glow & shadow
      '--glow-color':         opts.glowColor || `rgba(${hexToRgbStr(blend(baseHex, '#FFFFFF', 0.70))}, 0.55)`,
      '--glow-intensity':     opts.glowIntensity || '0.50',
      '--glow-x':             opts.glowX || '50%',
      '--glow-y':             opts.glowY || '28%',
      '--shine-opacity':      opts.shineOpacity || '0.42',
      '--frame-shadow-color': `rgba(${hexToRgbStr(blend(baseHex, '#000000', 0.70))}, 0.28)`,
      '--frame-glow-color':   `rgba(${hexToRgbStr(baseHex)}, 0.18)`,
      '--card-drop-shadow-1': `drop-shadow(0 26px 55px rgba(${hexToRgbStr(blend(baseHex, '#000000', 0.70))}, 0.18))`,
      '--card-drop-shadow-2': `drop-shadow(0 10px 24px rgba(${hexToRgbStr(blend(baseHex, '#000000', 0.70))}, 0.10))`,
    };

    return palette;
  }

  /* ── Apply to DOM ────────────────────────────────────────── */

  /**
   * Apply a generated palette to a card element via inline CSS variables.
   *
   * @param {HTMLElement} cardEl  The .gold-card element
   * @param {object}      palette  Object from generate()
   */
  function apply(cardEl, palette) {
    for (const [prop, value] of Object.entries(palette)) {
      cardEl.style.setProperty(prop, value);
    }
  }

  /**
   * Remove all inline custom properties from a card element,
   * reverting to its class-defined theme.
   */
  function reset(cardEl) {
    const props = Object.keys(generate('#000000'));
    for (const prop of props) {
      cardEl.style.removeProperty(prop);
    }
  }

  /* ── Register Theme Class ────────────────────────────────── */

  /**
   * Dynamically inject a new theme variant CSS class.
   * Creates a <style> rule for .gold-card--{name} with the generated palette.
   *
   * @param {string} name     Theme name (e.g. 'bronze')
   * @param {string} baseHex  Primary metallic color
   * @param {object} opts     Optional overrides (same as generate())
   */
  function register(name, baseHex, opts = {}) {
    const palette = generate(baseHex, opts);
    const rules = Object.entries(palette)
      .map(([prop, val]) => `  ${prop}: ${val};`)
      .join('\n');

    const css = `.gold-card--${name} {\n${rules}\n}`;

    // Inject into document
    const styleId = `gold-card-theme--${name}`;
    if (!document.getElementById(styleId)) {
      const styleEl = document.createElement('style');
      styleEl.id = styleId;
      styleEl.textContent = css;
      document.head.appendChild(styleEl);
    } else {
      document.getElementById(styleId).textContent = css;
    }

    return { name, palette, css };
  }

  /* ── Preset Themes ───────────────────────────────────────── */

  const PRESETS = {
    gold:    '#D4AF37',
    silver:  '#C0C0C0',
    prisma:  '#40C4AA',
    ember:   '#D44830',
    bronze:  '#CD7F32',
    platinum: '#E5E4E2',
    amethyst: '#9966CC',
    ruby:    '#9B111E',
    sapphire: '#0F52BA',
    obsidian: '#3C3C3C',
  };

  /**
   * Register all preset themes at once.
   */
  function registerAllPresets() {
    for (const [name, baseHex] of Object.entries(PRESETS)) {
      register(name, baseHex);
    }
  }

  /* ── Public API ──────────────────────────────────────────── */

  return {
    generate,
    apply,
    reset,
    register,
    registerAllPresets,
    hexToRgb,
    rgbToHex,
    blend,
    hexToRgbStr,
    PRESETS,
  };
})();
