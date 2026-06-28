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
