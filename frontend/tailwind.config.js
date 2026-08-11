/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./components/**/*.{js,vue,ts}",
    "./layouts/**/*.vue",
    "./pages/**/*.vue",
    "./plugins/**/*.{js,ts}",
    "./app.vue",
    "./error.vue",
  ],
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        "secondary": "#4a6549",
        "surface-bright": "#f8f9fa",
        "on-primary": "#ffffff",
        "inverse-surface": "#2e3132",
        "inverse-primary": "#4ae176",
        "surface-variant": "#e1e3e4",
        "on-tertiary-fixed-variant": "#314d3e",
        "secondary-container": "#ccebc7",
        "on-error-container": "#93000a",
        "on-primary-container": "#004b1e",
        "primary-fixed": "#6bff8f",
        "on-primary-fixed": "#002109",
        "on-secondary-fixed-variant": "#334d33",
        "on-tertiary-fixed": "#042014",
        "primary": "#006e2f",
        "on-secondary": "#ffffff",
        "primary-container": "#22c55e",
        "tertiary-container": "#95b3a0",
        "surface-tint": "#006e2f",
        "tertiary": "#486554",
        "surface-container-lowest": "#ffffff",
        "on-secondary-container": "#506b4f",
        "surface": "#f8f9fa",
        "error-container": "#ffdad6",
        "secondary-fixed-dim": "#b0cfad",
        "on-surface": "#191c1d",
        "background": "#f8f9fa",
        "tertiary-fixed": "#caead6",
        "surface-container-low": "#f3f4f5",
        "error": "#ba1a1a",
        "secondary-fixed": "#ccebc7",
        "surface-dim": "#d9dadb",
        "on-background": "#191c1d",
        "tertiary-fixed-dim": "#afceba",
        "surface-container": "#edeeef",
        "outline": "#6d7b6c",
        "on-error": "#ffffff",
        "on-tertiary": "#ffffff",
        "on-surface-variant": "#3d4a3d",
        "on-primary-fixed-variant": "#005321",
        "on-secondary-fixed": "#07200b",
        "primary-fixed-dim": "#4ae176",
        "on-tertiary-container": "#2a4637",
        "surface-container-high": "#e7e8e9",
        "inverse-on-surface": "#f0f1f2",
        "surface-container-highest": "#e1e3e4",
        "outline-variant": "#bccbb9"
      },
      borderRadius: {
        "DEFAULT": "0.25rem",
        "lg": "0.5rem",
        "xl": "0.75rem",
        "full": "9999px"
      },
      spacing: {
        "margin-desktop": "40px",
        "sidebar-width": "280px",
        "margin-mobile": "16px",
        "container-max": "1440px",
        "gutter": "24px",
        "unit": "8px"
      },
      fontFamily: {
        "headline-md": ["Inter", "sans-serif"],
        "label-md": ["Inter", "sans-serif"],
        "body-lg": ["Inter", "sans-serif"],
        "headline-lg": ["Inter", "sans-serif"],
        "body-sm": ["Inter", "sans-serif"],
        "display": ["Inter", "sans-serif"],
        "body-md": ["Inter", "sans-serif"]
      },
      fontSize: {
        "headline-md": ["24px", { lineHeight: "32px", fontWeight: "600" }],
        "label-md": ["12px", { lineHeight: "16px", letterSpacing: "0.05em", fontWeight: "600" }],
        "body-lg": ["18px", { lineHeight: "28px", fontWeight: "400" }],
        "headline-lg": ["32px", { lineHeight: "40px", letterSpacing: "-0.01em", fontWeight: "600" }],
        "body-sm": ["14px", { lineHeight: "20px", fontWeight: "400" }],
        "display": ["48px", { lineHeight: "56px", letterSpacing: "-0.02em", fontWeight: "700" }],
        "body-md": ["16px", { lineHeight: "24px", fontWeight: "400" }]
      }
    }
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/container-queries')
  ],
}