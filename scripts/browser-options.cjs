// Let Playwright find the installed Chrome on Windows, macOS, or Linux.
// CHROME_PATH can select a portable/custom installation for every browser suite.
function chromeOptions(env = process.env) {
    const executablePath = env.CHROME_PATH?.trim();
    return {
        headless: true,
        ...(executablePath ? { executablePath } : { channel: 'chrome' }),
    };
}

module.exports = { chromeOptions };
