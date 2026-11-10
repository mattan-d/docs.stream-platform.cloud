const { chromium } = require('playwright');

(async () => {

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  const url = process.argv[2];
  const output = process.argv[3];

  // כל הפרמטרים אחרי OUTPUT הם selectors ללחיצה
  const selectors = process.argv.slice(4);

  // כניסה לדף login
  await page.goto('https://dev.video/login');

  await page.fill('#stream-form-username', 'ori.nevo.dor');
  await page.fill('#stream-form-password', 'ori.nevo.dor');

  await page.click('button[type=submit]');

  // מעבר לדף המבוקש
  await page.goto(url);
  await page.waitForLoadState('networkidle');

  // לחיצה על האלמנטים לפי הסדר
  for (const selector of selectors) {

    console.log('Clicking:', selector);

    await page.waitForSelector(selector, { timeout: 5000 });

    await page.click(selector);

    await page.waitForTimeout(1000); // נותן זמן ל-UI להגיב
  }

  // צילום מסך
  await page.screenshot({
    path: output,
  });

  await browser.close();

})();