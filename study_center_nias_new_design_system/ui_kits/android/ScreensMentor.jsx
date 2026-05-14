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
