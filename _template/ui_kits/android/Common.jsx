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
