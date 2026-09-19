import sharp from 'sharp';
import fs from 'fs';

const BASE = '/img/';
const IMGS = {
  'svc-web':       'svc-web.webp',
  'svc-apps':      'svc-apps.webp',
  'svc-ia':        'svc-ia.webp',
  'svc-rrss':      'svc-rrss.webp',
  'svc-content':   'svc-content.webp',
  'svc-design':    'svc-design.webp',
  'proj-boutique': 'proj-boutique.webp',
  'proj-delivery': 'proj-delivery.webp',
  'proj-clinica':  'proj-clinica.webp',
  'testi-maria':   'testi-maria.webp',
  'testi-carlos':  'testi-carlos.webp',
  'testi-ana':     'testi-ana.webp',
  'testi-luis':    'testi-luis.webp',
  'cta-equipo':    'cta-equipo.webp',
};
const SMALL = ['testi-maria', 'testi-carlos', 'testi-ana', 'testi-luis'];

fs.mkdirSync('public/img', { recursive: true });

for (const [name, file] of Object.entries(IMGS)) {
  const res = await fetch(BASE + file);
  const buf = Buffer.from(await res.arrayBuffer());
  const w = SMALL.includes(name) ? 240 : 1000;
  await sharp(buf)
    .resize({ width: w, withoutEnlargement: true })
    .webp({ quality: 78 })
    .toFile(`public/img/${name}.webp`);
  const kb = Math.round(fs.statSync(`public/img/${name}.webp`).size / 1024);
  console.log(`✔ ${name}.webp — ${kb} KB`);
}