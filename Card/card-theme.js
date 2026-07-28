/**
 * FIFA 18 Card Theme Engine — card-theme.js
 *
 * setCardTheme({color1, color2}) only modifies CSS custom properties
 * on the card element. Never modifies SVGs or DOM structure.
 *
 * Usage:
 *   setCardTheme({ color1: '#C62828', color2: '#FF6F00' });  // Fuego
 *   setCardTheme({ color1: '#1565C0', color2: '#26C6DA' });  // Agua
 */

function hexToRgb(hex) {
  hex = hex.replace('#', '');
  if (hex.length === 3) {
    hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
  }
  var r = parseInt(hex.substring(0, 2), 16);
  var g = parseInt(hex.substring(2, 4), 16);
  var b = parseInt(hex.substring(4, 6), 16);
  return r + ', ' + g + ', ' + b;
}

function setCardTheme(theme, cardEl) {
  if (!theme) return;
  cardEl = cardEl || document.getElementById('playerCard');
  if (!cardEl) return;

  if (theme.color1) {
    cardEl.style.setProperty('--color1', theme.color1);
    cardEl.style.setProperty('--color1-rgb', hexToRgb(theme.color1));
  }
  if (theme.color2) {
    cardEl.style.setProperty('--color2', theme.color2);
    cardEl.style.setProperty('--color2-rgb', hexToRgb(theme.color2));
  }
}

var defaultCardTheme = {
  color1: '#243b73',
  color2: '#1a2b56'
};

window.setCardTheme = setCardTheme;
window.defaultCardTheme = defaultCardTheme;
window.hexToRgb = hexToRgb;
