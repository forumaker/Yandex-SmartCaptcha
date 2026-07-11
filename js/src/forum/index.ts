import app from 'flarum/forum/app';
import Application from 'flarum/common/Application';
import { extend } from 'flarum/common/extend';
import extendAuthModalsWithYandexSmartCaptcha from './extendAuthModals';
import loadSmartCaptcha from './utils/loadSmartCaptcha';

app.initializers.add('forumaker-yandex-smart-captcha', () => {
  extendAuthModalsWithYandexSmartCaptcha();

  // app.forum isn't hydrated yet while initializers are running, so reading
  // its attributes here throws. `mount` fires once, after boot has finished
  // and forum data is available, which is the earliest safe place to check
  // settings and kick off the preload.
  extend(Application.prototype, 'mount', () => {
    if (!app.forum) return;

    const isEnabled =
      !!app.forum.attribute('forumaker-yandex-smart-captcha.signin') ||
      !!app.forum.attribute('forumaker-yandex-smart-captcha.signup');
    const siteKey = app.forum.attribute<string>('forumaker-yandex-smart-captcha.site_key');

    if (isEnabled && siteKey) {
      // Preload the SmartCaptcha script right after the forum boots, so
      // it's already cached by the time the login/signup modal opens. This
      // only fetches the JS file once (async, non-blocking) — no
      // verification requests happen until a widget is actually rendered.
      loadSmartCaptcha().catch(() => {});
    }
  });
});