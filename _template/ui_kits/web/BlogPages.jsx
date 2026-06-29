// BlogPages.jsx — blog index + blog detail + comments

function BlogIndex({ openBlog }) {
  const [filter, setFilter] = React.useState({ q: '', cabang: '', sort: 'latest' });
  return (
    <main style={{ maxWidth: 1152, margin: '0 auto', padding: '40px 24px' }}>
      <h1 style={{ fontFamily: T.fontSans, fontSize: 36, fontWeight: 800, color: T.ink900, margin: '0 0 24px', letterSpacing: '-0.01em' }}>Blog</h1>

      {/* Filter bar */}
      <div style={{
        background: '#fff', borderRadius: T.r.md, padding: 16,
        boxShadow: T.sh[1], display: 'flex', gap: 10, marginBottom: 28,
        flexWrap: 'wrap', alignItems: 'center', border: `1px solid ${T.line}` }}>
        <div style={{ position: 'relative', flex: 1, minWidth: 200 }}>
          <input
            placeholder="Cari blog..."
            value={filter.q} onChange={e => setFilter({ ...filter, q: e.target.value })}
            style={{
              width: '100%', boxSizing: 'border-box',
              fontFamily: T.fontSans, fontSize: 14, padding: '10px 14px 10px 38px',
              border: `1px solid ${T.line}`, borderRadius: T.r.md, background: '#fff',
              outline: 'none' }} />
          <div style={{ position: 'absolute', left: 12, top: 11, color: T.ink300 }}>
            <Icon name="search" size={16} color={T.ink500} />
          </div>
        </div>
        <select value={filter.cabang} onChange={e => setFilter({ ...filter, cabang: e.target.value })}
          style={{ padding: '10px 14px', border: `1px solid ${T.line}`, borderRadius: T.r.md, fontFamily: T.fontSans, fontSize: 14, background: '#fff' }}>
          <option value="">Semua Cabang</option>
          {CABANGS.map(c => <option key={c.slug} value={c.slug}>{c.nama}</option>)}
        </select>
        <select value={filter.sort} onChange={e => setFilter({ ...filter, sort: e.target.value })}
          style={{ padding: '10px 14px', border: `1px solid ${T.line}`, borderRadius: T.r.md, fontFamily: T.fontSans, fontSize: 14, background: '#fff' }}>
          <option value="latest">Terbaru</option>
          <option value="popular">Terpopuler</option>
        </select>
        <Button tone="primary">Filter</Button>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 20 }}>
        {BLOGS.map(b => <BlogCard key={b.slug} blog={b} onClick={() => openBlog(b.slug)} />)}
      </div>
    </main>
  );
}

function BlogDetail({ slug, setRoute, user }) {
  const blog = BLOGS.find(b => b.slug === slug) || BLOGS[0];
  const [comments, setComments] = React.useState([
    { id: 1, author: 'Putri Zalukhu', role: 'student', date: '14 Mei 2026', body: 'Terima kasih sudah berbagi, Kak! Aku ikut belajar bareng minggu depan.' },
    { id: 2, author: 'Ruth Halawa', role: 'mentor', date: '14 Mei 2026', body: 'Ayo lanjut. Saya akan bawa soal latihan dari bab 4.' },
  ]);
  const [draft, setDraft] = React.useState('');
  const submit = (e) => {
    e.preventDefault();
    if (!draft.trim()) return;
    setComments([...comments, { id: Date.now(), author: user?.name || 'Tamu', role: user?.role || 'student', date: 'Hari ini', body: draft }]);
    setDraft('');
  };

  return (
    <main style={{ maxWidth: 768, margin: '0 auto', padding: '40px 24px' }}>
      <a onClick={() => setRoute('blog')} style={{ fontSize: 13, color: T.ink500, cursor: 'pointer', display: 'inline-flex', alignItems: 'center', gap: 4, marginBottom: 16 }}>
        <Icon name="arrowLeft" size={14} /> Kembali ke Blog
      </a>
      <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap', marginBottom: 14 }}>
        <Chip tone="cabang">{blog.cabang}</Chip>
        {blog.tags.map(t => <Chip key={t} tone="tag">{t}</Chip>)}
      </div>
      <h1 style={{ fontFamily: T.fontDisplay, fontWeight: 600, fontSize: 42, lineHeight: 1.15, color: T.ink900, margin: '0 0 20px', letterSpacing: '-0.01em', textWrap: 'pretty' }}>
        {blog.title}
      </h1>
      <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 28 }}>
        <Avatar name={blog.author} role={blog.role} size={44} />
        <div>
          <div style={{ fontWeight: 600, fontSize: 14, color: T.ink900 }}>{blog.author}</div>
          <div style={{ fontSize: 12, color: T.ink500, marginTop: 2 }}>{blog.role} · {blog.date}</div>
        </div>
      </div>

      {/* Hero image */}
      <div style={{
        height: 300, borderRadius: T.r.lg,
        background: `linear-gradient(135deg, ${T.teal700}, ${T.teal500})`,
        display: 'flex', alignItems: 'center', justifyContent: 'center',
        color: 'rgba(255,255,255,0.55)', marginBottom: 36 }}>
        <Icon name="book-open" size={96} color="rgba(255,255,255,0.65)" stroke={1.2} />
      </div>

      {/* Article body */}
      <div style={{ fontFamily: T.fontSans, fontSize: 17, lineHeight: 1.72, color: T.ink900 }}>
        <p style={{ marginTop: 0 }}>{blog.excerpt}</p>
        <p>
          Setiap sore di Cabang Gunungsitoli, sekitar dua puluh remaja berkumpul di ruang belajar untuk mendalami matematika dan bahasa Inggris. Sesi diawali doa singkat, lalu mentor membagi kelompok berdasarkan kelas dan kebutuhan.
        </p>
        <h2 style={{ fontFamily: T.fontSans, fontSize: 24, fontWeight: 700, color: T.teal800, marginTop: 32, marginBottom: 12 }}>Apa yang berbeda minggu ini?</h2>
        <p>
          Kami mencoba pendekatan baru: siswa kelas 9 mendampingi siswa kelas 7 — bukan untuk menggantikan mentor, tetapi untuk menumbuhkan rasa percaya diri dan tanggung jawab. Hasilnya menggembirakan.
        </p>
        <blockquote style={{
          fontFamily: T.fontDisplay, fontWeight: 500, fontSize: 20,
          color: T.ink700, borderLeft: `3px solid ${T.orange500}`, paddingLeft: 20, margin: '24px 0',
          lineHeight: 1.4 }}>
          "Setiap orang harus cepat untuk mendengar, tetapi lambat untuk berkata‑kata."
          <div style={{ fontFamily: T.fontSans, fontStyle: 'normal', fontSize: 14, color: T.ink500, marginTop: 8, fontWeight: 600 }}>— Yakobus 1:19</div>
        </blockquote>
        <p>
          Mari kita jaga ritme ini sampai akhir semester. Sampai jumpa lagi sore besok di rumah kedua kita.
        </p>
      </div>

      {/* Comments */}
      <section style={{ borderTop: `1px solid ${T.line}`, marginTop: 48, paddingTop: 32 }}>
        <h2 style={{ fontFamily: T.fontSans, fontSize: 22, fontWeight: 700, color: T.teal800, margin: '0 0 24px' }}>
          Komentar ({comments.length})
        </h2>
        {user ? (
          <form onSubmit={submit} style={{ marginBottom: 32 }}>
            <textarea value={draft} onChange={e => setDraft(e.target.value)} rows={3}
              placeholder="Tulis komentar..."
              style={{
                width: '100%', boxSizing: 'border-box', padding: 14,
                border: `1px solid ${T.line}`, borderRadius: T.r.md,
                fontFamily: T.fontSans, fontSize: 14, resize: 'none', outline: 'none',
                color: T.ink900 }} />
            <div style={{ marginTop: 8 }}><Button tone="primary">Kirim Komentar</Button></div>
          </form>
        ) : (
          <p style={{ fontSize: 14, color: T.ink500, marginBottom: 24 }}>
            <a onClick={() => setRoute('login')} style={{ color: T.teal700, fontWeight: 600, cursor: 'pointer', textDecoration: 'underline' }}>Masuk</a> untuk berkomentar.
          </p>
        )}
        <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
          {comments.map(c => (
            <div key={c.id} style={{ display: 'flex', gap: 12 }}>
              <Avatar name={c.author} role={c.role} size={36} />
              <div style={{ flex: 1, background: '#fff', borderRadius: T.r.md, padding: 14, border: `1px solid ${T.lineSoft}` }}>
                <div style={{ display: 'flex', alignItems: 'baseline', gap: 8, marginBottom: 4 }}>
                  <span style={{ fontWeight: 600, fontSize: 14, color: T.ink900 }}>{c.author}</span>
                  <RoleBadge role={c.role} />
                  <span style={{ marginLeft: 'auto', fontSize: 11, color: T.ink500 }}>{c.date}</span>
                </div>
                <div style={{ fontSize: 14, color: T.ink900, lineHeight: 1.5 }}>{c.body}</div>
              </div>
            </div>
          ))}
        </div>
      </section>
    </main>
  );
}

Object.assign(window, { BlogIndex, BlogDetail });
