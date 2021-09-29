/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import ConsentManagerInterface from '../../ConsentManagement/ConsentManagerInterface';

export default class HCaptcha {
  private isInitialized = false;

  public init(consentManager: ConsentManagerInterface, serviceName: string = 'hCaptcha', reloadOnDisable: boolean = true) {
    consentManager.addConsentStatusListener(
      serviceName,
      (newValue, isInit) => {
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
}
