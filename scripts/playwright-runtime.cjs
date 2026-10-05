// npm install supplies Playwright on other computers; the fallback is a local
// authoring runtime and is never required by the Laravel applications.
try { module.exports = require('playwright'); }
catch (error) {
    if (error.code !== 'MODULE_NOT_FOUND') throw error;
    const path = require('node:path');
    const home = process.env.USERPROFILE || process.env.HOME;
    module.exports = require(process.env.PLAYWRIGHT_MODULE || path.join(home, '.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright'));
}
