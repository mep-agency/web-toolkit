/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import UcConsentStatusEventInterface from './UcConsentStatusEventInterface';
import ConsentManagementDriverInterface from '../../ConsentManagement/ConsentManagementDriverInterface';
import BrowserUiInterface from './BrowserUiInterface';
import DriverConsentStatusChangedEvent from '../../ConsentManagement/DriverConsentStatusChangedEvent';

// Declare Browser UI API
declare let UC_UI: BrowserUiInterface;

class BrowserUiDriver implements ConsentManagementDriverInterface {
  public readonly STATUS_UPDATE_EVENT_NAME = 'consent_status';

  public readonly events: EventTarget = document.createElement('div');

  private browserUi: Promise<BrowserUiInterface>;

  public constructor(
    startHidden?: boolean,
    private settingsId?: string,
  ) {
    // https://docs.usercentrics.com/#/cmp-v2-ui-api?id=suppress-the-cmp
    if (startHidden ?? document.documentElement.getAttribute('data-uc-start-hidden') === 'true') {
      (window as any).UC_UI_SUPPRESS_CMP_DISPLAY = true;
    }

    this.browserUi = new Promise<BrowserUiInterface>((resolve) => {
      // Browser UI fires this event when ready (not documented)
      window.addEventListener('UC_UI_INITIALIZED', () => {
        resolve(UC_UI);
      });
    });
  }

  public init() {
    /*
     * A dedicated window.mwtUcDataLayer object is created so that you can enable it from the
     * Usercentrics' admin panel (Implementation > Data Layer). Usercentrics' library will
     * push updates to this object.
     */
    (window as any).mwtUcDataLayer = {
      push: (data: UcConsentStatusEventInterface) => {
        if (data.event === 'consent_status') {
          const newConsentStatus = Object.keys(data)
            .reduce((map, currentService) => {
              const currentServiceStatus = data[currentService];

              // Exclude invalid values
              if (!['event', 'type'].includes(currentService) && typeof currentServiceStatus === 'boolean') {
                map.set(currentService, currentServiceStatus);
              }

              return map;
            }, new Map<string, boolean>());

          this.events.dispatchEvent(new DriverConsentStatusChangedEvent(
            this.STATUS_UPDATE_EVENT_NAME,
            {
              detail: {
                newConsentStatus,
              },
            },
          ));
        }
      },
    };

    // Load Browser UI library
    document.addEventListener('DOMContentLoaded', () => {
      const usercentricsScript = document.createElement('script');
      usercentricsScript.id = 'usercentrics-cmp';
      usercentricsScript.src = 'https://app.usercentrics.eu/browser-ui/latest/loader.js';
      usercentricsScript.setAttribute(
        'data-settings-id',
        this.settingsId ?? document.documentElement.getAttribute('data-uc-settings-id')!,
      );

      document.body.append(usercentricsScript);
    });
  }

  public async openPreferencesPanel() {
    (await this.browserUi).showSecondLayer();
  }

  public async closePreferencesPanel() {
    (await this.browserUi).closeCMP();
  }
}

let alreadyInitialized = false;

export default function createBrowserUiDriver(startHidden?: boolean, settingsId?: string) {
  if (alreadyInitialized) {
    throw new Error('This function must be called once, please store the returned object.');
  }

  alreadyInitialized = true;

  return new BrowserUiDriver(startHidden, settingsId);
}
