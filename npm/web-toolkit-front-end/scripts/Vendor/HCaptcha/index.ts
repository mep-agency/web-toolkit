/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import ConsentManager from '../../ConsentManagement/ConsentManager';
import PrivacyEnabledElement from '../../ConsentManagement/PrivacyEnabledElement';

export default class HCaptcha {
  private isInitialized = false;

  public init(serviceName: string = 'h-captcha', reloadOnDisable: boolean = true, toggleDisableParentForm: boolean = true) {
    ConsentManager.addConsentStatusListener(
      serviceName,
      (newValue, isInit) => {
        if (toggleDisableParentForm) {
          HCaptcha.toggleDisableForm(newValue);
        }

        if (newValue) {
          if (this.isInitialized) {
            throw new Error('Cannot initialize multiple times!');
          }

          this.isInitialized = true;

          const hCaptchaScript = document.createElement('script');
          hCaptchaScript.src = 'https://hcaptcha.com/1/api.js';

          document.body.append(hCaptchaScript);

          return;
        }

        if (!isInit && reloadOnDisable) {
          document.location.reload();
        }
      },
    );
  }

  private static toggleDisableForm(isCaptchaEnabled: boolean) {
    const hCaptchaWidget = document.getElementsByClassName('h-captcha')[0];

    if (hCaptchaWidget) {
      const parentForm = hCaptchaWidget.closest('form');

      if (parentForm) {
        const formFieldSet = parentForm.querySelector('fieldset:only-of-type') as HTMLFieldSetElement;

        if (formFieldSet) {
          formFieldSet.disabled = !isCaptchaEnabled;
          // eslint-disable-next-line no-new
          new PrivacyEnabledElement(parentForm, 'h-captcha');
        }
      }
    }
  }
}
