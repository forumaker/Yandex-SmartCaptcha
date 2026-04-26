import app from 'flarum/admin/app';
import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import type m from 'mithril';

function Section(iconClass: string, titleKey: string, ...children: m.Children[]) {
  return (
    <section className="YandexSmartCaptcha-SettingsSection">
      <h3>
        <i className={iconClass} aria-hidden="true" />
        {app.translator.trans(titleKey)}
      </h3>

      <div className="YandexSmartCaptcha-SettingsSection-content">{children}</div>
    </section>
  );
}

export default class YandexSmartCaptchaPage extends ExtensionPage {
  content() {
    return (
      <div className="YandexSmartCaptchaPage">
        <div className="YandexSmartCaptchaPage-content">
          {Section(
            'fas fa-key',
            'forumaker-yandex-smart-captcha.admin.sections.keys',

            // 👇 ТЕКСТ СО ССЫЛКОЙ
            <div className="Form-group">
              <p className="helpText">
                {app.translator.trans(
                  'forumaker-yandex-smart-captcha.admin.settings.create_here',
                  {
                    link: (
                      <a
                        href="https://console.yandex.cloud/"
                        target="_blank"
                        rel="noopener noreferrer"
                      >
                        {app.translator.trans(
                          'forumaker-yandex-smart-captcha.admin.settings.here'
                        )}
                      </a>
                    ),
                  }
                )}
              </p>
            </div>,

            <div className="Form-group">
              {this.buildSettingComponent({
                type: 'text',
                setting: 'forumaker-yandex-smart-captcha.site_key',
                label: app.translator.trans('forumaker-yandex-smart-captcha.admin.settings.site_key'),
                help: app.translator.trans('forumaker-yandex-smart-captcha.admin.settings.site_key_help'),
                placeholder: 'ysc1',
              })}
            </div>,

            <div className="Form-group">
              {this.buildSettingComponent({
                type: 'text',
                setting: 'forumaker-yandex-smart-captcha.server_key',
                label: app.translator.trans('forumaker-yandex-smart-captcha.admin.settings.server_key'),
                help: app.translator.trans('forumaker-yandex-smart-captcha.admin.settings.server_key_help'),
                placeholder: 'ysc2',
              })}
            </div>
          )}

          {Section(
            'fas fa-shield-halved',
            'forumaker-yandex-smart-captcha.admin.sections.protection',

            <div className="Form-group">
              {this.buildSettingComponent({
                type: 'boolean',
                setting: 'forumaker-yandex-smart-captcha.signup',
                label: app.translator.trans('forumaker-yandex-smart-captcha.admin.settings.signup'),
                help: app.translator.trans('forumaker-yandex-smart-captcha.admin.settings.signup_help'),
              })}
            </div>,

            <div className="Form-group">
              {this.buildSettingComponent({
                type: 'boolean',
                setting: 'forumaker-yandex-smart-captcha.signin',
                label: app.translator.trans('forumaker-yandex-smart-captcha.admin.settings.signin'),
                help: app.translator.trans('forumaker-yandex-smart-captcha.admin.settings.signin_help'),
              })}
            </div>
          )}

          <div className="Form-group">{this.submitButton()}</div>
        </div>
      </div>
    );
  }
}