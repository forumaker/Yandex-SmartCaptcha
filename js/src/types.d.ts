export {};

declare global {
  interface Window {
    smartCaptcha?: {
      render: (container: HTMLElement, options: Record<string, unknown>) => number | string;
      destroy: (widgetId: number | string) => void;
      reset: (widgetId: number | string) => void;
    };
  }
}
