// Home.jsx — landing: hero + cabang grid + recent blog

const CABANGS = [
  { slug: 'gunungsitoli', nama: 'Gunungsitoli', alamat: 'Jl. Diponegoro, Gunungsitoli', kontak: '+62 822-7600-1234', count: 12 },
  { slug: 'nias',        nama: 'Kab. Nias',     alamat: 'Gido, Kabupaten Nias',        kontak: '+62 822-7600-1235', count: 8 },
  { slug: 'nias-selatan',nama: 'Kab. Nias Selatan', alamat: 'Teluk Dalam, Nias Selatan',kontak: '+62 822-7600-1236', count: 6 },
  { slug: 'nias-utara',  nama: 'Kab. Nias Utara',   alamat: 'Lotu, Nias Utara',         kontak: '+62 822-7600-1237', count: 3 },
];
const BLOGS = [
  { slug: 'persiapan-uts-2026', title: 'Persiapan Ujian Tengah Semester di Cabang Gunungsitoli', cabang: 'Gunungsitoli', author: 'Seri Waruwu', role: 'student', date: '14 Mei 2026', tags: ['pendidikan','ujian'], excerpt: 'Minggu ini kami mengajak teman-teman SMP untuk belajar matematika bersama setiap sore.' },
  { slug: 'renungan-yakobus',   title: 'Renungan Yakobus 1:19 — Cepat Mendengar', cabang: 'Kab. Nias', author: 'Ruth Halawa', role: 'mentor', date: '12 Mei 2026', tags: ['renungan','remaja'], excerpt: 'Mendengar adalah keterampilan yang paling sulit dipraktikkan dalam komunitas remaja.' },
  { slug: 'futsal-bersama',     title: 'Turnamen Futsal Persaudaraan Antar Cabang', cabang: 'Kab. Nias Selatan', author: 'Andre Daeli', role: 'admin', date: '08 Mei 2026', tags: ['kegiatan','olahraga'], excerpt: 'Empat cabang berkumpul di lapangan Teluk Dalam untuk satu hari penuh persaudaraan.' },
];

function Home({ setRoute, openBlog }) {
  return (
    <main>
      {/* HERO */}
      <section style={{
        background: `linear-gradient(135deg, ${T.teal800}, ${T.teal600})`,
        color: '#fff', padding: '80px 24px', position: 'relative', overflow: 'hidden' }}>
        {/* decorative book stack illustration — flat triangles echoing logo */}
        <svg viewBox="0 0 200 200" style={{ position: 'absolute', right: -20, top: 20, width: 320, opacity: 0.10 }}>
          <path d="M30,60 L100,10 L170,60 L170,75 L100,25 L30,75 Z" fill={T.yellow500} />
          <rect x="50" y="80"  width="100" height="22" fill={T.yellow500} rx="2" />
          <rect x="50" y="106" width="100" height="22" fill={T.orange500} rx="2" />
          <rect x="40" y="135" width="120" height="40" fill={T.teal900} />
        </svg>
        <div style={{ maxWidth: 720, margin: '0 auto', textAlign: 'center', position: 'relative' }}>
          <div style={{ fontSize: 12, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.12em', color: T.yellow300, marginBottom: 14 }}>
            Komunitas belajar Nias
          </div>
          <h1 style={{
            fontFamily: T.fontDisplay, fontWeight: 600, fontSize: 56, lineHeight: 1.05,
            letterSpacing: '-0.01em', margin: 0 }}>
            Study Center <span style={{ color: T.yellow500 }}>Nias</span>
          </h1>
          <p style={{ fontSize: 18, lineHeight: 1.55, color: 'rgba(255,255,255,0.86)', maxWidth: 540, margin: '20px auto 28px' }}>
            Rumah kedua bagi remaja Nias — tempat belajar, bertumbuh, dan dikenal. Sebuah komunitas yang ramah orangtua, hangat untuk anak.
          </p>
          <div style={{ display: 'flex', gap: 12, justifyContent: 'center', flexWrap: 'wrap' }}>
            <Button tone="orange" size="lg" onClick={() => setRoute('blog')}>Baca Blog</Button>
            <Button tone="ghost" size="lg" onClick={() => setRoute('login')}
              style={{ background: 'rgba(255,255,255,0.10)', color: '#fff', borderColor: 'rgba(255,255,255,0.30)' }}>
              Bergabung
            </Button>
          </div>
        </div>
      </section>

      {/* CABANG */}
      <section style={{ maxWidth: 1152, margin: '0 auto', padding: '64px 24px 24px' }}>
        <div style={{ display: 'flex', alignItems: 'flex-end', justifyContent: 'space-between', marginBottom: 24 }}>
          <div>
            <div className="sc-eyebrow" style={{ fontSize: 12, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', color: T.teal700, marginBottom: 6 }}>EMPAT CABANG · NIAS</div>
            <h2 style={{ fontFamily: T.fontSans, fontSize: 30, fontWeight: 800, margin: 0, color: T.ink900, letterSpacing: '-0.01em' }}>Cabang kami</h2>
          </div>
          <a style={{ fontSize: 13, color: T.teal700, fontWeight: 600, cursor: 'pointer' }}>Lihat semua →</a>
        </div>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 16 }}>
          {CABANGS.map(c => (
            <Card key={c.slug} hover>
              <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 12 }}>
                <div style={{
                  width: 40, height: 40, borderRadius: T.r.md,
                  background: T.teal100, color: T.teal700,
                  display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                  <Icon name="map-pin" size={20} />
                </div>
                <Chip tone="cabang">{c.count} artikel</Chip>
              </div>
              <div style={{ fontFamily: T.fontSans, fontWeight: 700, fontSize: 16, color: T.ink900 }}>{c.nama}</div>
              <div style={{ fontSize: 12, color: T.ink500, marginTop: 4, lineHeight: 1.5 }}>{c.alamat}</div>
            </Card>
          ))}
        </div>
      </section>

      {/* BLOG */}
      <section style={{ maxWidth: 1152, margin: '0 auto', padding: '40px 24px 24px' }}>
        <div style={{ display: 'flex', alignItems: 'flex-end', justifyContent: 'space-between', marginBottom: 24 }}>
          <h2 style={{ fontFamily: T.fontSans, fontSize: 30, fontWeight: 800, margin: 0, color: T.ink900, letterSpacing: '-0.01em' }}>Blog terbaru</h2>
          <a onClick={() => setRoute('blog')} style={{ fontSize: 13, color: T.teal700, fontWeight: 600, cursor: 'pointer' }}>Lihat semua →</a>
        </div>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 20 }}>
          {BLOGS.map(b => <BlogCard key={b.slug} blog={b} onClick={() => openBlog(b.slug)} />)}
        </div>
      </section>
    </main>
  );
}

function Card({ children, hover, style, onClick }) {
  const [h, setH] = React.useState(false);
  return (
    <div onClick={onClick}
      onMouseEnter={() => hover && setH(true)} onMouseLeave={() => setH(false)}
      style={{
        background: '#fff', borderRadius: T.r.md,
        boxShadow: h ? T.sh[3] : T.sh[2],
        border: `1px solid ${h ? T.teal300 : T.line}`,
        padding: '18px 18px',
        transition: `box-shadow 200ms ${T.easeOut}, border-color 200ms ${T.easeOut}, transform 200ms ${T.easeOut}`,
        cursor: onClick ? 'pointer' : 'default',
        ...style }}>{children}</div>
  );
}

function BlogCard({ blog, onClick }) {
  const [h, setH] = React.useState(false);
  return (
    <article onClick={onClick}
      onMouseEnter={() => setH(true)} onMouseLeave={() => setH(false)}
      style={{
        background: '#fff', borderRadius: T.r.md, overflow: 'hidden',
        boxShadow: h ? T.sh[3] : T.sh[2],
        border: `1px solid ${h ? T.teal300 : T.line}`,
        cursor: 'pointer', display: 'flex', flexDirection: 'column',
        transition: `box-shadow 200ms ${T.easeOut}, border-color 200ms ${T.easeOut}` }}>
      <div style={{
        height: 168,
        background: `linear-gradient(135deg, ${T.teal700} 0%, ${T.teal500} 100%)`,
        display: 'flex', alignItems: 'center', justifyContent: 'center',
        color: 'rgba(255,255,255,0.5)', fontFamily: T.fontDisplay, fontSize: 56 }}>
        <Icon name={blog.tags.includes('renungan') ? 'book-open' : 'newspaper'} size={56} color="rgba(255,255,255,0.65)" stroke={1.4} />
      </div>
      <div style={{ padding: 18, display: 'flex', flexDirection: 'column', gap: 10, flex: 1 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
          <Chip tone="cabang">{blog.cabang}</Chip>
          <span style={{ fontSize: 11, color: T.ink500, fontFamily: T.fontMono }}>{blog.date}</span>
        </div>
        <h3 style={{
          fontFamily: T.fontSans, fontWeight: 700, fontSize: 17, lineHeight: 1.3,
          color: T.ink900, margin: 0, textWrap: 'pretty',
          display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical', overflow: 'hidden' }}>{blog.title}</h3>
        <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap' }}>
          {blog.tags.map(t => <Chip key={t} tone="tag">{t}</Chip>)}
        </div>
        <div style={{ marginTop: 'auto', display: 'flex', alignItems: 'center', gap: 10, paddingTop: 6 }}>
          <Avatar name={blog.author} role={blog.role} size={28} />
          <div>
            <div style={{ fontSize: 13, fontWeight: 600, color: T.ink900, lineHeight: 1.1 }}>{blog.author}</div>
            <div style={{ fontSize: 11, color: T.ink500, marginTop: 2 }}>{blog.role}</div>
          </div>
        </div>
      </div>
    </article>
  );
}

Object.assign(window, { Home, BlogCard, Card, BLOGS, CABANGS });
