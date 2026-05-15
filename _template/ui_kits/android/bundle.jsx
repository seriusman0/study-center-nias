// === bundled JSX === //

/* ──────── ui_kits/android/Tokens.jsx ──────── */
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

/* ──────── ui_kits/android/Atoms.jsx ──────── */
// Small reusable atoms used across the kit.
// Loaded after Tokens.jsx so `T` is in scope.

function Icon({ name, size = 20, color = 'currentColor', stroke = 1.75 }) {
  const paths = ICONS[name] || [];
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none"
      stroke={color} strokeWidth={stroke} strokeLinecap="round" strokeLinejoin="round"
      style={{ flexShrink: 0 }}>
      {paths.map((d, i) => <path key={i} d={d} />)}
    </svg>
  );
}
const ICONS = {
  home: ['M3 9l9-7 9 7v11a2 2 0 0 1-2 2h-4a2 2 0 0 1-2-2v-5a2 2 0 0 0-4 0v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z'],
  'book-open': ['M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z','M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z'],
  'map-pin': ['M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z','M12 7a3 3 0 1 1 0 6 3 3 0 0 1 0-6'],
  newspaper: ['M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2','M18 14h-8','M15 18h-5','M10 6h8v4h-8z'],
  flame: ['M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z'],
  check: ['M20 6 9 17l-5-5'],
  arrowRight: ['M5 12h14','m12 5 7 7-7 7'],
  arrowLeft: ['M19 12H5','m12 19-7-7 7-7'],
  search: ['M11 11a8 8 0 1 0 0-16 8 8 0 0 0 0 16z','m21 21-4.35-4.35'].map(s => s.replace('M11 11a8 8 0 1 0 0-16','M11 19a8 8 0 1 0 0-16')),
  user: ['M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2','M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z'],
  sparkles: ['M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.582a.5.5 0 0 1 0 .962L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z','M20 3v4','M22 5h-4'],
  heart: ['M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7Z'],
  // Extended for full UI kit (admin / mentor / fulltimer screens)
  plus: ['M5 12h14','M12 5v14'],
  edit: ['M12 20h9','M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z'],
  trash: ['M3 6h18','M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6','M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2','M10 11v6','M14 11v6'],
  clipboardCheck: ['M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2','M9 4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1z','m9 14 2 2 4-4'],
  layoutDashboard: ['M3 3h7v9H3z','M14 3h7v5h-7z','M14 12h7v9h-7z','M3 16h7v5H3z'],
  mapPin: ['M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z','M12 7a3 3 0 1 1 0 6 3 3 0 0 1 0-6'],
  keyRound: ['M2 18a4 4 0 0 1 4-4h12a4 4 0 0 1 0 8H6a4 4 0 0 1-4-4z','M15.5 14.5a4 4 0 1 0 5.5-5.5 4 4 0 0 0-5.5 5.5z'],
  userCog: ['M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2','M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z','m18.5 19 1 1','M20 13l1.6 1.6c.4.4.4 1 0 1.4l-3.6 3.6c-.4.4-1 .4-1.4 0L15 18c-.4-.4-.4-1 0-1.4l3.6-3.6c.4-.4 1-.4 1.4 0z'],
  idCard: ['M16 10h2','M16 14h2','M6.17 15a3 3 0 0 1 5.66 0','M9 11a2 2 0 1 0 0-4 2 2 0 0 0 0 4','M2 5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2z'],
  school: ['M14 22v-4a2 2 0 0 0-2-2h0a2 2 0 0 0-2 2v4','m18 10 4 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-8l4-2','m18 5-6-3-6 3v7l6 3 6-3z','M12 5v9'],
  download: ['M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4','M7 10l5 5 5-5','M12 15V3'],
  filter: ['M22 3H2l8 9.46V19l4 2v-8.54z'],
  chevronRight: ['m9 18 6-6-6-6'],
  chevronDown: ['m6 9 6 6 6-6'],
  bell: ['M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9','M10.3 21a1.94 1.94 0 0 0 3.4 0'],
  fileText: ['M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z','M14 2v6h6','M16 13H8','M16 17H8','M10 9H8'],
  graduationCap: ['M22 10v6M2 10l10-5 10 5-10 5z','M6 12v5c3 3 9 3 12 0v-5'],
};

function Button({ tone = 'primary', size = 'md', icon, children, onClick, disabled, style }) {
  const tones = {
    primary: { bg: T.teal600, hover: T.teal700, fg: '#fff', border: 'none' },
    orange:  { bg: T.orange500, hover: T.orange600, fg: '#fff', border: 'none' },
    ghost:   { bg: 'transparent', hover: T.teal50, fg: T.teal700, border: `1px solid ${T.line}` },
    quiet:   { bg: 'transparent', hover: T.lineSoft, fg: T.ink700, border: 'none' },
    danger:  { bg: '#fff', hover: '#fbe5e2', fg: '#c1352b', border: '1px solid #f4ccc7' },
  };
  const t = tones[tone];
  const sizes = {
    sm: { padding: '6px 12px', fontSize: 13, borderRadius: T.r.sm },
    md: { padding: '10px 18px', fontSize: 14, borderRadius: T.r.md },
    lg: { padding: '14px 24px', fontSize: 16, borderRadius: T.r.md },
  };
  const [hover, setHover] = React.useState(false);
  const [pressed, setPressed] = React.useState(false);
  return (
    <button
      onClick={onClick}
      onMouseEnter={() => setHover(true)} onMouseLeave={() => { setHover(false); setPressed(false); }}
      onMouseDown={() => setPressed(true)} onMouseUp={() => setPressed(false)}
      disabled={disabled}
      style={{
        fontFamily: T.fontSans, fontWeight: 600, lineHeight: 1,
        background: hover ? t.hover : t.bg, color: t.fg, border: t.border,
        ...sizes[size], cursor: disabled ? 'not-allowed' : 'pointer',
        opacity: disabled ? 0.45 : 1,
        display: 'inline-flex', alignItems: 'center', gap: 8,
        transform: pressed ? 'scale(0.98)' : 'scale(1)',
        transition: `background 160ms ${T.easeOut}, transform 80ms ${T.easeOut}`,
        ...style,
      }}>
      {icon && <Icon name={icon} size={16} />}
      {children}
    </button>
  );
}

function Chip({ tone = 'cabang', children, style }) {
  const tones = {
    cabang: { bg: T.teal100, fg: T.teal800 },
    tag: { bg: T.lineSoft, fg: T.ink700 },
    success: { bg: '#e1f3ec', fg: T.teal700 },
    warning: { bg: '#fde6cf', fg: T.orange700 },
  };
  const t = tones[tone];
  return (
    <span style={{
      display: 'inline-flex', alignItems: 'center', gap: 6,
      fontFamily: T.fontSans, fontSize: 12, fontWeight: 600,
      background: t.bg, color: t.fg,
      padding: '4px 10px', borderRadius: T.r.pill,
      letterSpacing: '0.02em', ...style,
    }}>{children}</span>
  );
}

function RoleBadge({ role }) {
  const map = {
    student: { bg: T.teal600, fg: '#fff' },
    mentor:  { bg: T.orange500, fg: '#fff' },
    admin:   { bg: T.teal900, fg: '#fff' },
    fulltimer: { bg: T.yellow600, fg: T.ink900 },
  };
  const t = map[role] || map.student;
  return (
    <span style={{
      display: 'inline-block', fontSize: 10, fontWeight: 700,
      textTransform: 'uppercase', letterSpacing: '0.08em',
      padding: '3px 8px', borderRadius: T.r.pill,
      background: t.bg, color: t.fg,
    }}>{role}</span>
  );
}

function Avatar({ name, role = 'student', size = 40, src }) {
  const initials = (name || '').split(' ').map(s => s[0]).slice(0, 2).join('').toUpperCase();
  const ringColor = role === 'admin' ? T.teal700 : role === 'fulltimer' ? T.yellow500 : T.orange500;
  const bg = role === 'mentor' ? T.orange500 : role === 'admin' ? T.teal900 : T.teal700;
  return (
    <div style={{
      width: size, height: size, borderRadius: '50%',
      background: src ? `url(${src}) center/cover` : bg,
      color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center',
      fontFamily: T.fontSans, fontWeight: 700, fontSize: Math.round(size * 0.38),
      boxShadow: `0 0 0 ${Math.max(2, size/24)}px ${ringColor}, 0 0 0 ${Math.max(2, size/24)+1}px #fff`,
      flexShrink: 0,
    }}>
      {!src && initials}
    </div>
  );
}

function Toast({ msg, tone = 'success', show }) {
  const map = {
    success: { bg: T.teal700, fg: '#fff', icon: 'check' },
    danger:  { bg: '#c1352b', fg: '#fff', icon: 'check' },
  };
  const t = map[tone];
  if (!show) return null;
  return (
    <div style={{
      position: 'fixed', top: 16, right: 16, zIndex: 100,
      background: t.bg, color: t.fg, padding: '10px 16px',
      borderRadius: T.r.md, boxShadow: T.sh[4],
      fontFamily: T.fontSans, fontSize: 14, fontWeight: 500,
      display: 'flex', alignItems: 'center', gap: 8,
      animation: `slideInRight 200ms ${T.easeOut}`,
    }}>
      <Icon name={t.icon} size={16} /> {msg}
    </div>
  );
}

Object.assign(window, { Icon, Button, Chip, RoleBadge, Avatar, Toast });

/* ──────── ui_kits/android/android-frame.jsx ──────── */

// Android.jsx — Simplified Android (Material 3) device frame
// Status bar + top app bar + content + gesture nav + keyboard.
// Based on Figma M3 spec. No dependencies, no image assets.

const MD_C = {
  surface: '#f4fbf8',
  surfaceVariant: '#dae5e1',
  inverseOnSurface: '#ecf2ef',
  secondaryContainer: '#cde8e1',
  primaryFixedDim: '#83d5c6',
  onSurface: '#171d1b',
  onSurfaceVar: '#49454f',
  onPrimaryContainer: '#00201c',
  primary: '#006a60',
  frameBorder: 'rgba(116,119,117,0.5)',
};

// ─────────────────────────────────────────────────────────────
// Status bar (time left, wifi/cell/battery right)
// ─────────────────────────────────────────────────────────────
function AndroidStatusBar({ dark = false }) {
  const c = dark ? '#fff' : MD_C.onSurface;
  return (
    <div style={{
      height: 40, display: 'flex', alignItems: 'center',
      justifyContent: 'space-between', padding: '0 16px',
      position: 'relative',
      fontFamily: 'Roboto, system-ui, sans-serif',
    }}>
      {/* time left */}
      <div style={{ width: 128, display: 'flex', alignItems: 'center', gap: 8 }}>
        <span style={{ fontSize: 14, fontWeight: 400, letterSpacing: 0.25, lineHeight: '20px', color: c }}>9:30</span>
      </div>
      {/* camera punch-hole (center) */}
      <div style={{
        position: 'absolute', left: '50%', top: 8, transform: 'translateX(-50%)',
        width: 24, height: 24, borderRadius: 100, background: '#2e2e2e',
      }} />
      {/* status icons right */}
      <div style={{ display: 'flex', alignItems: 'center' }}>
        <div style={{ display: 'flex', paddingRight: 2 }}>
          <svg width="16" height="16" viewBox="0 0 16 16" style={{ marginRight: -2 }}>
            <path d="M8 13.3L.67 5.97a10.37 10.37 0 0114.66 0L8 13.3z" fill={c}/>
          </svg>
          <svg width="16" height="16" viewBox="0 0 16 16" style={{ marginRight: -2 }}>
            <path d="M14.67 14.67V1.33L1.33 14.67h13.34z" fill={c}/>
          </svg>
        </div>
        <svg width="16" height="16" viewBox="0 0 16 16">
          <rect x="3.75" y="2" width="8.5" height="13" rx="1.5" fill={c}/>
          <rect x="5.5" y="0.9" width="5" height="2" rx="0.5" fill={c}/>
        </svg>
      </div>
    </div>
  );
}

// ─────────────────────────────────────────────────────────────
// Top app bar (Material 3 small/medium)
// ─────────────────────────────────────────────────────────────
function AndroidAppBar({ title = 'Title', large = false }) {
  const iconDot = (
    <div style={{
      width: 48, height: 48, display: 'flex', alignItems: 'center', justifyContent: 'center',
    }}>
      <div style={{ width: 22, height: 22, borderRadius: '50%', background: MD_C.onSurfaceVar, opacity: 0.3 }} />
    </div>
  );
  return (
    <div style={{ background: MD_C.surface, padding: '4px 4px 0' }}>
      <div style={{ height: 56, display: 'flex', alignItems: 'center', gap: 4 }}>
        {iconDot}
        {!large && (
          <span style={{
            flex: 1, fontSize: 22, fontWeight: 400, color: MD_C.onSurface,
            fontFamily: 'Roboto, system-ui, sans-serif',
          }}>{title}</span>
        )}
        {large && <div style={{ flex: 1 }} />}
        {iconDot}
      </div>
      {large && (
        <div style={{
          padding: '16px 16px 20px',
          fontSize: 28, fontWeight: 400, color: MD_C.onSurface,
          fontFamily: 'Roboto, system-ui, sans-serif',
        }}>{title}</div>
      )}
    </div>
  );
}

// ─────────────────────────────────────────────────────────────
// List item (Material 3)
// ─────────────────────────────────────────────────────────────
function AndroidListItem({ headline, supporting, leading }) {
  return (
    <div style={{
      display: 'flex', alignItems: 'center', gap: 16,
      padding: '12px 16px', minHeight: 56, boxSizing: 'border-box',
      fontFamily: 'Roboto, system-ui, sans-serif',
    }}>
      {leading && (
        <div style={{
          width: 40, height: 40, borderRadius: '50%',
          background: MD_C.primary, color: '#fff',
          display: 'flex', alignItems: 'center', justifyContent: 'center',
          fontSize: 18, fontWeight: 500, flexShrink: 0,
        }}>{leading}</div>
      )}
      <div style={{ flex: 1, minWidth: 0 }}>
        <div style={{ fontSize: 16, color: MD_C.onSurface, lineHeight: '24px' }}>{headline}</div>
        {supporting && (
          <div style={{ fontSize: 14, color: MD_C.onSurfaceVar, lineHeight: '20px' }}>{supporting}</div>
        )}
      </div>
    </div>
  );
}

// ─────────────────────────────────────────────────────────────
// Gesture nav bar (pill)
// ─────────────────────────────────────────────────────────────
function AndroidNavBar({ dark = false }) {
  return (
    <div style={{
      height: 24, display: 'flex', alignItems: 'center', justifyContent: 'center',
    }}>
      <div style={{
        width: 108, height: 4, borderRadius: 2,
        background: dark ? '#fff' : MD_C.onSurface, opacity: 0.4,
      }} />
    </div>
  );
}

// ─────────────────────────────────────────────────────────────
// Device frame — wraps everything
// ─────────────────────────────────────────────────────────────
function AndroidDevice({
  children, width = 412, height = 892, dark = false,
  title, large = false, keyboard = false,
}) {
  return (
    <div style={{
      width, height, borderRadius: 18, overflow: 'hidden',
      background: dark ? '#1d1b20' : MD_C.surface,
      border: `8px solid ${MD_C.frameBorder}`,
      boxShadow: '0 30px 80px rgba(0,0,0,0.25)',
      display: 'flex', flexDirection: 'column', boxSizing: 'border-box',
    }}>
      <AndroidStatusBar dark={dark} />
      {title !== undefined && <AndroidAppBar title={title} large={large} />}
      <div style={{ flex: 1, overflow: 'auto' }}>
        {children}
      </div>
      {keyboard && <AndroidKeyboard />}
      <AndroidNavBar dark={dark} />
    </div>
  );
}

// ─────────────────────────────────────────────────────────────
// Keyboard — Gboard (Material 3)
// ─────────────────────────────────────────────────────────────
function AndroidKeyboard() {
  let _k = 0;
  const key = (l, { flex = 1, bg = MD_C.surface, r = 6, minW, fs = 21 } = {}) => (
    <div key={_k++} style={{
      height: 46, borderRadius: r, flex, minWidth: minW,
      background: bg, display: 'flex', alignItems: 'center', justifyContent: 'center',
      fontFamily: 'Roboto, system-ui', fontSize: fs,
      color: MD_C.onPrimaryContainer,
    }}>{l}</div>
  );
  const row = (keys, style = {}) => (
    <div style={{ display: 'flex', gap: 6, justifyContent: 'center', ...style }}>
      {keys.map(l => key(l))}
    </div>
  );
  return (
    <div style={{
      background: MD_C.inverseOnSurface, padding: '0 8px 8px',
      display: 'flex', flexDirection: 'column', gap: 4,
    }}>
      {/* navbar spacer (icons omitted) */}
      <div style={{ height: 44 }} />
      {/* key rows */}
      <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
        {row(['q','w','e','r','t','y','u','i','o','p'])}
        {row(['a','s','d','f','g','h','j','k','l'], { padding: '0 20px' })}
        <div style={{ display: 'flex', gap: 6 }}>
          {key('', { bg: MD_C.surfaceVariant })}
          <div style={{ display: 'flex', gap: 6, flex: 7, minWidth: 274 }}>
            {['z','x','c','v','b','n','m'].map(l => key(l))}
          </div>
          {key('', { bg: MD_C.surfaceVariant })}
        </div>
        <div style={{ display: 'flex', gap: 6 }}>
          {key('?123', { bg: MD_C.secondaryContainer, r: 100, minW: 58, fs: 14 })}
          {key(',', { bg: MD_C.surfaceVariant })}
          {key('', { flex: 3, minW: 154 })}
          {key('.', { bg: MD_C.surfaceVariant })}
          {key('', { bg: MD_C.primaryFixedDim, r: 100, minW: 58 })}
        </div>
      </div>
    </div>
  );
}

Object.assign(window, {
  AndroidDevice, AndroidStatusBar, AndroidAppBar, AndroidListItem, AndroidNavBar, AndroidKeyboard,
});

/* ──────── ui_kits/android/Common.jsx ──────── */
// Common.jsx — shared phone-UI atoms used by all role-specific screen files.
// Loaded after Atoms.jsx so Icon / Chip / RoleBadge / Avatar etc are in scope.

// SC-branded Android top app bar — replaces the Material 3 default
function SCTopBar({ title, subtitle, right, leading, dark = true, compact }) {
  return (
    <div style={{
      background: T.teal700, color: '#fff',
      padding: compact ? '6px 12px 10px' : '8px 16px 14px',
      borderBottom: `1px solid rgba(0,0,0,0.08)`,
    }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: 10, paddingTop: 6 }}>
        {leading || <img src="../../assets/logo.png" alt="" style={{ width: 28, height: 28, objectFit: 'contain', filter: 'brightness(0) invert(1)' }} />}
        <div style={{ flex: 1, minWidth: 0 }}>
          <div style={{ fontFamily: T.fontSans, fontWeight: 800, fontSize: 16, color: '#fff', letterSpacing: '-0.01em', lineHeight: 1.1 }}>{title}</div>
          {subtitle && <div style={{ fontSize: 10.5, color: T.yellow300, marginTop: 2, fontWeight: 700, letterSpacing: '0.05em' }}>{subtitle}</div>}
        </div>
        {right}
      </div>
    </div>
  );
}

function IconChip({ children, color = T.teal700, onDark }) {
  return (
    <div style={{
      width: 34, height: 34, borderRadius: 10,
      background: onDark ? 'rgba(255,255,255,0.15)' : T.teal100,
      color: onDark ? '#fff' : color,
      display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
      flexShrink: 0,
    }}>{children}</div>
  );
}

// Bottom nav — adapts to role
function BottomNav({ active, role = 'student' }) {
  const baseTabs = [
    { key: 'home', label: 'Beranda', icon: 'home' },
    { key: 'blog', label: 'Blog', icon: 'newspaper' },
  ];
  const extra = {
    student:   { key: 'jurnal',   label: 'Jurnal',   icon: 'book-open' },
    mentor:    { key: 'presensi', label: 'Presensi', icon: 'clipboardCheck' },
    admin:     { key: 'admin',    label: 'Admin',    icon: 'layoutDashboard' },
    fulltimer: { key: 'tulis',    label: 'Tulis',    icon: 'edit' },
    guest:     null,
  }[role];
  const profileTab = { key: 'profile', label: 'Profil', icon: 'user' };
  const tabs = extra ? [...baseTabs, extra, profileTab] : [...baseTabs, profileTab];

  return (
    <div style={{
      background: '#fff', borderTop: `1px solid ${T.line}`,
      display: 'grid', gridTemplateColumns: `repeat(${tabs.length}, 1fr)`,
      padding: '6px 0 4px',
    }}>
      {tabs.map(t => {
        const isActive = t.key === active;
        return (
          <div key={t.key} style={{
            display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 2,
            padding: '6px 0',
          }}>
            <div style={{
              padding: '4px 16px', borderRadius: 999,
              background: isActive ? T.teal100 : 'transparent',
              color: isActive ? T.teal700 : T.ink500,
              display: 'flex', alignItems: 'center',
            }}><Icon name={t.icon} size={20} stroke={isActive ? 2.2 : 1.75} /></div>
            <div style={{ fontSize: 10, fontWeight: isActive ? 700 : 500, color: isActive ? T.teal700 : T.ink500 }}>{t.label}</div>
          </div>
        );
      })}
    </div>
  );
}

// Numbered section card — used by Jurnal and admin sub-pages
function PhoneSection({ number, title, eyebrow, action, children }) {
  return (
    <div style={{ background: '#fff', borderRadius: 14, padding: 14, border: `1px solid ${T.line}`, boxShadow: T.sh[1] }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 10 }}>
        {number && <div style={{
          width: 22, height: 22, borderRadius: '50%',
          background: T.teal100, color: T.teal700,
          display: 'flex', alignItems: 'center', justifyContent: 'center',
          fontFamily: T.fontSans, fontWeight: 800, fontSize: 12, flexShrink: 0,
        }}>{number}</div>}
        <div style={{ flex: 1, minWidth: 0 }}>
          <div style={{ fontWeight: 700, fontSize: 14, color: T.teal800, lineHeight: 1.2 }}>{title}</div>
          {eyebrow && <div style={{ fontSize: 10, color: T.ink500, marginTop: 1 }}>{eyebrow}</div>}
        </div>
        {action}
      </div>
      <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>{children}</div>
    </div>
  );
}

function PhoneCheck({ checked, title, sub, compact }) {
  return (
    <div style={{
      display: 'flex', alignItems: compact ? 'center' : 'flex-start', gap: 10,
      padding: compact ? '6px 8px' : '8px 10px',
      border: `1px solid ${checked ? T.teal300 : T.line}`,
      background: checked ? T.teal50 : '#fff',
      borderRadius: 8,
    }}>
      <div style={{
        width: 18, height: 18, borderRadius: 4, flexShrink: 0,
        border: `2px solid ${checked ? T.teal600 : T.line}`,
        background: checked ? T.teal600 : '#fff',
        display: 'flex', alignItems: 'center', justifyContent: 'center',
        marginTop: compact ? 0 : 1,
      }}>{checked && <Icon name="check" size={11} color="#fff" stroke={3.5} />}</div>
      <div style={{ flex: 1, minWidth: 0 }}>
        <div style={{ fontSize: 13, fontWeight: compact ? 500 : 600, color: T.ink900, lineHeight: 1.3 }}>{title}</div>
        {sub && <div style={{ fontSize: 12, color: T.ink500, marginTop: 1 }}>{sub}</div>}
      </div>
    </div>
  );
}

// Permissions list — shown at top of each role's "what you can do" header
function PermissionsBlock({ role, perms }) {
  const map = {
    admin:     { color: T.teal900,  bg: T.teal100,   label: 'Administrator' },
    mentor:    { color: T.orange700, bg: T.orange100, label: 'Mentor' },
    student:   { color: T.teal700,  bg: T.teal100,   label: 'Siswa' },
    fulltimer: { color: T.yellow700, bg: T.yellow100, label: 'Full-timer' },
    guest:     { color: T.ink700,   bg: T.lineSoft,  label: 'Tamu publik' },
  }[role];
  return (
    <div style={{
      background: map.bg, padding: '10px 12px', borderRadius: 12,
      border: `1px solid ${map.color}22`, margin: '12px 16px',
    }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: 6, marginBottom: 6 }}>
        <div style={{
          padding: '2px 8px', borderRadius: 999,
          background: map.color, color: '#fff',
          fontSize: 9, fontWeight: 800, textTransform: 'uppercase', letterSpacing: '0.08em',
        }}>{role}</div>
        <div style={{ fontSize: 11, fontWeight: 600, color: map.color }}>{map.label}</div>
      </div>
      <div style={{ display: 'flex', flexWrap: 'wrap', gap: 4 }}>
        {perms.map(p => (
          <span key={p} style={{
            fontSize: 10, fontWeight: 500, color: map.color,
            background: '#fff', border: `1px solid ${map.color}33`,
            padding: '2px 6px', borderRadius: 4, fontFamily: T.fontMono,
          }}>{p}</span>
        ))}
      </div>
    </div>
  );
}

// Listrow — admin table-like rows
function ListRow({ leading, primary, secondary, badge, action }) {
  return (
    <div style={{
      display: 'flex', alignItems: 'center', gap: 12,
      padding: '10px 14px', background: '#fff',
      borderBottom: `1px solid ${T.lineSoft}`,
    }}>
      {leading}
      <div style={{ flex: 1, minWidth: 0 }}>
        <div style={{ fontSize: 13.5, fontWeight: 600, color: T.ink900, lineHeight: 1.2, textOverflow: 'ellipsis', overflow: 'hidden', whiteSpace: 'nowrap' }}>{primary}</div>
        {secondary && <div style={{ fontSize: 11.5, color: T.ink500, marginTop: 2 }}>{secondary}</div>}
      </div>
      {badge}
      {action}
    </div>
  );
}

// Page-header inside an Android screen body
function PageHeader({ title, sub, action }) {
  return (
    <div style={{ padding: '12px 16px 6px', display: 'flex', alignItems: 'flex-start', gap: 10 }}>
      <div style={{ flex: 1 }}>
        <div style={{ fontFamily: T.fontSans, fontWeight: 800, fontSize: 17, color: T.ink900, letterSpacing: '-0.01em' }}>{title}</div>
        {sub && <div style={{ fontSize: 12, color: T.ink500, marginTop: 2 }}>{sub}</div>}
      </div>
      {action}
    </div>
  );
}

function AddFab({ onClick, color = T.teal600 }) {
  return (
    <div style={{
      position: 'absolute', right: 12, bottom: 60, zIndex: 5,
      width: 52, height: 52, borderRadius: 16,
      background: color, color: '#fff',
      display: 'flex', alignItems: 'center', justifyContent: 'center',
      boxShadow: '0 8px 24px rgba(14,142,109,0.45), 0 2px 4px rgba(0,0,0,0.15)',
    }}><Icon name="plus" size={22} stroke={2.5} /></div>
  );
}

Object.assign(window, { SCTopBar, IconChip, BottomNav, PhoneSection, PhoneCheck, PermissionsBlock, ListRow, PageHeader, AddFab });

/* ──────── ui_kits/android/ScreensCommon.jsx ──────── */
// ScreensGuest.jsx & ScreensStudent.jsx & ScreensFulltimer.jsx
// All shared screens for the non-staff roles.

// ─────────────────────────────────── GUEST ───────────────────────────────────

function GuestHome() {
  return (
    <div style={{ background: T.bg, minHeight: '100%', display: 'flex', flexDirection: 'column' }}>
      <SCTopBar title="Study Center Nias" subtitle="TAMU · BELUM MASUK"
        right={<div style={{
          padding: '6px 12px', background: T.yellow500, color: T.teal900,
          borderRadius: 999, fontSize: 11, fontWeight: 800,
        }}>Daftar</div>} />

      {/* Hero card */}
      <div style={{ padding: 16 }}>
        <div style={{
          background: `linear-gradient(135deg, ${T.teal800}, ${T.teal600})`,
          color: '#fff', borderRadius: 16, padding: '20px 18px',
          position: 'relative', overflow: 'hidden',
        }}>
          <svg viewBox="0 0 100 100" style={{ position: 'absolute', right: -10, top: -10, width: 120, opacity: 0.12 }}>
            <path d="M30,55 L50,10 L70,55 Z" fill={T.yellow500} />
            <rect x="25" y="60" width="50" height="10" fill={T.yellow500} />
            <rect x="25" y="72" width="50" height="10" fill={T.orange500} />
          </svg>
          <div style={{ fontSize: 11, color: T.yellow300, fontWeight: 700, letterSpacing: '0.08em', textTransform: 'uppercase' }}>KOMUNITAS BELAJAR NIAS</div>
          <div style={{ fontFamily: T.fontDisplay, fontSize: 28, marginTop: 6, lineHeight: 1.05 }}>Rumah kedua remaja.</div>
          <div style={{ fontSize: 13, color: 'rgba(255,255,255,0.85)', marginTop: 8, lineHeight: 1.45 }}>
            Tempat belajar, bertumbuh, dan dikenal di empat cabang Nias.
          </div>
          <div style={{ display: 'flex', gap: 8, marginTop: 14 }}>
            <button style={{
              padding: '8px 14px', borderRadius: 10, border: 'none',
              background: T.orange500, color: '#fff', fontWeight: 700, fontSize: 13,
            }}>Baca Blog</button>
            <button style={{
              padding: '8px 14px', borderRadius: 10, fontWeight: 600, fontSize: 13,
              background: 'rgba(255,255,255,0.15)', color: '#fff',
              border: '1px solid rgba(255,255,255,0.30)',
            }}>Bergabung</button>
          </div>
        </div>
      </div>

      <div style={{ padding: '0 16px 8px', fontSize: 11, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', color: T.teal700 }}>EMPAT CABANG</div>
      <div style={{ padding: '0 16px 12px', display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 8 }}>
        {[
          ['Gunungsitoli', '12 artikel'],
          ['Kab. Nias', '8 artikel'],
          ['Kab. Nias Selatan', '6 artikel'],
          ['Kab. Nias Utara', '3 artikel'],
        ].map(([n, c]) => (
          <div key={n} style={{
            background: '#fff', borderRadius: 12, padding: '12px 10px',
            border: `1px solid ${T.line}`, display: 'flex', alignItems: 'center', gap: 8,
          }}>
            <IconChip><Icon name="mapPin" size={16} /></IconChip>
            <div style={{ flex: 1, minWidth: 0 }}>
              <div style={{ fontSize: 12, fontWeight: 700, color: T.ink900, lineHeight: 1.2, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{n}</div>
              <div style={{ fontSize: 10, color: T.ink500, marginTop: 1 }}>{c}</div>
            </div>
          </div>
        ))}
      </div>

      <div style={{ flex: 1 }} />
      <BottomNav role="guest" active="home" />
    </div>
  );
}

function GuestBlog() {
  const posts = [
    { title: 'Persiapan UTS di Cabang Gunungsitoli', cabang: 'Gunungsitoli', date: '14 Mei 2026', author: 'Seri W.' },
    { title: 'Renungan Yakobus 1:19', cabang: 'Kab. Nias', date: '12 Mei', author: 'Ruth H.' },
    { title: 'Turnamen Futsal antar Cabang', cabang: 'Nias Selatan', date: '08 Mei', author: 'Andre D.' },
    { title: 'Mengenal Karakter Christ-Like', cabang: 'Gunungsitoli', date: '01 Mei', author: 'Ruth H.' },
  ];
  return (
    <div style={{ background: T.bg, minHeight: '100%', display: 'flex', flexDirection: 'column' }}>
      <SCTopBar title="Blog" subtitle="EMPAT CABANG"
        right={<Icon name="search" size={20} color="#fff" />} />

      {/* Filter chips */}
      <div style={{ padding: '12px 16px 6px', display: 'flex', gap: 6, overflowX: 'auto' }}>
        {['Semua', 'Gunungsitoli', 'Kab. Nias', 'N. Selatan', 'N. Utara'].map((c, i) => (
          <div key={c} style={{
            padding: '6px 12px', borderRadius: 999, fontSize: 12, fontWeight: 600,
            background: i === 0 ? T.teal700 : '#fff',
            color: i === 0 ? '#fff' : T.ink700,
            border: `1px solid ${i === 0 ? T.teal700 : T.line}`,
            flexShrink: 0,
          }}>{c}</div>
        ))}
      </div>

      <div style={{ padding: '8px 16px 16px', display: 'flex', flexDirection: 'column', gap: 10 }}>
        {posts.map((p, i) => (
          <div key={i} style={{
            background: '#fff', borderRadius: 14, padding: 12,
            border: `1px solid ${T.line}`, display: 'flex', gap: 12,
          }}>
            <div style={{
              width: 64, height: 64, borderRadius: 10, flexShrink: 0,
              background: `linear-gradient(135deg, ${T.teal700}, ${T.teal500})`,
              display: 'flex', alignItems: 'center', justifyContent: 'center',
            }}><Icon name="newspaper" size={22} color="rgba(255,255,255,0.85)" stroke={1.4} /></div>
            <div style={{ flex: 1, minWidth: 0 }}>
              <div style={{ display: 'flex', gap: 6, marginBottom: 4 }}>
                <Chip tone="cabang" style={{ fontSize: 10, padding: '2px 7px' }}>{p.cabang}</Chip>
              </div>
              <div style={{ fontSize: 13.5, fontWeight: 700, color: T.ink900, lineHeight: 1.3, textWrap: 'pretty' }}>{p.title}</div>
              <div style={{ fontSize: 11, color: T.ink500, marginTop: 4 }}>{p.author} · {p.date}</div>
            </div>
          </div>
        ))}
      </div>

      <div style={{ flex: 1 }} />
      <BottomNav role="guest" active="blog" />
    </div>
  );
}

// ─────────────────────────────────── STUDENT ─────────────────────────────────

function StudentHome() {
  return (
    <div style={{ background: T.bg, minHeight: '100%', display: 'flex', flexDirection: 'column' }}>
      <SCTopBar title="Halo, Seri" subtitle="SISWA · KAB. NIAS"
        right={<Avatar name="Seri Waruwu" role="student" size={32} />} />

      <PermissionsBlock role="student" perms={['create_blog', 'view_blogs']} />

      {/* Verse-of-the-day */}
      <div style={{ padding: '0 16px 10px' }}>
        <div style={{
          background: T.yellow100, border: `1px solid ${T.yellow300}`,
          borderRadius: 14, padding: 14,
        }}>
          <div style={{ fontSize: 10, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', color: T.teal700, marginBottom: 4 }}>AYAT HARI INI</div>
          <div style={{ fontFamily: T.fontDisplay, fontSize: 17, color: T.ink900, lineHeight: 1.4 }}>
            "Setiap orang harus cepat untuk mendengar, tetapi lambat untuk berkata-kata."
          </div>
          <div style={{ fontSize: 12, color: T.ink700, marginTop: 6, fontWeight: 600 }}>— Yakobus 1:19</div>
        </div>
      </div>

      {/* Action tiles */}
      <div style={{ padding: '4px 16px 12px', display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 10 }}>
        <Tile icon="book-open" tone="teal" label="Jurnal Harian" sub="3 / 8 hari ini" />
        <Tile icon="flame" tone="orange" label="Streak" sub="12 hari" />
        <Tile icon="newspaper" tone="teal" label="Blog" sub="3 baru" />
        <Tile icon="idCard" tone="yellow" label="Kartu Nama" sub="Siap dicetak" />
      </div>

      <div style={{ flex: 1 }} />
      <BottomNav role="student" active="home" />
    </div>
  );
}

function StudentJurnal() {
  return (
    <div style={{ background: T.bg, minHeight: '100%', display: 'flex', flexDirection: 'column' }}>
      <SCTopBar title="Jurnal Harian" subtitle="KAMIS · 14 MEI 2026"
        right={<div style={{ display: 'flex', gap: 4 }}>
          <NavBtn><Icon name="arrowLeft" size={16} color="#fff" /></NavBtn>
          <NavBtn><Icon name="arrowRight" size={16} color="#fff" /></NavBtn>
        </div>} />

      <div style={{ padding: '12px 16px 4px' }}>
        <div style={{
          display: 'inline-flex', alignItems: 'center', gap: 6,
          background: T.orange100, color: T.orange700,
          padding: '6px 12px', borderRadius: 999, fontSize: 12, fontWeight: 700,
          border: `1px solid ${T.orange300}`,
        }}>
          <Icon name="flame" size={14} color={T.orange500} stroke={2} />
          Streak 12 hari berturut-turut
        </div>
      </div>

      <div style={{ padding: '8px 16px 16px', display: 'flex', flexDirection: 'column', gap: 10 }}>
        <PhoneSection number="1" title="Pembacaan Alkitab">
          <PhoneCheck checked title="Perjanjian Lama" sub="2 Tawarikh 14-16" />
          <PhoneCheck title="Perjanjian Baru" sub="Roma 8:18-39" />
        </PhoneSection>

        <PhoneSection number="2" title="Hafal Ayat Mingguan" eyebrow="Minggu ke-2 · Mei 2026">
          <div style={{
            background: T.yellow100, border: `1px solid ${T.yellow300}`,
            borderRadius: 10, padding: '10px 12px', marginBottom: 8,
          }}>
            <div style={{ fontWeight: 800, fontSize: 13, color: T.teal800, marginBottom: 4 }}>Yakobus 1:19</div>
            <div style={{ fontFamily: T.fontDisplay, fontSize: 15, lineHeight: 1.4, color: T.ink900 }}>
              "Setiap orang harus cepat untuk mendengar, tetapi lambat untuk berkata-kata."
            </div>
          </div>
          <PhoneCheck title="Sudah hafal ayat minggu ini" />
        </PhoneSection>

        <PhoneSection number="3" title="Jadwal Kehidupan">
          <Subhead>Kerohanian</Subhead>
          <PhoneCheck compact checked title="Doa pagi" />
          <PhoneCheck compact title="Hadir di kebaktian remaja" />
          <Subhead>Pendidikan</Subhead>
          <PhoneCheck compact checked title="Belajar di Study Center 1 jam" />
          <PhoneCheck compact title="Mengerjakan PR sekolah" />
          <Subhead>Karakter</Subhead>
          <PhoneCheck compact title="Menahan diri dari berkata kasar" />
        </PhoneSection>
      </div>

      <div style={{ flex: 1 }} />
      <BottomNav role="student" active="jurnal" />
    </div>
  );
}

function StudentProfile() {
  return (
    <div style={{ background: T.bg, minHeight: '100%', display: 'flex', flexDirection: 'column' }}>
      <SCTopBar title="Profil" right={<Icon name="edit" size={20} color="#fff" />} />

      {/* Identity */}
      <div style={{
        background: '#fff', margin: 16, borderRadius: 16, padding: 18,
        border: `1px solid ${T.line}`, boxShadow: T.sh[1],
        display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 6, textAlign: 'center',
      }}>
        <Avatar name="Seri Waruwu" role="student" size={84} />
        <div style={{ fontSize: 18, fontWeight: 800, color: T.ink900, marginTop: 8 }}>Seri Waruwu</div>
        <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
          <RoleBadge role="student" />
          <span style={{ fontSize: 12, color: T.ink500 }}>· Kab. Nias</span>
        </div>
        <div style={{ fontSize: 13, color: T.ink700, marginTop: 6, maxWidth: 240, lineHeight: 1.45 }}>
          Kelas 9 di SMP N 2 Gido. Suka matematika dan voli.
        </div>
      </div>

      <div style={{ padding: '0 16px 8px', fontSize: 11, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', color: T.ink500 }}>Identitas</div>
      <div style={{ background: '#fff', margin: '0 16px 14px', borderRadius: 14, border: `1px solid ${T.line}` }}>
        <DataRow icon="school" label="Sekolah" value="SMP N 2 Gido" />
        <DataRow icon="graduationCap" label="Kelas" value="9A" />
        <DataRow icon="mapPin" label="Cabang" value="Kab. Nias" last />
      </div>

      <div style={{ padding: '0 16px 8px', fontSize: 11, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', color: T.ink500 }}>Cetak</div>
      <div style={{ padding: '0 16px 16px', display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 10 }}>
        <PrintCard icon="idCard" label="Kartu Nama" sub="Cetak / PDF" />
        <PrintCard icon="fileText" label="CV" sub="Cetak / PDF" />
      </div>

      <div style={{ flex: 1 }} />
      <BottomNav role="student" active="profile" />
    </div>
  );
}

// ─────────────────────────────────── FULLTIMER ───────────────────────────────

function FulltimerHome() {
  return (
    <div style={{ background: T.bg, minHeight: '100%', display: 'flex', flexDirection: 'column', position: 'relative' }}>
      <SCTopBar title="Halo, Daniel" subtitle="FULL-TIMER · BLOG MODERATOR"
        right={<Avatar name="Daniel Lase" role="fulltimer" size={32} />} />

      <PermissionsBlock role="fulltimer" perms={['manage_blogs', 'create_blog', 'view_blogs']} />

      <PageHeader title="Semua blog" sub="Tugasmu: kurasi & moderasi"
        action={<button style={pillBtn(T.teal600)}><Icon name="plus" size={14} color="#fff" stroke={3} /> Tulis</button>} />

      <div style={{ padding: '0 16px 16px', display: 'flex', flexDirection: 'column', gap: 8 }}>
        {[
          { title: 'Persiapan UTS di Cabang Gunungsitoli', author: 'Seri W.', state: 'published', date: '14 Mei' },
          { title: 'Renungan Yakobus 1:19', author: 'Ruth H.', state: 'published', date: '12 Mei' },
          { title: 'Draft: Pelayanan Akhir Bulan', author: 'Andre D.', state: 'draft', date: '11 Mei' },
          { title: 'Turnamen Futsal antar Cabang', author: 'Andre D.', state: 'published', date: '08 Mei' },
        ].map((p, i) => (
          <div key={i} style={{
            background: '#fff', borderRadius: 12, padding: '12px 14px',
            border: `1px solid ${T.line}`, display: 'flex', alignItems: 'center', gap: 10,
          }}>
            <div style={{
              width: 4, height: 32, borderRadius: 2,
              background: p.state === 'draft' ? T.yellow500 : T.teal600,
            }} />
            <div style={{ flex: 1, minWidth: 0 }}>
              <div style={{ fontSize: 13.5, fontWeight: 700, color: T.ink900, lineHeight: 1.25, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{p.title}</div>
              <div style={{ fontSize: 11, color: T.ink500, marginTop: 2 }}>{p.author} · {p.date}</div>
            </div>
            {p.state === 'draft'
              ? <Chip tone="warning" style={{ fontSize: 9 }}>Draft</Chip>
              : <Chip tone="success" style={{ fontSize: 9 }}>Tayang</Chip>}
            <Icon name="chevronRight" size={16} color={T.ink300} />
          </div>
        ))}
      </div>

      <div style={{ flex: 1 }} />
      <AddFab />
      <BottomNav role="fulltimer" active="home" />
    </div>
  );
}

function FulltimerEditor() {
  return (
    <div style={{ background: T.bg, minHeight: '100%', display: 'flex', flexDirection: 'column' }}>
      <SCTopBar title="Tulis Blog" subtitle="DRAFT TERSIMPAN"
        leading={<Icon name="arrowLeft" size={22} color="#fff" />}
        right={<button style={{
          padding: '6px 12px', borderRadius: 8, border: 'none',
          background: T.orange500, color: '#fff', fontWeight: 700, fontSize: 12,
        }}>Terbitkan</button>} />

      <div style={{ padding: 16 }}>
        <div style={{ fontSize: 11, fontWeight: 600, color: T.ink900, marginBottom: 6 }}>Judul</div>
        <input readOnly defaultValue="Pelayanan Akhir Bulan di Empat Cabang" style={{
          width: '100%', boxSizing: 'border-box', padding: '11px 14px',
          border: `1.5px solid ${T.teal600}`, borderRadius: 10,
          fontFamily: T.fontSans, fontSize: 14, fontWeight: 700,
          boxShadow: '0 0 0 3px rgba(14,142,109,0.20)',
          color: T.ink900,
        }} />

        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 10, marginTop: 12 }}>
          <Field label="Cabang">
            <SelectFake value="Gunungsitoli" />
          </Field>
          <Field label="Tag">
            <SelectFake value="renungan, remaja" />
          </Field>
        </div>

        <div style={{ marginTop: 12, fontSize: 11, fontWeight: 600, color: T.ink900, marginBottom: 6 }}>Isi</div>
        <div style={{
          background: '#fff', border: `1px solid ${T.line}`, borderRadius: 10,
          minHeight: 220, padding: '12px 14px',
        }}>
          <div style={{ display: 'flex', gap: 4, marginBottom: 10, paddingBottom: 8, borderBottom: `1px solid ${T.lineSoft}` }}>
            {['B', 'I', 'H2', 'Quote', 'List', 'Image'].map(t => (
              <div key={t} style={{
                padding: '4px 8px', fontSize: 11, fontWeight: 700, color: T.ink500,
                borderRadius: 6, background: T.bg,
              }}>{t}</div>
            ))}
          </div>
          <div style={{ fontSize: 14, color: T.ink900, lineHeight: 1.6 }}>
            Akhir bulan ini empat cabang bertemu di Teluk Dalam untuk satu hari penuh
            persaudaraan, doa, dan permainan. Berikut catatan tim full-timer yang
            mengkoordinasi acara…
          </div>
        </div>
      </div>

      <div style={{ flex: 1 }} />
    </div>
  );
}

// ─────────────────────────────────── helpers ─────────────────────────────────

function NavBtn({ children }) {
  return <div style={{ width: 32, height: 32, borderRadius: 8, background: 'rgba(255,255,255,0.15)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>{children}</div>;
}
function Subhead({ children }) {
  return <div style={{ fontSize: 10, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', color: T.ink500, margin: '6px 0 4px' }}>{children}</div>;
}
function Tile({ icon, label, sub, tone }) {
  const map = {
    teal:   { bg: T.teal100,   color: T.teal600 },
    orange: { bg: T.orange100, color: T.orange500 },
    yellow: { bg: T.yellow100, color: T.yellow600 },
  };
  const t = map[tone];
  return (
    <div style={{
      background: '#fff', borderRadius: 14, padding: 14,
      border: `1px solid ${T.line}`, boxShadow: T.sh[1],
      display: 'flex', flexDirection: 'column', gap: 8,
    }}>
      <div style={{
        width: 36, height: 36, borderRadius: 10, background: t.bg,
        display: 'flex', alignItems: 'center', justifyContent: 'center',
      }}><Icon name={icon} size={18} color={t.color} stroke={2} /></div>
      <div>
        <div style={{ fontSize: 13, fontWeight: 700, color: T.ink900 }}>{label}</div>
        <div style={{ fontSize: 11, color: T.ink500, marginTop: 2 }}>{sub}</div>
      </div>
    </div>
  );
}
function DataRow({ icon, label, value, last }) {
  return (
    <div style={{
      padding: '12px 14px', display: 'flex', alignItems: 'center', gap: 12,
      borderBottom: last ? 'none' : `1px solid ${T.lineSoft}`,
    }}>
      <Icon name={icon} size={18} color={T.teal700} stroke={1.7} />
      <div style={{ flex: 1, fontSize: 13, color: T.ink900 }}>{label}</div>
      <div style={{ fontSize: 13, color: T.ink700, fontWeight: 600 }}>{value}</div>
    </div>
  );
}
function PrintCard({ icon, label, sub }) {
  return (
    <div style={{
      background: '#fff', border: `1px solid ${T.line}`, borderRadius: 14, padding: 14,
      display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 6, textAlign: 'center',
    }}>
      <div style={{ width: 40, height: 40, borderRadius: 10, background: T.teal100, color: T.teal700, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
        <Icon name={icon} size={22} stroke={1.8} />
      </div>
      <div style={{ fontSize: 13, fontWeight: 700, color: T.ink900 }}>{label}</div>
      <div style={{ fontSize: 11, color: T.ink500 }}>{sub}</div>
    </div>
  );
}
function Field({ label, children }) {
  return (
    <div><div style={{ fontSize: 11, fontWeight: 600, color: T.ink900, marginBottom: 6 }}>{label}</div>{children}</div>
  );
}
function SelectFake({ value }) {
  return (
    <div style={{
      padding: '10px 12px', border: `1px solid ${T.line}`, borderRadius: 10,
      background: '#fff', display: 'flex', alignItems: 'center',
      fontSize: 13, color: T.ink900,
    }}>
      <span style={{ flex: 1 }}>{value}</span>
      <Icon name="chevronDown" size={14} color={T.ink500} />
    </div>
  );
}
function pillBtn(bg) {
  return {
    padding: '6px 12px', borderRadius: 999, border: 'none',
    background: bg, color: '#fff', fontFamily: T.fontSans,
    fontSize: 12, fontWeight: 700,
    display: 'inline-flex', alignItems: 'center', gap: 4,
  };
}

Object.assign(window, {
  GuestHome, GuestBlog,
  StudentHome, StudentJurnal, StudentProfile,
  FulltimerHome, FulltimerEditor,
  Tile, DataRow, PrintCard, SelectFake, Field, pillBtn, Subhead, NavBtn,
});

/* ──────── ui_kits/android/ScreensMentor.jsx ──────── */
// ScreensMentor.jsx — mentor-specific screens

function MentorHome() {
  return (
    <div style={{ background: T.bg, minHeight: '100%', display: 'flex', flexDirection: 'column' }}>
      <SCTopBar title="Halo, Ruth" subtitle="MENTOR · GUNUNGSITOLI"
        right={<Avatar name="Ruth Halawa" role="mentor" size={32} />} />

      <PermissionsBlock role="mentor" perms={['create_blog', 'view_blogs', 'create_schedule']} />

      {/* Today summary */}
      <div style={{ padding: '0 16px 12px' }}>
        <div style={{
          background: '#fff', borderRadius: 14, padding: 14,
          border: `1px solid ${T.line}`, boxShadow: T.sh[1],
        }}>
          <div style={{ fontSize: 10, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', color: T.ink500 }}>HARI INI</div>
          <div style={{ display: 'flex', alignItems: 'baseline', gap: 8, marginTop: 4 }}>
            <div style={{ fontFamily: T.fontDisplay, fontSize: 28, color: T.teal800, lineHeight: 1 }}>2 sesi</div>
            <div style={{ fontSize: 11, color: T.ink500 }}>terjadwal di kelas 7A &amp; 8B</div>
          </div>
          <div style={{ marginTop: 10, display: 'flex', gap: 8 }}>
            <button style={pillBtn(T.teal600)}><Icon name="plus" size={14} color="#fff" stroke={3} /> Catat Presensi</button>
            <button style={{ ...pillBtn('#fff'), color: T.teal700, border: `1px solid ${T.line}` }}>Lihat semua</button>
          </div>
        </div>
      </div>

      {/* Action tiles */}
      <div style={{ padding: '0 16px 12px', display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 10 }}>
        <Tile icon="clipboardCheck" tone="orange" label="Presensi Saya" sub="14 minggu ini" />
        <Tile icon="users" tone="teal" label="Daftar Siswa" sub="22 di cabang" />
        <Tile icon="book-open" tone="yellow" label="Jurnal Siswa" sub="Verifikasi" />
        <Tile icon="newspaper" tone="teal" label="Tulis Blog" sub="3 draft" />
      </div>

      <div style={{ flex: 1 }} />
      <BottomNav role="mentor" active="home" />
    </div>
  );
}

function MentorPresensi() {
  const rows = [
    { date: 'Kam, 14 Mei 2026', kelas: '7A Matematika', cabang: 'Gunungsitoli', datang: '15:00', pulang: '17:00', murid: 8 },
    { date: 'Rab, 13 Mei 2026', kelas: '8B Bahasa Inggris', cabang: 'Gunungsitoli', datang: '15:00', pulang: '17:00', murid: 6 },
    { date: 'Sel, 12 Mei 2026', kelas: '7A Matematika', cabang: 'Gunungsitoli', datang: '15:00', pulang: '16:30', murid: 7 },
    { date: 'Sen, 11 Mei 2026', kelas: '9A IPA', cabang: 'Gunungsitoli', datang: '15:00', pulang: '17:00', murid: 5, locked: true },
  ];
  return (
    <div style={{ background: T.bg, minHeight: '100%', display: 'flex', flexDirection: 'column', position: 'relative' }}>
      <SCTopBar title="Presensi Mentor Saya" subtitle="KEHADIRAN &amp; SESI MENGAJAR"
        right={<Icon name="filter" size={20} color="#fff" />} />

      <div style={{ padding: '12px 16px 16px', display: 'flex', flexDirection: 'column', gap: 8 }}>
        {rows.map((r, i) => (
          <div key={i} style={{
            background: '#fff', borderRadius: 12, padding: '12px 14px',
            border: `1px solid ${T.line}`,
          }}>
            <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: 10 }}>
              <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ fontSize: 11, color: T.ink500, fontFamily: T.fontMono }}>{r.date.toUpperCase()}</div>
                <div style={{ fontSize: 14, fontWeight: 700, color: T.ink900, marginTop: 2 }}>{r.kelas}</div>
                <div style={{ fontSize: 11, color: T.ink500, marginTop: 2 }}>{r.cabang}</div>
              </div>
              <Chip tone="cabang" style={{ fontSize: 10, padding: '2px 8px' }}>{r.murid} murid</Chip>
            </div>
            <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginTop: 8, paddingTop: 8, borderTop: `1px solid ${T.lineSoft}` }}>
              <div style={{ fontSize: 12, color: T.ink700 }}>
                <span style={{ color: T.ink500 }}>Datang</span> <strong>{r.datang}</strong>
              </div>
              <div style={{ fontSize: 12, color: T.ink700 }}>
                <span style={{ color: T.ink500 }}>Pulang</span> <strong>{r.pulang}</strong>
              </div>
              <div style={{ marginLeft: 'auto' }}>
                {r.locked
                  ? <span style={{ fontSize: 11, color: T.ink300, fontStyle: 'italic' }}>terkunci</span>
                  : <span style={{ fontSize: 11, color: T.teal700, fontWeight: 700 }}>Edit · Hapus</span>}
              </div>
            </div>
          </div>
        ))}
      </div>

      <div style={{ flex: 1 }} />
      <AddFab color={T.orange500} />
      <BottomNav role="mentor" active="presensi" />
    </div>
  );
}

function MentorPanel() {
  return (
    <div style={{ background: T.bg, minHeight: '100%', display: 'flex', flexDirection: 'column' }}>
      <SCTopBar title="Panel Mentor" subtitle="AKSES TERBATAS (READ-ONLY)" />

      <div style={{ padding: '12px 16px 0' }}>
        <div style={{
          background: T.info_bg || '#e3f0fb', border: `1px solid #b8d8f0`,
          borderRadius: 10, padding: '10px 12px',
          fontSize: 12, color: '#1c5896', lineHeight: 1.4,
        }}>
          Halo <strong>Ruth Halawa</strong>. Anda mengelola siswa di cabang <strong>Gunungsitoli</strong>.
          Sebagai mentor Anda dapat melihat daftar siswa, mencatat presensi, dan mengelola jurnal &mdash;
          tetapi tidak dapat mengubah role, permission, atau cabang.
        </div>
      </div>

      {/* Stats trio */}
      <div style={{ padding: '12px 16px', display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 8 }}>
        <Stat n="22" l="Siswa di Cabang" color={T.teal700} />
        <Stat n="14" l="Presensi Saya" color={T.orange600} />
        <Stat n="2" l="Sesi Hari Ini" color={T.yellow700} />
      </div>

      {/* Menu */}
      <div style={{ padding: '4px 16px 16px', display: 'flex', flexDirection: 'column', gap: 6 }}>
        <SectionLabel>Pengelolaan</SectionLabel>
        <MenuCard icon="users" title="Daftar Siswa" sub="Read-only · 22 nama" />
        <MenuCard icon="clipboardCheck" title="Presensi Siswa" sub="Catat kehadiran kelas" />
        <MenuCard icon="userCog" title="Presensi Mentor" sub="Kehadiran &amp; sesi Anda" />

        <SectionLabel>Jurnal Harian</SectionLabel>
        <MenuCard icon="book-open" title="Porsi Alkitab" sub="Atur jadwal PL/PB" lock />
        <MenuCard icon="sparkles" title="Hafal Ayat Mingguan" sub="Ayat untuk siswa" lock />
        <MenuCard icon="heart" title="Jadwal Kehidupan" sub="Item kerohanian, pendidikan, karakter" lock />
        <MenuCard icon="fileText" title="Laporan Siswa" sub="Lihat progres harian" />

        <SectionLabel>Master Kelas</SectionLabel>
        <MenuCard icon="school" title="Master Kelas" sub="Lihat — read-only" lock />
      </div>

      <div style={{ flex: 1 }} />
      <BottomNav role="mentor" active="presensi" />
    </div>
  );
}

function Stat({ n, l, color }) {
  return (
    <div style={{
      background: '#fff', borderRadius: 12, padding: '12px 10px',
      border: `1px solid ${T.line}`, textAlign: 'center',
    }}>
      <div style={{ fontFamily: T.fontSans, fontSize: 22, fontWeight: 800, color, lineHeight: 1 }}>{n}</div>
      <div style={{ fontSize: 10, color: T.ink500, marginTop: 4, fontWeight: 600 }}>{l}</div>
    </div>
  );
}

function SectionLabel({ children }) {
  return (
    <div style={{
      fontSize: 10, fontWeight: 700, textTransform: 'uppercase',
      letterSpacing: '0.08em', color: T.ink500, marginTop: 12, marginBottom: 2,
      paddingLeft: 4,
    }}>{children}</div>
  );
}

function MenuCard({ icon, title, sub, lock }) {
  return (
    <div style={{
      background: '#fff', borderRadius: 12, padding: '10px 12px',
      border: `1px solid ${T.line}`, display: 'flex', alignItems: 'center', gap: 10,
    }}>
      <div style={{ width: 32, height: 32, borderRadius: 8, background: T.teal100, color: T.teal700, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
        <Icon name={icon} size={16} stroke={1.8} />
      </div>
      <div style={{ flex: 1, minWidth: 0 }}>
        <div style={{ fontSize: 13.5, fontWeight: 700, color: T.ink900, lineHeight: 1.2 }}>{title}</div>
        <div style={{ fontSize: 11, color: T.ink500, marginTop: 2 }}>{sub}</div>
      </div>
      {lock && (
        <Chip tone="warning" style={{ fontSize: 9, padding: '2px 6px' }}>
          <Icon name="keyRound" size={9} stroke={2.5} /> read-only
        </Chip>
      )}
      <Icon name="chevronRight" size={14} color={T.ink300} />
    </div>
  );
}

Object.assign(window, { MentorHome, MentorPresensi, MentorPanel, Stat, SectionLabel, MenuCard });

/* ──────── ui_kits/android/ScreensAdmin.jsx ──────── */
// ScreensAdmin.jsx — admin-specific screens

function AdminDashboard() {
  return (
    <div style={{ background: T.bg, minHeight: '100%', display: 'flex', flexDirection: 'column' }}>
      <SCTopBar title="Dashboard Admin" subtitle="KENDALI PENUH"
        right={<Icon name="bell" size={20} color="#fff" />} />

      <PermissionsBlock role="admin" perms={['manage_users','manage_roles','manage_cabangs','manage_blogs','create_blog','view_blogs','approve_payment']} />

      {/* Stat cards */}
      <div style={{ padding: '0 16px 12px', display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 10 }}>
        <BigStat icon="users" n="128" l="Total Pengguna" tone={T.teal700} />
        <BigStat icon="newspaper" n="46" l="Total Blog" tone={T.orange600} />
        <BigStat icon="mapPin" n="4" l="Cabang" tone={T.yellow700} />
        <BigStat icon="clipboardCheck" n="312" l="Presensi" tone={T.teal800} />
      </div>

      {/* Chart preview */}
      <div style={{ padding: '0 16px 12px' }}>
        <div style={{
          background: '#fff', borderRadius: 14, padding: 14, border: `1px solid ${T.line}`,
        }}>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
            <div style={{ fontSize: 13, fontWeight: 700, color: T.ink900 }}>Pengguna per Role</div>
            <div style={{ fontSize: 11, color: T.ink500 }}>30 hari</div>
          </div>
          {/* tiny stacked bar */}
          <div style={{ display: 'flex', height: 10, borderRadius: 6, overflow: 'hidden', marginTop: 12 }}>
            <div style={{ width: '60%', background: T.teal600 }} />
            <div style={{ width: '18%', background: T.orange500 }} />
            <div style={{ width: '12%', background: T.yellow500 }} />
            <div style={{ width: '10%', background: T.teal900 }} />
          </div>
          <div style={{ display: 'flex', gap: 12, marginTop: 10, fontSize: 11, color: T.ink700, flexWrap: 'wrap' }}>
            <LegendDot c={T.teal600}>Siswa 77</LegendDot>
            <LegendDot c={T.orange500}>Mentor 23</LegendDot>
            <LegendDot c={T.yellow500}>Fulltimer 15</LegendDot>
            <LegendDot c={T.teal900}>Admin 13</LegendDot>
          </div>
        </div>
      </div>

      {/* Latest activity */}
      <div style={{ padding: '0 16px 16px' }}>
        <div style={{ fontSize: 11, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', color: T.ink500, marginBottom: 6 }}>5 PRESENSI TERBARU</div>
        <div style={{ background: '#fff', borderRadius: 12, border: `1px solid ${T.line}` }}>
          {[
            ['14 Mei', '15:00 - 17:00', '7A Matematika', 'Gunungsitoli', 8],
            ['14 Mei', '15:00 - 17:00', '8B Bahasa', 'Kab. Nias', 6],
            ['13 Mei', '15:30 - 17:30', '9 IPA', 'Nias Selatan', 5],
            ['13 Mei', '15:00 - 16:30', '7B Matematika', 'Nias Utara', 7],
          ].map((r, i) => (
            <ListRow key={i}
              leading={<div style={{ fontSize: 10, fontFamily: T.fontMono, color: T.ink500, width: 44 }}>{r[0]}</div>}
              primary={r[2]}
              secondary={`${r[3]} · ${r[1]}`}
              badge={<Chip tone="cabang" style={{ fontSize: 9 }}>{r[4]}</Chip>} />
          ))}
        </div>
      </div>

      <div style={{ flex: 1 }} />
      <BottomNav role="admin" active="admin" />
    </div>
  );
}

function AdminUsers() {
  const users = [
    { name: 'Seri Waruwu', role: 'student', cabang: 'Kab. Nias', email: 'seri@scn.id' },
    { name: 'Putri Zalukhu', role: 'student', cabang: 'Gunungsitoli', email: 'putri@scn.id' },
    { name: 'Ruth Halawa', role: 'mentor', cabang: 'Gunungsitoli', email: 'ruth@scn.id' },
    { name: 'Daniel Lase', role: 'fulltimer', cabang: 'Gunungsitoli', email: 'daniel@scn.id' },
    { name: 'Andre Daeli', role: 'admin', cabang: '—', email: 'andre@scn.id' },
    { name: 'Yuni Gea', role: 'student', cabang: 'Nias Selatan', email: 'yuni@scn.id' },
  ];
  return (
    <div style={{ background: T.bg, minHeight: '100%', display: 'flex', flexDirection: 'column', position: 'relative' }}>
      <SCTopBar title="Pengguna" subtitle="128 TOTAL" right={<Icon name="search" size={20} color="#fff" />} />

      {/* Role filter chips */}
      <div style={{ padding: '12px 16px 4px', display: 'flex', gap: 6, overflowX: 'auto' }}>
        {['Semua', 'student', 'mentor', 'fulltimer', 'admin'].map((r, i) => (
          <div key={r} style={{
            padding: '6px 12px', borderRadius: 999, fontSize: 11, fontWeight: 700,
            background: i === 0 ? T.teal700 : '#fff',
            color: i === 0 ? '#fff' : T.ink700,
            border: `1px solid ${i === 0 ? T.teal700 : T.line}`,
            textTransform: r === 'Semua' ? 'none' : 'capitalize',
            flexShrink: 0,
          }}>{r}</div>
        ))}
      </div>

      <div style={{ padding: '8px 16px 16px' }}>
        <div style={{ background: '#fff', borderRadius: 12, border: `1px solid ${T.line}`, overflow: 'hidden' }}>
          {users.map((u, i) => (
            <ListRow key={i}
              leading={<Avatar name={u.name} role={u.role} size={36} />}
              primary={u.name}
              secondary={`${u.cabang} · ${u.email}`}
              badge={<RoleBadge role={u.role} />} />
          ))}
        </div>
      </div>

      <div style={{ flex: 1 }} />
      <AddFab />
      <BottomNav role="admin" active="admin" />
    </div>
  );
}

function AdminRoles() {
  const roles = [
    { name: 'admin', label: 'Administrator', count: 13, perms: ['manage_users','manage_roles','manage_cabangs','manage_blogs','create_blog','view_blogs','approve_payment'] },
    { name: 'fulltimer', label: 'Full-timer', count: 15, perms: ['manage_blogs','create_blog','view_blogs'] },
    { name: 'mentor', label: 'Mentor', count: 23, perms: ['create_blog','view_blogs','create_schedule'] },
    { name: 'student', label: 'Siswa', count: 77, perms: ['create_blog','view_blogs'] },
    { name: 'guest', label: 'Tamu publik', count: 0, perms: ['view_blogs'] },
  ];
  return (
    <div style={{ background: T.bg, minHeight: '100%', display: 'flex', flexDirection: 'column' }}>
      <SCTopBar title="Role & Permission" subtitle="HAK AKSES SISTEM" right={<Icon name="plus" size={20} color="#fff" />} />

      <div style={{ padding: '12px 16px 4px', fontSize: 12, color: T.ink500 }}>
        Setiap role memiliki kumpulan permission yang berbeda. Tap sebuah role untuk mengatur permission-nya.
      </div>

      <div style={{ padding: '8px 16px 16px', display: 'flex', flexDirection: 'column', gap: 8 }}>
        {roles.map(r => (
          <div key={r.name} style={{
            background: '#fff', borderRadius: 12, padding: 12, border: `1px solid ${T.line}`,
          }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 8 }}>
              <RoleBadge role={r.name} />
              <div style={{ flex: 1, fontSize: 13.5, fontWeight: 700, color: T.ink900 }}>{r.label}</div>
              <div style={{ fontSize: 11, color: T.ink500, fontFamily: T.fontMono }}>{r.count} user</div>
              <Icon name="edit" size={14} color={T.ink500} />
            </div>
            <div style={{ display: 'flex', flexWrap: 'wrap', gap: 4 }}>
              {r.perms.map(p => (
                <span key={p} style={{
                  fontSize: 10, fontWeight: 500, color: T.teal800,
                  background: T.teal50, border: `1px solid ${T.teal100}`,
                  padding: '2px 6px', borderRadius: 4, fontFamily: T.fontMono,
                }}>{p}</span>
              ))}
            </div>
          </div>
        ))}
      </div>

      <div style={{ flex: 1 }} />
      <BottomNav role="admin" active="admin" />
    </div>
  );
}

function AdminJurnalSetup() {
  return (
    <div style={{ background: T.bg, minHeight: '100%', display: 'flex', flexDirection: 'column' }}>
      <SCTopBar title="Jurnal · Hafal Ayat" subtitle="DAFTAR AYAT MINGGUAN"
        leading={<Icon name="arrowLeft" size={22} color="#fff" />}
        right={<Icon name="plus" size={20} color="#fff" />} />

      <div style={{ padding: '12px 16px 8px', display: 'flex', alignItems: 'center', gap: 8 }}>
        <div style={{ flex: 1, position: 'relative' }}>
          <input readOnly defaultValue="2026" style={{
            width: '100%', boxSizing: 'border-box', padding: '8px 10px',
            border: `1px solid ${T.line}`, borderRadius: 8, fontSize: 12, fontFamily: T.fontMono,
          }} />
        </div>
        <SelectFake value="Mei 2026" />
      </div>

      <div style={{ padding: '4px 16px 16px', display: 'flex', flexDirection: 'column', gap: 6 }}>
        {[
          { wk: 1, ref: '1 Korintus 13:4', body: 'Kasih itu sabar; kasih itu murah hati...', n: 22 },
          { wk: 2, ref: 'Yakobus 1:19', body: 'Setiap orang harus cepat untuk mendengar...', n: 19, active: true },
          { wk: 3, ref: 'Mazmur 23:1', body: 'TUHAN adalah gembalaku, takkan kekurangan...', n: 0 },
          { wk: 4, ref: 'Filipi 4:13', body: 'Segala perkara dapat kutanggung di dalam Dia...', n: 0 },
        ].map(v => (
          <div key={v.wk} style={{
            background: v.active ? T.yellow100 : '#fff',
            borderRadius: 12, padding: 12,
            border: `1px solid ${v.active ? T.yellow300 : T.line}`,
          }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 4 }}>
              <div style={{
                width: 24, height: 24, borderRadius: 6, background: v.active ? T.yellow500 : T.teal100,
                color: v.active ? T.teal900 : T.teal700,
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                fontSize: 11, fontWeight: 800,
              }}>{v.wk}</div>
              <div style={{ flex: 1, fontSize: 13.5, fontWeight: 800, color: T.teal800 }}>{v.ref}</div>
              <span style={{ fontSize: 10, color: T.ink500, fontFamily: T.fontMono }}>{v.n} hafal</span>
              <Icon name="edit" size={14} color={T.ink500} />
            </div>
            <div style={{ fontFamily: T.fontDisplay, fontSize: 14, color: T.ink900, lineHeight: 1.4, paddingLeft: 32 }}>
              "{v.body}"
            </div>
          </div>
        ))}
      </div>

      <div style={{ flex: 1 }} />
      <BottomNav role="admin" active="admin" />
    </div>
  );
}

function AdminCabang() {
  const branches = [
    { name: 'Gunungsitoli', alamat: 'Jl. Diponegoro, Gunungsitoli', kontak: '+62 822-7600-1234', users: 56 },
    { name: 'Kab. Nias', alamat: 'Gido, Kabupaten Nias', kontak: '+62 822-7600-1235', users: 31 },
    { name: 'Kab. Nias Selatan', alamat: 'Teluk Dalam', kontak: '+62 822-7600-1236', users: 22 },
    { name: 'Kab. Nias Utara', alamat: 'Lotu', kontak: '+62 822-7600-1237', users: 19 },
  ];
  return (
    <div style={{ background: T.bg, minHeight: '100%', display: 'flex', flexDirection: 'column' }}>
      <SCTopBar title="Cabang" subtitle="EMPAT WILAYAH" right={<Icon name="plus" size={20} color="#fff" />} />

      <div style={{ padding: '12px 16px 16px', display: 'flex', flexDirection: 'column', gap: 8 }}>
        {branches.map(b => (
          <div key={b.name} style={{
            background: '#fff', borderRadius: 14, padding: 14, border: `1px solid ${T.line}`,
            display: 'flex', alignItems: 'flex-start', gap: 12,
          }}>
            <IconChip><Icon name="mapPin" size={18} /></IconChip>
            <div style={{ flex: 1, minWidth: 0 }}>
              <div style={{ fontSize: 14, fontWeight: 700, color: T.ink900 }}>{b.name}</div>
              <div style={{ fontSize: 11.5, color: T.ink500, marginTop: 2 }}>{b.alamat}</div>
              <div style={{ fontSize: 11, color: T.ink500, marginTop: 2 }}>{b.kontak}</div>
              <div style={{ display: 'flex', alignItems: 'center', gap: 6, marginTop: 6 }}>
                <Chip tone="cabang" style={{ fontSize: 10 }}>{b.users} pengguna</Chip>
              </div>
            </div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
              <Icon name="edit" size={16} color={T.ink500} />
              <Icon name="trash" size={16} color={T.danger || '#c1352b'} />
            </div>
          </div>
        ))}
      </div>

      <div style={{ flex: 1 }} />
      <BottomNav role="admin" active="admin" />
    </div>
  );
}

function AdminNameTag() {
  return (
    <div style={{ background: T.bg, minHeight: '100%', display: 'flex', flexDirection: 'column' }}>
      <SCTopBar title="Generator Name Tag" subtitle="CETAK KARTU NAMA SISWA" />

      <div style={{ padding: '12px 16px 8px' }}>
        <div style={{ fontSize: 11, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', color: T.ink500, marginBottom: 6 }}>PARAMETER</div>
        <div style={{ background: '#fff', borderRadius: 12, padding: 12, border: `1px solid ${T.line}`, display: 'flex', flexDirection: 'column', gap: 10 }}>
          <Field label="Cabang"><SelectFake value="Gunungsitoli" /></Field>
          <Field label="Kelas"><SelectFake value="7A Matematika" /></Field>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 10 }}>
            <Field label="Ukuran"><SelectFake value="8.5 × 5.5 cm" /></Field>
            <Field label="Per halaman"><SelectFake value="10 kartu" /></Field>
          </div>
        </div>
      </div>

      <div style={{ padding: '4px 16px 8px' }}>
        <div style={{ fontSize: 11, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', color: T.ink500, marginBottom: 6 }}>PRATINJAU</div>
        <div style={{
          background: T.paper, borderRadius: 10, padding: 14,
          border: `1px solid ${T.line}`, position: 'relative', overflow: 'hidden',
        }}>
          {/* corner triangles motif from kartu nama */}
          <svg viewBox="0 0 100 70" preserveAspectRatio="none" style={{ position: 'absolute', top: 0, right: 0, width: 70, height: 50 }}>
            <polygon points="100,0 50,0 100,50" fill={T.orange500} />
            <polygon points="100,50 75,25 100,25" fill={T.teal800} />
            <polygon points="50,0 75,0 58,20" fill={T.yellow600} />
          </svg>
          <svg viewBox="0 0 100 70" preserveAspectRatio="none" style={{ position: 'absolute', bottom: 0, left: 0, width: 70, height: 50 }}>
            <polygon points="0,70 0,20 60,70" fill={T.orange500} />
            <polygon points="0,70 40,70 0,40" fill={T.teal800} />
          </svg>
          <div style={{ position: 'relative', display: 'flex', alignItems: 'center', gap: 8 }}>
            <img src="../../assets/logo.png" style={{ width: 32, height: 32, objectFit: 'contain' }} />
            <div>
              <div style={{ fontSize: 11, fontWeight: 800, letterSpacing: '0.5px', color: '#5d3a14' }}>STUDY CENTER</div>
              <div style={{ fontSize: 8, fontWeight: 700, color: T.teal800 }}>STUDY CENTER KABUPATEN NIAS</div>
              <div style={{ fontSize: 7, fontStyle: 'italic', color: T.teal800 }}>SECOND HOME FOR THE BETTER FUTURE</div>
            </div>
          </div>
          <div style={{ position: 'relative', height: 1, background: T.teal800, margin: '8px 0' }} />
          <div style={{ position: 'relative', fontSize: 10, lineHeight: 1.7, color: T.ink900 }}>
            <div><strong>Nama</strong> : Seri Waruwu</div>
            <div><strong>Kelas</strong> : 9A</div>
            <div><strong>Sekolah</strong> : SMP N 2 Gido</div>
          </div>
        </div>
      </div>

      <div style={{ padding: '8px 16px 16px', display: 'flex', gap: 8 }}>
        <button style={{ ...pillBtn(T.teal600), flex: 1, justifyContent: 'center', padding: '10px 12px', fontSize: 13 }}>
          <Icon name="download" size={14} color="#fff" stroke={2} /> Generate PDF
        </button>
        <button style={{ ...pillBtn('#fff'), color: T.teal700, border: `1px solid ${T.line}`, padding: '10px 12px' }}>
          Cetak
        </button>
      </div>

      <div style={{ flex: 1 }} />
      <BottomNav role="admin" active="admin" />
    </div>
  );
}

function BigStat({ icon, n, l, tone }) {
  return (
    <div style={{
      background: '#fff', borderRadius: 14, padding: 14,
      border: `1px solid ${T.line}`, boxShadow: T.sh[1],
    }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
        <Icon name={icon} size={18} color={tone} stroke={1.8} />
        <div style={{ fontSize: 11, color: T.ink500, fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.05em' }}>{l}</div>
      </div>
      <div style={{ fontFamily: T.fontSans, fontSize: 28, fontWeight: 800, color: tone, marginTop: 6, letterSpacing: '-0.01em' }}>{n}</div>
    </div>
  );
}

function LegendDot({ c, children }) {
  return (
    <div style={{ display: 'inline-flex', alignItems: 'center', gap: 4 }}>
      <div style={{ width: 8, height: 8, borderRadius: 2, background: c }} />
      {children}
    </div>
  );
}

Object.assign(window, {
  AdminDashboard, AdminUsers, AdminRoles, AdminJurnalSetup, AdminCabang, AdminNameTag,
  BigStat, LegendDot,
});
