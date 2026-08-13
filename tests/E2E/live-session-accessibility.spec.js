import {expect, test} from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

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

    expect(response?.status()).toBe(200);
    await expect(page).toHaveURL(/\/student(?:\/|$)/);
    await expect(page).not.toHaveURL(/\/teacher\/live-sessions/);
});

test('native classroom prejoin is keyboard operable and exposes accessible media controls', async ({page}) => {
    await login(page, 'teacher@mindigo.com');
    await page.goto('/teacher/live-sessions');
    const roomLink = page.locator('a[href*="/room"]').first();
    test.skip(await roomLink.count() === 0, 'No live native demo room is available.');
    await roomLink.click();

    const prejoin = page.locator('[data-prejoin]');
    await expect(prejoin).toHaveAttribute('role', 'dialog');
    await expect(prejoin).toHaveAttribute('aria-modal', 'true');
    await expect(page.locator('[data-prejoin-camera]')).toBeVisible();
    await expect(page.locator('[data-prejoin-microphone]')).toBeVisible();
    await expect(page.locator('[data-enter-room]')).toBeFocused();
    const accessibility = await new AxeBuilder({page}).include('[data-prejoin]').analyze();
    expect(accessibility.violations.filter(violation => ['critical', 'serious'].includes(violation.impact))).toEqual([]);
    await page.keyboard.press('Enter');
    await expect(prejoin).toBeHidden();
    await expect(page.locator('[data-toggle-captions]')).toHaveAttribute('aria-pressed', 'false');
});
