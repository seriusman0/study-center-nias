// Nav.jsx — top navbar (sticky), matches studycenter-app/resources/views/layouts/app.blade.php

function Nav({ route, setRoute, user, onLogout }) {
  const link = (key, label) => {
    const active = route === key || (key === 'blog' && route === 'blog-detail');
    return (
      <a onClick={() => setRoute(key)} key={key}
        style={{
          padding: '8px 14px', borderRadius: T.r.sm, cursor: 'pointer',
          fontSize: 14, fontWeight: 500,
          color: active ? T.yellow500 : 'rgba(255,255,255,0.92)',
          background: 'transparent',
          transition: `background 160ms ${T.easeOut}`,
        }}
        onMouseEnter={e => e.currentTarget.style.background = 'rgba(255,255,255,0.10)'}
        onMouseLeave={e => e.currentTarget.style.background = 'transparent'}>
        {label}
      </a>
    );
  };
  return (
    <nav style={{
      background: T.teal700, color: '#fff',
      position: 'sticky', top: 0, zIndex: 40,
      boxShadow: '0 1px 0 rgba(0,0,0,0.06), 0 2px 8px rgba(21,32,28,0.06)',
    }}>
      <div style={{
        maxWidth: 1152, margin: '0 auto', padding: '0 24px', height: 64,
        display: 'flex', alignItems: 'center', justifyContent: 'space-between',
      }}>
        <a onClick={() => setRoute('home')}
          style={{ display: 'flex', alignItems: 'center', gap: 10, cursor: 'pointer', textDecoration: 'none' }}>
          <img src="../../assets/logo.png" alt="" style={{ width: 32, height: 32, objectFit: 'contain', filter: 'brightness(0) invert(1)' }} />
          <span style={{ fontFamily: T.fontSans, fontWeight: 800, fontSize: 17, letterSpacing: '-0.01em', color: '#fff', lineHeight: 1 }}>
            Study Center <span style={{ color: T.yellow500 }}>Nias</span>
          </span>
        </a>
        <div style={{ display: 'flex', alignItems: 'center', gap: 4 }}>
          {link('blog', 'Blog')}
          {link('cabang', 'Cabang')}
          {user && user.role === 'student' && link('jurnal', 'Jurnal')}
          {user && link('tulis', 'Tulis')}
          {user ? (
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginLeft: 8 }}>
              <Avatar name={user.name} role={user.role} size={32} />
              <a onClick={onLogout} style={{
                padding: '8px 14px', cursor: 'pointer', fontSize: 13,
                color: 'rgba(255,255,255,0.65)',
              }}>Keluar</a>
            </div>
          ) : (
            <>
              {link('login', 'Masuk')}
              <button onClick={() => setRoute('login')} style={{
                marginLeft: 6, background: T.yellow500, color: T.teal900,
                fontFamily: T.fontSans, fontWeight: 700, fontSize: 14,
                padding: '8px 18px', border: 'none', borderRadius: T.r.md, cursor: 'pointer',
              }}>Daftar</button>
            </>
          )}
        </div>
      </div>
    </nav>
  );
}

function Footer() {
  return (
    <footer style={{ background: T.teal900, color: 'rgba(255,255,255,0.75)', padding: '32px 24px', marginTop: 48 }}>
      <div style={{ maxWidth: 1152, margin: '0 auto', textAlign: 'center', fontFamily: T.fontSans, fontSize: 13 }}>
        <div style={{ color: T.yellow500, fontWeight: 700, marginBottom: 4 }}>Study Center Nias</div>
        <div>Gunungsitoli · Kab. Nias · Kab. Nias Selatan · Kab. Nias Utara</div>
        <div style={{ marginTop: 12, color: 'rgba(255,255,255,0.40)', fontSize: 12 }}>© 2026 Study Center Nias · Second home for the better future</div>
      </div>
    </footer>
  );
}

Object.assign(window, { Nav, Footer });
