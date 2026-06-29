// SignIn.jsx — auth card. Matches studycenter-app/resources/views/auth/login.blade.php

function SignIn({ onSignIn, setRoute }) {
  const [email, setEmail] = React.useState('seri@studycenter.id');
  const [password, setPassword] = React.useState('demo1234');
  const [role, setRole] = React.useState('student'); // pick role for demo

  const submit = (e) => {
    e.preventDefault();
    onSignIn({
      name: role === 'mentor' ? 'Ruth Halawa' : role === 'admin' ? 'Andre Daeli' : 'Seri Waruwu',
      role,
      email,
    });
  };

  return (
    <main style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '64px 16px', minHeight: '70vh' }}>
      <div style={{
        background: '#fff', borderRadius: T.r.lg, boxShadow: T.sh[3],
        padding: 36, width: '100%', maxWidth: 420, border: `1px solid ${T.line}`,
      }}>
        <div style={{ textAlign: 'center', marginBottom: 28 }}>
          <img src="../../assets/logo.png" alt="" style={{ width: 56, height: 56, objectFit: 'contain', margin: '0 auto 12px', display: 'block' }} />
          <h1 style={{ fontFamily: T.fontSans, fontSize: 24, fontWeight: 800, color: T.teal800, margin: 0, letterSpacing: '-0.01em' }}>Masuk</h1>
          <div style={{ fontSize: 14, color: T.ink500, marginTop: 6 }}>Masuk ke akun Study Center Nias</div>
        </div>

        {/* Google */}
        <button type="button" style={{
          width: '100%', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10,
          padding: '12px 16px', border: `1px solid ${T.line}`, borderRadius: T.r.md,
          background: '#fff', fontFamily: T.fontSans, fontSize: 14, fontWeight: 600,
          cursor: 'pointer', marginBottom: 14,
        }}>
          <svg width="18" height="18" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
          </svg>
          Masuk dengan Google
        </button>

        <div style={{ display: 'flex', alignItems: 'center', gap: 10, margin: '14px 0' }}>
          <div style={{ flex: 1, height: 1, background: T.line }} />
          <span style={{ fontSize: 11, color: T.ink500, fontFamily: T.fontMono }}>ATAU</span>
          <div style={{ flex: 1, height: 1, background: T.line }} />
        </div>

        <form onSubmit={submit} style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
          <Field label="Email atau Username">
            <input value={email} onChange={e => setEmail(e.target.value)} style={inputStyle} />
          </Field>
          <Field label="Password">
            <input type="password" value={password} onChange={e => setPassword(e.target.value)} style={inputStyle} />
          </Field>
          {/* Demo role picker */}
          <div style={{ background: T.bg, border: `1px dashed ${T.line}`, borderRadius: T.r.md, padding: 12 }}>
            <div style={{ fontSize: 11, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', color: T.ink500, marginBottom: 6 }}>Demo · pilih role</div>
            <div style={{ display: 'flex', gap: 6 }}>
              {['student', 'mentor', 'admin'].map(r => (
                <button key={r} type="button" onClick={() => setRole(r)}
                  style={{
                    flex: 1, padding: '8px 6px', border: `1px solid ${role === r ? T.teal600 : T.line}`,
                    background: role === r ? T.teal50 : '#fff', cursor: 'pointer',
                    fontFamily: T.fontSans, fontSize: 12, fontWeight: 600,
                    color: role === r ? T.teal800 : T.ink700, borderRadius: T.r.sm, textTransform: 'capitalize',
                  }}>
                  {r}
                </button>
              ))}
            </div>
          </div>
          <label style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 13, color: T.ink700 }}>
            <input type="checkbox" defaultChecked /> Ingat saya
          </label>
          <Button tone="primary" size="lg" style={{ width: '100%', justifyContent: 'center' }}>Masuk</Button>
        </form>

        <div style={{ textAlign: 'center', fontSize: 13, color: T.ink500, marginTop: 20 }}>
          Belum punya akun?{' '}
          <a style={{ color: T.teal700, fontWeight: 600, cursor: 'pointer', textDecoration: 'underline' }}>Daftar</a>
        </div>
      </div>
    </main>
  );
}

function Field({ label, children }) {
  return (
    <div>
      <label style={{ display: 'block', fontSize: 13, fontWeight: 600, color: T.ink900, marginBottom: 6 }}>{label}</label>
      {children}
    </div>
  );
}
const inputStyle = {
  width: '100%', boxSizing: 'border-box',
  fontFamily: T.fontSans, fontSize: 14, padding: '11px 14px',
  border: `1px solid ${T.line}`, borderRadius: T.r.md,
  background: '#fff', color: T.ink900, outline: 'none',
};

Object.assign(window, { SignIn });
