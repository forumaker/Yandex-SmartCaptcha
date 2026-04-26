# 🛡️ Yandex SmartCaptcha for Flarum
Yandex SmartCaptcha Adds a [Captcha](https://cloud.yandex.ru/services/smartcaptcha) to the login and registration modals. Supports **Flarum 2.x**


## 🚀 Features
- ✅ Captcha on login and/or registration — configurable separately
- 🌙 Automatic dark/light theme detection based on the active Flarum theme
- 🌍 Automatic language detection (ru, en, be, kk, tt, uk, uz, tr)


## 📦 Installation
```
composer require forumaker/yandex-smartcaptcha:"*"
```


## 🛠️ Configuration
1. Go to [Yandex Cloud Console → SmartCaptcha](https://console.yandex.cloud/)
2. Create a new captcha and add your forum domain to the allowed hosts
3. Copy the **Client key** and **Server key**
4. In Yandex Cloud Console **disable** the Dynamic color scheme toggle. The extension detects the active Flarum theme automatically and applies the correct appearance


## 📸 Screenshots


## 🔗 Links
- [**GitHub Repository**](https://github.com/forumaker/yandex-smartcaptcha)
- [**Packagist**](https://packagist.org/packages/forumaker/yandex-smartcaptcha)
- [**Extiverse**](https://extiverse.com/extension/forumaker/yandex-smartcaptcha)
- [**Discuss**](https://discuss.flarum.org/)