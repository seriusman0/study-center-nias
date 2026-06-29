// Design-token JS bindings, for inline-style consumption in JSX.
// Mirrors /colors_and_type.css.
const T = {
  // brand
  teal900: '#003a2c', teal800: '#005a44', teal700: '#007a5c',
  teal600: '#0e8e6d', teal500: '#2ba888', teal300: '#9dd9c5',
  teal100: '#e1f3ec', teal50:  '#f1f9f5',
  orange700:'#b85a10', orange600:'#d8731a', orange500:'#f19121',
  orange300:'#f8c187', orange100:'#fde6cf',
  yellow700:'#97810f', yellow600:'#c8a91a', yellow500:'#e0c020',
  yellow300:'#f0db77', yellow100:'#fbf3c5',
  // neutrals
  ink900:'#15201c', ink700:'#324840', ink500:'#61756c', ink300:'#a3b3ac',
  line:'#d9e2dc', lineSoft:'#eef2ee',
  surface:'#ffffff', bg:'#f7f6f1', bgAlt:'#fbfaf6', paper:'#f5efe2',
  // type
  fontSans: '"Plus Jakarta Sans", "Segoe UI", system-ui, sans-serif',
  fontDisplay: '"Grandeur", "Plus Jakarta Sans", Georgia, serif',
  fontMono: '"JetBrains Mono", ui-monospace, monospace',
  // radius
  r:{xs:4,sm:8,md:12,lg:16,xl:24,pill:999},
  // shadow
  sh:{
    1:'0 1px 2px rgba(21,32,28,.04), 0 1px 1px rgba(21,32,28,.03)',
    2:'0 2px 6px rgba(21,32,28,.05), 0 1px 2px rgba(21,32,28,.04)',
    3:'0 8px 24px rgba(21,32,28,.07), 0 2px 4px rgba(21,32,28,.04)',
    4:'0 18px 40px rgba(21,32,28,.10), 0 4px 8px rgba(21,32,28,.05)',
    focus:'0 0 0 3px rgba(14,142,109,.28)',
  },
  // motion
  easeOut: 'cubic-bezier(0.22, 0.61, 0.36, 1)',
  easeBounce: 'cubic-bezier(0.34, 1.56, 0.64, 1)',
};
window.T = T;
