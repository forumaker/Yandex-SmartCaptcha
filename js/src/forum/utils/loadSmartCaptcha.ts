let smartCaptchaLoader: Promise<void> | null = null;

export default function loadSmartCaptcha(): Promise<void> {
  if (window.smartCaptcha?.render) {
    return Promise.resolve();
  }

  if (smartCaptchaLoader) {
    return smartCaptchaLoader;
  }

  smartCaptchaLoader = new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.src = 'https://smartcaptcha.cloud.yandex.ru/captcha.js?render=explicit';
    script.async = true;
    script.defer = true;

    script.onload = () => resolve();
    script.onerror = () => {
      smartCaptchaLoader = null;
      reject(new Error('Failed to load Yandex SmartCaptcha'));
    };

    document.head.appendChild(script);
  });

  return smartCaptchaLoader;
}