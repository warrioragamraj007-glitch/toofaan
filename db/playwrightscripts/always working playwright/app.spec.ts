// test.spec.js or test.spec.ts (if using TypeScript)
const { test, expect } = require('@playwright/test');
const baseURL = process.env.BASE_URL;


test('check if index.html has title !', async ({ page }) => {
  // Navigate to the index.html page
  await page.goto(baseURL);

  // Get the title of the page
  const title = await page.title();

  // Assert the title
  expect(title).toBe('Hello KMIT!');
});

test('check if index.html has ti !', async ({ page }) => {
  // Navigate to the index.html page
  await page.goto(baseURL);

  // Get the title of the page
  const title = await page.title();

  // Assert the title
  expect(title).toBe('Hello KMIT!');
});
