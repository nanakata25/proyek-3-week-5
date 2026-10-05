/* Real-browser verification and screenshot capture; never uses a personal profile. */
const { chromium, request } = require('./playwright-runtime.cjs');
const assert = require('node:assert/strict');
const fs = require('node:fs/promises');
const path = require('node:path');

const root = path.resolve(__dirname, '..');
const output = path.join(root, 'evidence');
const shots = path.join(output, 'screenshots');
const executablePath = process.env.CHROME_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const t1 = process.env.T1_URL || 'http://127.0.0.1:8001';
const t2 = process.env.T2_URL || 'http://127.0.0.1:8002';
const profile = path.join(root, '.runtime', 'test-profile-t2');
const storageKey = 'modul4.tugas2.keranjang';
const config = { executablePath, headless: true, args: ['--no-first-run', '--disable-default-apps'] };
const viewport = { width: 1366, height: 900 };
const results = { startedAt: new Date().toISOString(), origins: { t1, t2 }, viewport, steps: [], screenshots: [], consoleErrors: [] };
let browser;
let persistent;

async function step(name, action) {
    const start = Date.now();
    try {
        const detail = await action();
        results.steps.push({ name, passed: true, ms: Date.now() - start, ...(detail || {}) });
        console.log(`PASS ${name}`);
    } catch (error) {
        results.steps.push({ name, passed: false, ms: Date.now() - start, error: error.message });
        console.error(`FAIL ${name}: ${error.message}`);
        throw error;
    }
}

function observe(page) {
    page.on('pageerror', error => results.consoleErrors.push({ url: page.url(), message: error.message }));
}

async function visit(page, url) {
    const response = await page.goto(url, { waitUntil: 'networkidle' });
    assert.ok(response && response.status() < 400, `GET ${url} failed: ${response?.status()}`);
    return response;
}

async function shot(page, file, description) {
    await page.screenshot({ path: path.join(shots, file), fullPage: true, animations: 'disabled' });
    results.screenshots.push({ file: `screenshots/${file}`, url: page.url(), description, capturedAt: new Date().toISOString() });
}

async function textEquals(page, selector, expected) {
    await page.waitForFunction(({ selector, expected }) => document.querySelector(selector)?.textContent.trim() === expected, { selector, expected });
    assert.equal((await page.locator(selector).textContent()).trim(), expected);
}

async function cartStored(page) {
    return page.evaluate(key => JSON.parse(localStorage.getItem(key) || '[]'), storageKey);
}

async function setCart(page, items) {
    await page.evaluate(({ key, items }) => localStorage.setItem(key, JSON.stringify(items)), { key: storageKey, items });
    await page.reload({ waitUntil: 'networkidle' });
}

async function main() {
    await fs.mkdir(shots, { recursive: true });
    await fs.mkdir(path.dirname(profile), { recursive: true });
    browser = await chromium.launch(config);
    const loginContext = await browser.newContext({ viewport });
    const login = await loginContext.newPage();
    observe(login);

    await step('T1: form login tersedia', async () => {
        await visit(login, `${t1}/login`);
        assert.equal(await login.locator('#password').getAttribute('type'), 'password');
        await shot(login, 't1-login.png', 'Form login sebelum pengisian.');
    });

    await step('T1: password salah ditolak dengan pesan umum', async () => {
        await login.locator('#username').fill('budi');
        await login.locator('#password').fill('salah123');
        await Promise.all([login.waitForURL(`${t1}/login`, { waitUntil: 'networkidle' }), login.getByRole('button', { name: /Masuk/ }).click()]);
        await login.getByRole('alert').waitFor();
        assert.equal((await login.getByRole('alert').innerText()).trim(), 'Username atau password salah.');
        assert.equal(await login.locator('#password').inputValue(), '');
        await shot(login, 't1-login-gagal.png', 'Login budi dengan password salah ditolak; password tidak diisi ulang.');
    });

    await step('T1: login benar membuka dashboard dan mengganti sesi', async () => {
        const before = (await loginContext.cookies(t1)).find(cookie => cookie.name === 'tugas1_session');
        await login.locator('#username').fill('budi');
        await login.locator('#password').fill('rahasia123');
        await Promise.all([login.waitForURL(`${t1}/dashboard`, { waitUntil: 'networkidle' }), login.getByRole('button', { name: /Masuk/ }).click()]);
        assert.match(await login.locator('body').innerText(), /Budi Santoso/);
        assert.match(await login.locator('body').innerText(), /Login berhasil/);
        const after = (await loginContext.cookies(t1)).find(cookie => cookie.name === 'tugas1_session');
        assert.ok(before && after, 'Expected session cookie exists before and after login.');
        assert.notEqual(before.value, after.value);
        assert.equal(after.httpOnly, true);
        await shot(login, 't1-login-berhasil.png', 'Dashboard sesudah login berhasil sebagai Budi Santoso.');
        return { sessionCookieRotated: true, sessionCookieHttpOnly: after.httpOnly };
    });

    await step('T1: middleware guest mengalihkan pengguna aktif dari login', async () => {
        await visit(login, `${t1}/login`);
        assert.equal(login.url(), `${t1}/dashboard`);
        await shot(login, 't1-dashboard.png', 'Dashboard saat pengguna bersesi mengunjungi login dan dialihkan kembali.');
    });

    await step('T1: konteks incognito tanpa cookie ditolak dari dashboard', async () => {
        const isolated = await browser.newContext({ viewport });
        assert.equal((await isolated.cookies()).length, 0);
        const page = await isolated.newPage();
        observe(page);
        const response = await visit(page, `${t1}/dashboard`);
        assert.equal(page.url(), `${t1}/login`);
        const redirects = [];
        for (let req = response.request(); req; req = req.redirectedFrom()) redirects.unshift(req.url());
        await shot(page, 't1-incognito-redirect.png', 'Konteks browser incognito baru mengakses dashboard dan diarahkan ke login.');
        await isolated.close();
        return { initialCookies: 0, redirectChain: redirects };
    });

    await step('T1: logout mengakhiri akses dashboard', async () => {
        await Promise.all([login.waitForURL(`${t1}/login`, { waitUntil: 'networkidle' }), login.getByRole('button', { name: /Logout/ }).click()]);
        assert.match(await login.locator('body').innerText(), /berhasil logout/);
        await shot(login, 't1-setelah-logout.png', 'Pesan berhasil logout pada form login.');
        await visit(login, `${t1}/dashboard`);
        assert.equal(login.url(), `${t1}/login`);
    });

    await step('T1: POST tanpa token CSRF ditolak', async () => {
        const api = await request.newContext();
        const response = await api.post(`${t1}/login`, { form: { username: 'budi', password: 'rahasia123' }, headers: { Origin: 'https://example.invalid', 'Sec-Fetch-Site': 'cross-site' } });
        assert.equal(response.status(), 419);
        await api.dispose();
        return { status: response.status(), requestHadToken: false, requestOrigin: 'https://example.invalid' };
    });
    await loginContext.close();

    persistent = await chromium.launchPersistentContext(profile, { ...config, viewport });
    let cart = persistent.pages()[0] || await persistent.newPage();
    observe(cart);
    await visit(cart, t2);
    await cart.evaluate(key => localStorage.removeItem(key), storageKey);
    await cart.reload({ waitUntil: 'networkidle' });

    await step('T2: katalog lima produk dari server tersedia', async () => {
        assert.equal(await cart.locator('.product-card').count(), 5);
        await textEquals(cart, '[data-cart-count]', '0');
        assert.match(await cart.locator('body').innerText(), /Rp 5.000/);
        assert.match(await cart.locator('body').innerText(), /Penghapus/);
        await shot(cart, 't2-daftar-barang.png', 'Katalog lima produk dan stok dari database MySQL.');
    });

    await step('T2: halaman keranjang kosong tanpa login', async () => {
        await visit(cart, `${t2}/keranjang`);
        assert.equal(await cart.locator('#empty-cart').isVisible(), true);
        assert.equal(await cart.locator('#filled-cart').isVisible(), false);
        await shot(cart, 't2-keranjang-kosong.png', 'Keranjang belum berisi barang dan bisa diakses tanpa akun.');
    });

    await step('T2: dua Buku dan satu Pulpen menghasilkan total Rp13.000', async () => {
        await visit(cart, t2);
        await cart.locator('[data-add="1"]').click({ clickCount: 2, delay: 80 });
        await cart.locator('[data-add="2"]').click();
        await textEquals(cart, '[data-cart-count]', '3');
        await visit(cart, `${t2}/keranjang`);
        await textEquals(cart, '#summary-total', 'Rp 13.000');
        assert.equal(await cart.locator('.cart-item').count(), 2);
        assert.deepEqual(await cartStored(cart), [{ id: 1, jumlah: 2 }, { id: 2, jumlah: 1 }]);
        await shot(cart, 't2-keranjang-terisi.png', 'Buku Tulis dua unit dan Pulpen satu unit: tiga item, total Rp13.000.');
    });

    await step('T2: refresh mempertahankan isi dan total', async () => {
        await cart.reload({ waitUntil: 'networkidle' });
        await textEquals(cart, '#summary-total', 'Rp 13.000');
        assert.deepEqual(await cartStored(cart), [{ id: 1, jumlah: 2 }, { id: 2, jumlah: 1 }]);
        await shot(cart, 't2-setelah-refresh.png', 'Isi dan total tetap sama setelah page.reload pada browser nyata.');
    });

    await step('T2: tab kedua membaca dan menyinkronkan keranjang', async () => {
        const second = await persistent.newPage();
        observe(second);
        await visit(second, `${t2}/keranjang`);
        await textEquals(second, '#summary-total', 'Rp 13.000');
        await second.locator('[data-id="1"] [data-action="increment"]').click();
        await textEquals(cart, '#summary-total', 'Rp 18.000');
        await second.locator('[data-id="1"] [data-action="decrement"]').click();
        await textEquals(cart, '#summary-total', 'Rp 13.000');
        await shot(second, 't2-tab-kedua.png', 'Tab kedua pada origin sama membaca keranjang dan sinkron antartab teruji.');
        await second.close();
    });

    await step('T2: profil atau konteks browser berbeda mempunyai keranjang kosong', async () => {
        const isolated = await browser.newContext({ viewport });
        const page = await isolated.newPage();
        observe(page);
        await visit(page, `${t2}/keranjang`);
        assert.equal(await page.locator('#empty-cart').isVisible(), true);
        assert.deepEqual(await cartStored(page), []);
        await shot(page, 't2-profil-berbeda.png', 'Konteks browser terisolasi mempunyai Local Storage sendiri dan keranjang kosong.');
        await isolated.close();
    });

    await step('T2: browser ditutup dan diluncurkan ulang mempertahankan data', async () => {
        await persistent.close();
        persistent = null;
        persistent = await chromium.launchPersistentContext(profile, { ...config, viewport });
        cart = persistent.pages()[0] || await persistent.newPage();
        observe(cart);
        await visit(cart, `${t2}/keranjang`);
        await textEquals(cart, '#summary-total', 'Rp 13.000');
        assert.deepEqual(await cartStored(cart), [{ id: 1, jumlah: 2 }, { id: 2, jumlah: 1 }]);
        await shot(cart, 't2-setelah-browser-dibuka-ulang.png', 'Browser persistent profile ditutup lalu diluncurkan ulang; keranjang tetap ada.');
        return { browserRelaunched: true, persistentProfile: '.runtime/test-profile-t2', stored: await cartStored(cart) };
    });

    await step('T2: tambah, kurang hingga nol, hapus, dan kosongkan bekerja', async () => {
        await cart.locator('[data-id="1"] [data-action="increment"]').click();
        await textEquals(cart, '#summary-total', 'Rp 18.000');
        await cart.locator('[data-id="1"] [data-action="decrement"]').click();
        await cart.locator('[data-id="2"] [data-action="decrement"]').click();
        assert.equal(await cart.locator('[data-id="2"]').count(), 0);
        await textEquals(cart, '#summary-total', 'Rp 10.000');
        await cart.locator('[data-id="1"] [data-action="remove"]').click();
        assert.equal(await cart.locator('#empty-cart').isVisible(), true);
        await visit(cart, t2);
        await cart.locator('[data-add="3"]').click();
        await cart.locator('[data-add="4"]').click();
        await visit(cart, `${t2}/keranjang`);
        await textEquals(cart, '#summary-total', 'Rp 6.500');
        await cart.locator('#clear-cart').click();
        assert.equal(await cart.locator('#empty-cart').isVisible(), true);
        assert.deepEqual(await cartStored(cart), []);
    });

    await step('T2: manipulasi jumlah 999 dikoreksi sesuai stok', async () => {
        await setCart(cart, [{ id: 1, jumlah: 999 }]);
        await textEquals(cart, '#summary-total', 'Rp 200.000');
        await textEquals(cart, '[data-id="1"] .quantity', '40');
        assert.match(await cart.locator('#storage-notice').innerText(), /melebihi stok/);
        assert.deepEqual(await cartStored(cart), [{ id: 1, jumlah: 40 }]);
        assert.equal(await cart.locator('[data-id="1"] [data-action="increment"]').isDisabled(), true);
        await shot(cart, 't2-manipulasi-999.png', 'Local Storage diubah menjadi jumlah 999 melalui API browser, kemudian reload membatasi ke stok 40 dengan peringatan.');
        return { original: [{ id: 1, jumlah: 999 }], repaired: await cartStored(cart), total: 'Rp 200.000' };
    });

    await step('T2: harga dan nama palsu di Local Storage diabaikan', async () => {
        await setCart(cart, [{ id: 1, jumlah: 2, harga: 1, nama: 'Palsu' }, { id: 2, jumlah: 1 }]);
        await textEquals(cart, '#summary-total', 'Rp 13.000');
        assert.deepEqual(await cartStored(cart), [{ id: 1, jumlah: 2 }, { id: 2, jumlah: 1 }]);
        assert.equal(await cart.getByText('Palsu', { exact: true }).count(), 0);
    });

    await step('T2: JSON rusak dipulihkan tanpa error JavaScript', async () => {
        await cart.evaluate(key => localStorage.setItem(key, '{broken'), storageKey);
        await cart.reload({ waitUntil: 'networkidle' });
        assert.equal(await cart.locator('#empty-cart').isVisible(), true);
        assert.match(await cart.locator('#storage-notice').innerText(), /tidak valid/);
        assert.deepEqual(await cartStored(cart), []);
        await shot(cart, 't2-json-rusak.png', 'JSON keranjang yang rusak dipulihkan ke keranjang kosong dengan pemberitahuan.');
        await setCart(cart, [{ id: 1, jumlah: 2 }, { id: 2, jumlah: 1 }]);
    });

    await step('T2: tampilan seluler tidak melampaui lebar viewport', async () => {
        await cart.setViewportSize({ width: 390, height: 844 });
        await visit(cart, t2);
        assert.equal(await cart.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth), true);
        await shot(cart, 't2-katalog-mobile.png', 'Katalog pada viewport 390×844 tanpa overflow horizontal.');
        await visit(cart, `${t2}/keranjang`);
        assert.equal(await cart.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth), true);
        await shot(cart, 't2-keranjang-mobile.png', 'Keranjang pada viewport 390×844 tanpa overflow horizontal.');
    });

    await step('T1 dan T2: tidak ada uncaught JavaScript error', async () => {
        assert.deepEqual(results.consoleErrors, []);
    });
}

main().catch(error => {
    results.fatalError = error.stack;
    process.exitCode = 1;
}).finally(async () => {
    await persistent?.close().catch(() => {});
    await browser?.close().catch(() => {});
    results.finishedAt = new Date().toISOString();
    results.passed = results.steps.filter(item => item.passed).length;
    results.failed = results.steps.filter(item => !item.passed).length;
    await fs.mkdir(output, { recursive: true });
    await fs.writeFile(path.join(output, 'tugas12-browser.json'), JSON.stringify(results, null, 2));
    console.log(`Wrote evidence/tugas12-browser.json: ${results.passed} passed, ${results.failed} failed.`);
});
