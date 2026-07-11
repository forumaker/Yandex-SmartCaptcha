import app from 'flarum/forum/app';
import extendAuthModalsWithYandexSmartCaptcha from './extendAuthModals';
import loadSmartCaptcha from './utils/loadSmartCaptcha';

app.initializers.add('forumaker-yandex-smart-captcha', () => {
  extendAuthModalsWithYandexSmartCaptcha();

  const isEnabled =
    !!app.forum.attribute('forumaker-yandex-smart-captcha.signin') ||
    !!app.forum.attribute('forumaker-yandex-smart-captcha.signup');
  const siteKey = app.forum.attribute<string>('forumaker-yandex-smart-captcha.site_key');

  if (isEnabled && siteKey) {
    // Preload the SmartCaptcha script right after the forum boots, so it's
    // already cached by the time the login/signup modal opens. This only
    // fetches the JS file once (async, non-blocking) — no verification
    // requests happen until a widget is actually rendered in a modal.
    loadSmartCaptcha().catch(() => {});
  }
});