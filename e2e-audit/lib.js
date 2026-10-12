const fs = require('fs');
const path = require('path');

const BASE = 'http://127.0.0.1:8000';
const DIR = __dirname;
const SHOTS = path.join(DIR, 'screenshots');
const STATE = path.join(DIR, 'state');
[SHOTS, STATE].forEach((d) => fs.mkdirSync(d, { recursive: true }));

const NOTE_FILE = path.join(DIR, 'notes.json');
const LOG_FILE = path.join(DIR, 'network-log.json');

function note(entry) {
  let arr = [];
  try { arr = JSON.parse(fs.readFileSync(NOTE_FILE, 'utf8')); } catch (e) { arr = []; }
  arr.push({ at: new Date().toISOString(), ...entry });
  fs.writeFileSync(NOTE_FILE, JSON.stringify(arr, null, 2));
}

function netlog(entry) {
  let arr = [];
  try { arr = JSON.parse(fs.readFileSync(LOG_FILE, 'utf8')); } catch (e) { arr = []; }
  arr.push({ at: new Date().toISOString(), ...entry });
  fs.writeFileSync(LOG_FILE, JSON.stringify(arr, null, 2));
}

const statePath = (role) => path.join(STATE, `${role}.json`);

async function shot(page, name) {
  const safe = String(name).replace(/[^\w.-]+/g, '_');
  const rel = `screenshots/${safe}.png`;
  await page.screenshot({ path: path.join(DIR, rel), fullPage: true });
  return rel;
}

/** Kumpulkan console error + request gagal untuk sebuah halaman. */
function watchPage(page, label) {
  const consoleMsgs = [];
  const failed = [];
  page.on('console', (m) => {
    if (m.type() === 'error' || m.type() === 'warning') {
      consoleMsgs.push({ type: m.type(), text: m.text().slice(0, 300) });
    }
  });
  page.on('pageerror', (e) => consoleMsgs.push({ type: 'pageerror', text: String(e).slice(0, 300) }));
  page.on('response', (r) => {
    if (r.status() >= 400) failed.push({ status: r.status(), url: r.url() });
  });
  return {
    consoleMsgs,
    failed,
    flush() {
      netlog({ page: label, console: consoleMsgs, failedRequests: failed });
      return { consoleMsgs, failed };
    },
  };
}

/** Login via UI sekali, simpan storageState. Mengembalikan status login. */
async function loginAndSave(browser, role, email, password) {
  const ctx = await browser.newContext({ baseURL: BASE, viewport: { width: 1366, height: 768 } });
  const page = await ctx.newPage();
  let http = null;
  try {
    await page.goto('/login');
    await page.fill('#email', email);
    await page.fill('#password', password);
    const [resp] = await Promise.all([
      page.waitForResponse((r) => r.url().includes('/login') && r.request().method() === 'POST'),
      page.click('button[type="submit"]'),
    ]);
    http = resp.status();
    await page.waitForLoadState('domcontentloaded');
    const landed = page.url();
    const onDash = landed.includes('/dashboard') || landed.endsWith('/');
    if (onDash) await ctx.storageState({ path: statePath(role) });
    return { http, landed, onDash };
  } finally {
    await ctx.close();
  }
}

/**
 * Form utama halaman (layout punya <form> logout sebagai form pertama,
 * jadi jangan pakai form.first()).
 */
function mainForm(page) {
  return page
    .locator('form:has([name="athlete_id"]), form:has([name="conclusion"]), form:has(#fieldName)')
    .last();
}

/** Isi otomatis semua kontrol pada form aktif. */
async function fillForm(page, opts = {}) {
  const { values = {}, rating, marker } = opts;
  const form = mainForm(page);
  if ((await form.count()) === 0) {
    note({ id: 'fillForm', scenario: 'form utama tidak ditemukan', actual: page.url(), status: 'INFO' });
    return null;
  }

  const selAthlete = form.locator('select[name="athlete_id"]');
  if ((await selAthlete.count()) > 0) {
    const opt = selAthlete.locator('option', { hasText: 'QA_TEST_' }).first();
    if ((await opt.count()) > 0) {
      await selAthlete.selectOption(await opt.getAttribute('value'));
      await page.waitForTimeout(120);
    }
  }

  const controls = await form.locator('input, select, textarea').all();
  for (const el of controls) {
    const tag = (await el.evaluate((n) => n.tagName)).toLowerCase();
    const name = (await el.getAttribute('name')) || '';
    const type = ((await el.getAttribute('type')) || '').toLowerCase();
    if (!name || ['file', 'hidden', 'submit', 'button', 'image'].includes(type)) continue;
    // lewati field readonly/disabled (mis. kolom hasil otomatis)
    if ((await el.getAttribute('readonly')) !== null) continue;
    if ((await el.getAttribute('disabled')) !== null) continue;

    if (values[name] !== undefined) {
      await el.fill(String(values[name]));
      continue;
    }
    if (type === 'radio' || type === 'checkbox') {
      if (!(await el.isChecked())) await el.check().catch(() => {});
      continue;
    }
    if (tag === 'select') {
      const cur = await el.inputValue();
      if (!cur) {
        const optsAll = await el.locator('option').all();
        for (const o of optsAll) {
          const v = await o.getAttribute('value');
          if (v) { await el.selectOption(v); break; }
        }
      }
      continue;
    }
    if (type === 'date') { if (!(await el.inputValue())) await el.fill('2005-01-01'); continue; }
    if (type === 'datetime-local') { if (!(await el.inputValue())) await el.fill('2026-01-01T09:00'); continue; }
    if (type === 'number') {
      const min = parseFloat(await el.getAttribute('min'));
      const max = parseFloat(await el.getAttribute('max'));
      let v = defaultNumber(name);
      if (!Number.isNaN(min) && v < min) v = min;
      if (!Number.isNaN(max) && v > max) v = max;
      await el.fill(String(v));
      continue;
    }
    if (type === 'email') { await el.fill('qa_test@example.com'); continue; }
    if (type === 'tel') { await el.fill('08123456789'); continue; }
    if (type === 'password') { await el.fill('Password123!'); continue; }
    const cur = await el.inputValue();
    if (cur) continue; // jangan timpa nilai yang sudah terisi (mis. nama atlet)
    await el.fill(defaultText(name, marker));
  }

  if (rating) {
    const rSel = form.locator('select[name="rating"]');
    if ((await rSel.count()) > 0) {
      const labels = await rSel.locator('option').allTextContents();
      const hit = labels.find((l) => l.trim() === rating);
      if (hit) await rSel.selectOption({ label: hit });
      else note({ id: 'fill', scenario: `rating "${rating}" tidak tersedia`, actual: labels.join('|'), status: 'INFO' });
    }
  }
}

function defaultNumber(name) {
  const n = (name || '').toLowerCase();
  if (n.includes('distance') || n.includes('jarak')) return 1500;
  if (n.includes('height') || n.includes('tinggi')) return 175;
  if (n.includes('weight') || n.includes('berat')) return 70;
  if (n === 'age' || n.includes('usia')) return 20;
  if (n.includes('time') || n.includes('waktu')) return 12;
  if (n.includes('count') || n.includes('repetisi') || n.includes('push') || n.includes('sit')) return 20;
  return 10;
}

function defaultText(name, marker) {
  const n = (name || '').toLowerCase();
  if (n === 'name') return 'QA_TEST_Isian_Form';
  if (n === 'conclusion' || n === 'recommendations' || n === 'notes') return marker || 'QA_TEST_kesimpulan';
  if (n.includes('date')) return '2026-01-01';
  return marker || 'QA_TEST_isian';
}

module.exports = {
  BASE, SHOTS, STATE, note, netlog, shot, statePath, loginAndSave, fillForm, watchPage, mainForm,
};
