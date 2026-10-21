import { beforeEach, test } from 'node:test';
import assert from 'node:assert/strict';

const root = { dataset: { adminTheme: 'rose', themeStorageKey: 'sn-store-admin-theme-7' } };
const values = new Map();
globalThis.document = { documentElement: root, querySelector: () => ({ setAttribute() {} }) };
globalThis.getComputedStyle = () => ({ getPropertyValue: () => '29 78 216' });
globalThis.window = { addEventListener() {}, removeEventListener() {} };
globalThis.localStorage = { setItem: (key, value) => values.set(key, value) };
const { default: themePicker } = await import('../../resources/js/admin-theme.js');

beforeEach(() => {
    values.clear();
    root.dataset.adminTheme = 'rose';
    globalThis.localStorage = { setItem: (key, value) => values.set(key, value) };
});

test('a single choice updates the page color and persists it for the current account', () => {
    const picker = themePicker();
    picker.init();
    picker.choose('blue');
    assert.equal(root.dataset.adminTheme, 'blue');
    assert.equal(picker.selected, 'blue');
    assert.equal(values.get('sn-store-admin-theme-7'), 'blue');
});

test('a picker starts with the previously restored page preference', () => {
    root.dataset.adminTheme = 'emerald';
    const picker = themePicker();
    picker.init();
    assert.equal(picker.selected, 'emerald');
});

test('an unsupported preference falls back to the store default', () => {
    const picker = themePicker();
    picker.choose('untrusted-value');
    assert.equal(root.dataset.adminTheme, 'rose');
    assert.equal(values.get('sn-store-admin-theme-7'), 'rose');
});

test('colors still change when browser storage is disabled', () => {
    globalThis.localStorage = { setItem() { throw new Error('Storage disabled'); } };
    const picker = themePicker();
    picker.choose('violet');
    assert.equal(root.dataset.adminTheme, 'violet');
    assert.match(picker.message, /tidak mengizinkan/);
});
