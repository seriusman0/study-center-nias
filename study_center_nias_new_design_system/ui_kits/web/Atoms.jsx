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
