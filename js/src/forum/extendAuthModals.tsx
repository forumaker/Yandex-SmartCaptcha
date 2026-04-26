import { extend, override } from 'flarum/common/extend';
import app from 'flarum/forum/app';
import YandexSmartCaptcha from './components/YandexSmartCaptcha';

export default function extendAuthModalsWithYandexSmartCaptcha() {
  const isEnabled = (type: 'signin' | 'signup') =>
    !!app.forum.attribute(`forumaker-yandex-smart-captcha.${type}`);

  const getSiteKey = () =>
    app.forum.attribute<string>('forumaker-yandex-smart-captcha.site_key');

  const applyExtenders = (
    modulePath: string,
    type: 'signin' | 'signup',
    dataMethod: 'loginParams' | 'submitData'
  ) => {
    extend(modulePath, 'oninit', function () {
      if (!isEnabled(type)) return;

      const siteKey = getSiteKey();
      if (!siteKey) return;

      this.yandexSmartCaptcha = {
        token: null as string | null,
        widgetId: null as number | string | null,
        siteKey,
      };
    });

    extend(modulePath, dataMethod, function (data: Record<string, unknown>) {
      if (!isEnabled(type) || !this.yandexSmartCaptcha) return;

      data['smart-token'] = this.yandexSmartCaptcha.token || '';
      data['smart-action'] = type;
    });

    extend(modulePath, 'fields', function (items) {
      if (!isEnabled(type) || !this.yandexSmartCaptcha?.siteKey) return;

      items.add(
        'yandex-smart-captcha',
        <YandexSmartCaptcha state={this.yandexSmartCaptcha} />,
        -5
      );
    });

    override(modulePath, 'onsubmit', function (original, e: Event) {
      if (!isEnabled(type) || !this.yandexSmartCaptcha) {
        return original(e);
      }

      if (!this.yandexSmartCaptcha.token) {
        e.preventDefault();
        this.loading = false;
        this.alertAttrs = {
          type: 'error',
          content: app.translator.trans(
            'forumaker-yandex-smart-captcha.forum.validation.captcha_required'
          ),
        };
        return;
      }

      return original(e);
    });

    extend(modulePath, 'onerror', function (_, error) {
      if (!isEnabled(type) || !this.yandexSmartCaptcha) return;

      if (this.yandexSmartCaptcha.widgetId !== null && window.smartCaptcha) {
        window.smartCaptcha.reset(this.yandexSmartCaptcha.widgetId);
      }

      this.yandexSmartCaptcha.token = null;

      if (error.alert && !error.alert.content?.length) {
        error.alert.content = app.translator.trans(
          'forumaker-yandex-smart-captcha.forum.validation.captcha_invalid'
        );
      }
    });
  };

  applyExtenders('flarum/forum/components/LogInModal', 'signin', 'loginParams');
  applyExtenders('flarum/forum/components/SignUpModal', 'signup', 'submitData');
}