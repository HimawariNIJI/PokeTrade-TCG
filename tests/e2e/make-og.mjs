// Renders the social share image (1200x630) -> public/images/og-default.png
import { chromium } from '@playwright/test';

const html = `<!doctype html><html><head><meta charset="utf-8">
<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@600;700&display=swap" rel="stylesheet">
<style>
  * { margin:0; box-sizing:border-box; }
  body { width:1200px; height:630px; overflow:hidden; font-family:"Nunito",sans-serif;
    background:#0d1220; color:#fff; position:relative; }
  .mesh { position:absolute; inset:0;
    background:
      radial-gradient(40% 55% at 12% 18%, rgba(255,106,213,.45), transparent 70%),
      radial-gradient(38% 50% at 90% 10%, rgba(106,215,255,.4), transparent 70%),
      radial-gradient(50% 55% at 78% 95%, rgba(180,107,255,.4), transparent 70%); }
  .dots { position:absolute; inset:0; opacity:.5;
    background-image:radial-gradient(rgba(255,255,255,.08) 1.5px, transparent 1.5px);
    background-size:26px 26px; }
  .wrap { position:relative; height:100%; display:flex; flex-direction:column;
    justify-content:center; padding:84px; }
  .row { display:flex; align-items:center; gap:22px; margin-bottom:30px; }
  .ball { width:74px; height:74px; border-radius:9999px; position:relative; overflow:hidden;
    box-shadow:0 0 0 4px #0d1220, 0 0 0 6px rgba(255,255,255,.5); }
  .ball .top { position:absolute; left:0; right:0; top:0; height:50%;
    background:linear-gradient(90deg,#ff6ad5,#ffd86b,#6affc1,#6ad7ff,#b46bff); }
  .ball .bot { position:absolute; left:0; right:0; bottom:0; height:50%; background:#fff; }
  .ball .band{ position:absolute; left:0; right:0; top:calc(50% - 3px); height:6px; background:#0d1220; }
  .ball .btn { position:absolute; left:50%; top:50%; width:26px; height:26px; margin:-13px 0 0 -13px;
    border-radius:9999px; background:#fff; box-shadow:0 0 0 4px #0d1220; }
  .brand { font-family:"Fredoka"; font-weight:700; font-size:40px; letter-spacing:-.5px; }
  .brand .g { background:linear-gradient(90deg,#ff6ad5,#ffd86b,#6affc1,#6ad7ff,#b46bff);
    -webkit-background-clip:text; background-clip:text; color:transparent; }
  h1 { font-family:"Fredoka"; font-weight:700; font-size:96px; line-height:.98; letter-spacing:-2px; max-width:980px; }
  h1 .g { background:linear-gradient(90deg,#ff6ad5 10%,#ffd86b,#6affc1,#6ad7ff,#b46bff);
    -webkit-background-clip:text; background-clip:text; color:transparent; }
  p { margin-top:30px; font-size:34px; color:rgba(255,255,255,.82); font-weight:600; max-width:900px; }
  .chips { margin-top:40px; display:flex; gap:14px; }
  .chip { font-weight:700; font-size:22px; padding:12px 24px; border-radius:9999px;
    background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.18); }
</style></head>
<body>
  <div class="mesh"></div><div class="dots"></div>
  <div class="wrap">
    <div class="row">
      <div class="ball"><div class="top"></div><div class="bot"></div><div class="band"></div><div class="btn"></div></div>
      <div class="brand">Poke<span class="g">Trade</span></div>
    </div>
    <h1>Track the <span class="g">Prismatic Evolutions</span>.</h1>
    <p>Live card prices, real-card auctions, a digital gacha, and a trainer community.</p>
    <div class="chips"><span class="chip">Price Tracker</span><span class="chip">Auctions</span><span class="chip">Gacha</span><span class="chip">Forums</span></div>
  </div>
</body></html>`;

const b = await chromium.launch({ channel: 'chrome' });
const p = await b.newPage({ viewport: { width: 1200, height: 630 }, deviceScaleFactor: 1 });
await p.setContent(html, { waitUntil: 'networkidle' });
await p.waitForTimeout(700);
await p.screenshot({ path: 'public/images/og-default.png' });
await b.close();
console.log('og-default.png written');
