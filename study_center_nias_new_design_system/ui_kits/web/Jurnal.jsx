// Jurnal.jsx — daily journal (Pembacaan Alkitab, Hafal Ayat, Jadwal Kehidupan)

const LIFE_ITEMS = {
  kerohanian: [
    { id: 1, label: 'Doa pagi' },
    { id: 2, label: 'Hadir di kebaktian remaja' },
  ],
  pendidikan: [
    { id: 3, label: 'Belajar di Study Center 1 jam' },
    { id: 4, label: 'Mengerjakan PR sekolah' },
    { id: 5, label: 'Membaca 10 halaman buku non-pelajaran' },
  ],
  karakter: [
    { id: 6, label: 'Membantu di rumah' },
    { id: 7, label: 'Menahan diri dari berkata kasar' },
  ],
};

function Jurnal({ user, onToast }) {
  const [state, setState] = React.useState({
    pl: true, pb: false, verse: false,
    life: [1, 3, 6],
  });
  const [streak, setStreak] = React.useState(12);

  const toggle = (type, id) => {
    setState(s => {
      const next = { ...s };
      if (type === 'life') {
        next.life = s.life.includes(id) ? s.life.filter(x => x !== id) : [...s.life, id];
      } else {
        next[type] = !s[type];
      }
      return next;
    });
    onToast?.('Tersimpan');
  };

  const today = 'Kamis, 14 Mei 2026';
  const verse = { ref: 'Yakobus 1:19', body: 'Setiap orang harus cepat untuk mendengar, tetapi lambat untuk berkata-kata dan juga lambat untuk marah.' };

  return (
    <main style={{ maxWidth: 720, margin: '0 auto', padding: '24px 16px 48px' }}>
      {/* Hero */}
      <div style={{
        background: `linear-gradient(135deg, ${T.teal800}, ${T.teal600})`,
        color: '#fff', borderRadius: T.r.lg, padding: 24, marginBottom: 16,
        boxShadow: T.sh[2], position: 'relative', overflow: 'hidden' }}>
        {/* Decorative scripture book */}
        <svg viewBox="0 0 100 100" style={{ position: 'absolute', right: -10, top: -10, width: 140, opacity: 0.10 }}>
          <rect x="20" y="35" width="60" height="14" fill={T.yellow500} rx="1" />
          <rect x="20" y="51" width="60" height="14" fill={T.orange500} rx="1" />
          <path d="M30,32 L50,18 L70,32" stroke={T.yellow300} strokeWidth="3" fill="none" />
        </svg>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', position: 'relative' }}>
          <div>
            <h1 style={{
              fontFamily: T.fontSans, fontWeight: 800, fontSize: 22, margin: 0,
              letterSpacing: '-0.01em' }}>Halo, {user?.name?.split(' ')[0] || 'Seri'} <span>👋</span></h1>
            <div style={{ fontSize: 13, color: 'rgba(255,255,255,0.82)', marginTop: 4 }}>{today}</div>
            {streak > 0 && (
              <div style={{
                display: 'inline-flex', alignItems: 'center', gap: 6,
                background: 'rgba(255,255,255,0.15)', color: T.yellow300,
                padding: '4px 10px', borderRadius: T.r.pill, fontSize: 12, fontWeight: 700,
                marginTop: 10, backdropFilter: 'blur(4px)' }}>
                <Icon name="flame" size={14} color={T.orange500} stroke={2} />
                Streak {streak} hari berturut-turut
              </div>
            )}
          </div>
          <div style={{ display: 'flex', gap: 4, alignItems: 'center' }}>
            <button style={iconBtnStyle}><Icon name="arrowLeft" size={16} /></button>
            <button style={iconBtnStyle}><Icon name="arrowRight" size={16} /></button>
          </div>
        </div>
      </div>

      {/* Section 1 — Pembacaan Alkitab */}
      <Section title="1. Pembacaan Alkitab">
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
          <CheckRow checked={state.pl} onToggle={() => toggle('pl')}
            title="Perjanjian Lama" sub="2 Tawarikh 14-16" />
          <CheckRow checked={state.pb} onToggle={() => toggle('pb')}
            title="Perjanjian Baru" sub="Roma 8:18-39" />
        </div>
      </Section>

      {/* Section 2 — Hafal Ayat */}
      <Section title="2. Hafal Ayat Mingguan" eyebrow="Minggu ke-2 · Mei 2026">
        <div style={{
          background: T.yellow100, border: `1px solid ${T.yellow300}`,
          borderRadius: T.r.md, padding: '14px 16px', marginBottom: 12 }}>
          <div style={{ fontFamily: T.fontSans, fontWeight: 800, fontSize: 14, color: T.teal800, marginBottom: 6 }}>
            {verse.ref}
          </div>
          <div style={{
            fontFamily: T.fontDisplay, fontSize: 17,
            lineHeight: 1.5, color: T.ink900, textWrap: 'pretty' }}>"{verse.body}"</div>
        </div>
        <CheckRow checked={state.verse} onToggle={() => toggle('verse')}
          title="Sudah hafal ayat minggu ini" />
      </Section>

      {/* Section 3 — Jadwal Kehidupan */}
      <Section title="3. Jadwal Kehidupan">
        {[
          ['kerohanian', 'Kerohanian'],
          ['pendidikan', 'Pendidikan'],
          ['karakter', 'Karakter'],
        ].map(([key, label]) => (
          <div key={key} style={{ marginBottom: 18 }}>
            <div style={{ fontSize: 11, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', color: T.ink500, marginBottom: 8 }}>
              {label}
            </div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
              {LIFE_ITEMS[key].map(item => (
                <CheckRow key={item.id} compact
                  checked={state.life.includes(item.id)}
                  onToggle={() => toggle('life', item.id)}
                  title={item.label} />
              ))}
            </div>
          </div>
        ))}
      </Section>
    </main>
  );
}

const iconBtnStyle = {
  background: 'rgba(255,255,255,0.15)', color: '#fff',
  border: 'none', width: 36, height: 36, borderRadius: 8, cursor: 'pointer',
  display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
};

function Section({ title, eyebrow, children }) {
  return (
    <section style={{
      background: '#fff', borderRadius: T.r.md, padding: 20,
      marginBottom: 14, boxShadow: T.sh[2], border: `1px solid ${T.line}` }}>
      <h2 style={{ fontFamily: T.fontSans, fontWeight: 700, fontSize: 18, color: T.teal800, margin: 0 }}>{title}</h2>
      {eyebrow && <div style={{ fontSize: 11, color: T.ink500, marginTop: 2, marginBottom: 12 }}>{eyebrow}</div>}
      {!eyebrow && <div style={{ marginTop: 12 }}></div>}
      {children}
    </section>
  );
}

function CheckRow({ checked, onToggle, title, sub, compact }) {
  return (
    <label onClick={onToggle} style={{
      display: 'flex', alignItems: compact ? 'center' : 'flex-start',
      gap: 12, padding: compact ? '8px 12px' : '12px 14px',
      border: `1px solid ${checked ? T.teal300 : T.line}`,
      background: checked ? T.teal50 : '#fff',
      borderRadius: T.r.md, cursor: 'pointer',
      transition: `background 160ms ${T.easeOut}, border-color 160ms ${T.easeOut}` }}>
      <div style={{
        width: 22, height: 22, borderRadius: 6,
        border: `2px solid ${checked ? T.teal600 : T.line}`,
        background: checked ? T.teal600 : '#fff',
        display: 'flex', alignItems: 'center', justifyContent: 'center',
        flexShrink: 0, marginTop: compact ? 0 : 1,
        transition: `background 160ms ${T.easeOut}, border-color 160ms ${T.easeOut}` }}>
        {checked && <Icon name="check" size={14} color="#fff" stroke={3} />}
      </div>
      <div style={{ flex: 1 }}>
        <div style={{ fontSize: 14, fontWeight: compact ? 500 : 600, color: T.ink900, lineHeight: 1.3 }}>{title}</div>
        {sub && <div style={{ fontSize: 13, color: T.ink500, marginTop: 2 }}>{sub}</div>}
      </div>
    </label>
  );
}

Object.assign(window, { Jurnal });
