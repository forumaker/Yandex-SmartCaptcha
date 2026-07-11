import Component from 'flarum/common/Component';
import loadSmartCaptcha from '../utils/loadSmartCaptcha';

export default class YandexSmartCaptcha extends Component<{ state: any }> {
  oncreate(vnode) {
    super.oncreate(vnode);

    const state = this.attrs.state;
    const container = vnode.dom.querySelector('.YandexSmartCaptcha-container') as HTMLElement | null;

    if (!container || !state?.siteKey) return;

    const observer = new MutationObserver((mutations) => {
      for (const mutation of mutations) {
        for (const node of Array.from(mutation.addedNodes)) {
          if (node instanceof HTMLIFrameElement) {
            node.allow = [node.allow, 'accelerometer'].filter(Boolean).join('; ');
          }
        }
      }
    });

    observer.observe(container, { childList: true, subtree: true });

    loadSmartCaptcha()
      .then(() => {
        if (!window.smartCaptcha || state.widgetId !== null) return;

        state.widgetId = window.smartCaptcha.render(container, {
          sitekey: state.siteKey,
          hl: this.detectLanguage(),
          callback: (token: string) => {
            state.token = token;
          },
          'expired-callback': () => {
            state.token = null;
          },
          'error-callback': () => {
            state.token = null;
          },
        });

        if (this.detectTheme() === 'dark') {
          container.style.filter = 'invert(0.87) hue-rotate(180deg)';
        }
      })
      .catch(() => {
        state.token = null;
      });
  }

  onremove() {
    super.onremove();

    const state = this.attrs.state;

    if (state?.widgetId !== null && window.smartCaptcha) {
      window.smartCaptcha.destroy(state.widgetId);
    }

    if (state) {
      state.widgetId = null;
      state.token = null;
    }
  }

  detectTheme(): 'dark' | 'light' {
    const cssVar = getComputedStyle(document.documentElement)
      .getPropertyValue('--body-bg').trim();

    if (cssVar) {
      const hsl = cssVar.match(/hsla?\(\s*[\d.]+,\s*[\d.]+%,\s*([\d.]+)%/);
      if (hsl) return parseFloat(hsl[1]) < 50 ? 'dark' : 'light';

      if (cssVar.startsWith('#')) {
        const hex = cssVar.length === 4
          ? `#${cssVar[1]}${cssVar[1]}${cssVar[2]}${cssVar[2]}${cssVar[3]}${cssVar[3]}`
          : cssVar;
        if (hex.length >= 7) {
          const r = parseInt(hex.slice(1, 3), 16);
          const g = parseInt(hex.slice(3, 5), 16);
          const b = parseInt(hex.slice(5, 7), 16);
          if (!isNaN(r + g + b)) {
            return (0.299 * r + 0.587 * g + 0.114 * b) / 255 < 0.5 ? 'dark' : 'light';
          }
        }
      }

      const rgb = cssVar.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
      if (rgb) {
        const [, r, g, b] = rgb.map(Number);
        return (0.299 * r + 0.587 * g + 0.114 * b) / 255 < 0.5 ? 'dark' : 'light';
      }
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }

  detectLanguage(): 'ru' | 'en' | 'be' | 'kk' | 'tt' | 'uk' | 'uz' | 'tr' {
    const locale = (document.documentElement.lang || navigator.language || 'en').toLowerCase();

    if (locale.startsWith('ru')) return 'ru';
    if (locale.startsWith('be')) return 'be';
    if (locale.startsWith('kk')) return 'kk';
    if (locale.startsWith('tt')) return 'tt';
    if (locale.startsWith('uk')) return 'uk';
    if (locale.startsWith('uz')) return 'uz';
    if (locale.startsWith('tr')) return 'tr';

    return 'en';
  }

  view() {
    return (
      <div className="Form-group">
        <div className="YandexSmartCaptcha-container" />
      </div>
    );
  }
}