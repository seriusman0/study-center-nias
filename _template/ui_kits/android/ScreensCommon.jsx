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
