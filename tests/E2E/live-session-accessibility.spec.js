import {expect, test} from '@playwright/test';

async function login(page, email) {
    await page.goto('/login');
    await page.getByLabel(/email/i).fill(email);
    await page.locator('input[type="password"]').fill('123456');
    await page.locator('#loginBtn').click();
}

test('teacher can reach the live-session workspace with keyboard-visible controls', async ({page}) => {
    await login(page, 'teacher@mindigo.com');
    await page.goto('/teacher/live-sessions');

    await expect(page).toHaveURL(/teacher\/live-sessions/);
    await expect(page.locator('body')).toBeVisible();
    await page.keyboard.press('Tab');
    await expect(page.locator(':focus')).toBeVisible();
});

test('student cannot cross into teacher live-session routes', async ({page}) => {
    await login(page, 'student@mindigo.com');
    const response = await page.goto('/teacher/live-sessions');

    expect(response?.status()).toBe(403);
});
