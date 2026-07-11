# 🛡️ Yandex SmartCaptcha for Flarum
Adds a [Captcha](https://cloud.yandex.ru/services/smartcaptcha) to the login and registration modals. Supports **Flarum 2.x**

![License](https://img.shields.io/badge/license-MIT-blue) ![Packagist Version](https://img.shields.io/packagist/v/forumaker/yandex-smartcaptcha) ![Downloads](https://img.shields.io/packagist/dt/forumaker/yandex-smartcaptcha)


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
<img width="1300" height="730" alt="image" src="https://github.com/user-attachments/assets/66429b83-af9a-4775-84b6-0aac0f02b49b" />


## 🔗 Links
- [**GitHub Repository**](https://github.com/forumaker/yandex-smartcaptcha)
- [**Packagist**](https://packagist.org/packages/forumaker/yandex-smartcaptcha)
- [**Discuss**](https://discuss.flarum.org/d/39162-yandex-smartcaptcha-for-login-and-registation)
