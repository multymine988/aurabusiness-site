import https from 'https';

export default async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
  res.setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');

  if (req.method === 'OPTIONS') return res.status(200).end();
  if (req.method !== 'POST') return res.status(405).json({ error: 'Method not allowed' });

  let body;
  try { body = JSON.parse(req.body); } catch { return res.status(400).json({ error: 'Invalid JSON' }); }

  const { username } = body;
  if (!username || username.trim().length < 3) return res.status(400).json({ error: 'Missing or invalid username (min 3 characters)' });

  const YT_API_KEY = process.env.YOUTUBE_API_KEY;
  const u = encodeURIComponent(username.trim());

  try {
    const results = await Promise.allSettled([
      checkInstagram(u),
      checkTikTok(u),
      checkYouTube(u, YT_API_KEY)
    ]);

    const labels = ['Instagram', 'TikTok', 'YouTube'];
    const checked = [];
    const resultsList = [];

    results.forEach((r, i) => {
      if (r.status === 'fulfilled' && r.value) {
        checked.push(labels[i]);
        resultsList.push(`${labels[i]} ✅`);
      } else {
        resultsList.push(`${labels[i]} ❌`);
      }
    });

    console.log(`[FOLLOW] ${username.trim()} — ${resultsList.join(' | ')}`, new Date().toISOString());

    if (checked.length > 0) {
      return res.json({
        verified: true,
        message: `Compte trouvé sur ${checked.join(' & ')}. Follow vérifié !`
      });
    }

    return res.json({
      verified: false,
      message: 'Aucun compte trouvé avec ce pseudo sur Instagram, TikTok ou YouTube. Vérifie ton pseudo et réessaie.'
    });
  } catch (err) {
    console.error('Verification error:', err);
    return res.json({ verified: false, message: 'Erreur de vérification. Réessaie dans quelques instants.' });
  }
}

function checkInstagram(u) {
  return profileExists(`https://www.instagram.com/${u}/`);
}

function checkTikTok(u) {
  return profileExists(`https://www.tiktok.com/@${u}`);
}

function checkYouTube(u, apiKey) {
  if (apiKey) {
    return fetchJSON(`https://www.googleapis.com/youtube/v3/search?part=snippet&q=${u}&type=channel&key=${apiKey}`)
      .then(d => d && d.items && d.items.length > 0);
  }
  return profileExists(`https://www.youtube.com/@${u}`);
}

function profileExists(url) {
  return new Promise((resolve) => {
    const req = https.get(url, {
      headers: { 'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36' },
      timeout: 8000
    }, (res) => {
      let data = '';
      res.on('data', chunk => { data += chunk; if (data.length > 10000) req.destroy(); });
      res.on('end', () => {
        const lower = data.toLowerCase();
        if (res.statusCode === 404) return resolve(false);
        if (lower.includes('page not found') || lower.includes('cette page n\'existe pas') || lower.includes('couldn\'t find') || lower.includes('sorry, this page')) return resolve(false);
        resolve(true);
      });
    });
    req.on('error', () => resolve(false));
    req.on('timeout', () => { req.destroy(); resolve(false); });
  });
}

function fetchJSON(url) {
  return new Promise((resolve) => {
    https.get(url, { headers: { 'User-Agent': 'AuraBusiness/1.0' }, timeout: 8000 }, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => { try { resolve(JSON.parse(data)); } catch { resolve(null); } });
    }).on('error', () => resolve(null));
  });
}
