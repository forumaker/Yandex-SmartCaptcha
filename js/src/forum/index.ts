import app from 'flarum/forum/app';
import extendAuthModalsWithYandexSmartCaptcha from './extendAuthModals';

app.initializers.add('forumaker-yandex-smart-captcha', () => {
  extendAuthModalsWithYandexSmartCaptcha();
});