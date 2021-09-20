export interface UcConsentStatusEvent {
  event: 'consent_status';
  type: 'explicit' | 'implicit';
  [key: string]: string | boolean;
}

class BrowserUiUtil extends EventTarget {
  private isInitialized = false;

  public init(settingsId?: string) {
    if (this.isInitialized) {
      return;
    }

    this.isInitialized = true;

    document.addEventListener('DOMContentLoaded', () => {
      const usercentricsScript = document.createElement('script');
      usercentricsScript.id = 'usercentrics-cmp';
      usercentricsScript.src = 'https://app.usercentrics.eu/browser-ui/latest/loader.js';
      usercentricsScript.setAttribute(
        'data-settings-id',
        settingsId ?? document.documentElement.getAttribute('data-uc-settings-id')!,
      );

      document.body.append(usercentricsScript);
    });
  }

  public addConsentListener(
    callback: (evt: UcConsentStatusEvent) => (boolean | void),
    options?: AddEventListenerOptions | boolean,
  ) {
    this.addEventListener('consent_status', {
      handleEvent(evt: CustomEvent) {
        if (callback(evt.detail) === true) {
          window.location.reload();
        }
      },
    }, options);
  }
}

const browserUiUtil = new BrowserUiUtil();

/*
 * A dedicated window.mwtUcDataLayer object is added so that you can enable it from the
 * Usercentrics' admin panel (Implementation > Data Layer).
 *
 * Then you can listen to consent changes with:
 *
 * browserUiUtil.addConsentListener((evt) => {
 *   if (evt['My Custom Service'] === true) {
 *     console.log('"My Custom Service" is enabled!');
 *   } else {
 *     console.log('"My Custom Service" is disabled!');
 *   }
 *
 *   // Return true to reload the page
 *   return false;
 * });
 */
(window as any).mwtUcDataLayer = {
  push(data: UcConsentStatusEvent) {
    if (data.event === 'consent_status') {
      browserUiUtil.dispatchEvent(new CustomEvent('consent_status', { detail: data }));
    }
  },
};

export default browserUiUtil;
