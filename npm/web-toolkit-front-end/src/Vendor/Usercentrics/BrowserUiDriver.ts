import privacyManager from '../../Privacy/PrivacyManager';
import UcConsentStatusEventInterface from './UcConsentStatusEventInterface';

class BrowserUiDriver {
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
}

const browserUiDriver = new BrowserUiDriver();

/*
 * A dedicated window.mwtUcDataLayer object is created so that you can enable it from the
 * Usercentrics' admin panel (Implementation > Data Layer).
 */
(window as any).mwtUcDataLayer = {
  push(data: UcConsentStatusEventInterface) {
    if (data.event === 'consent_status') {
      const newStatus = Object.keys(data)
        .reduce((map, currentService) => {
          const currentServiceStatus = data[currentService];

          // Exclude invalid values
          if (!['event', 'type'].includes(currentService) && typeof currentServiceStatus === 'boolean') {
            map.set(currentService, currentServiceStatus);
          }

          return map;
        }, new Map<string, boolean>());

      privacyManager.updateStatus(newStatus);
    }
  },
};

export default browserUiDriver;
