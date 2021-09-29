/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import createConsentManager from './ConsentManagement/ConsentManager';
import createBrowserUiDriver from './Vendor/Usercentrics/BrowserUiDriver';
import HCaptcha from './Vendor/HCaptcha';

// Initialize the ConsentManager with Usercentrics' Browser UI driver
export const consentManager = createConsentManager(createBrowserUiDriver(
  // Tell Browser UI to hide the panel on page load. You can leave it undefined to check for a
  // "data-uc-start-hidden" attribute with value of "true" on the <html> element.
  // true,

  // Pass a settings ID manually or leave it undefined to get it automatically from a
  // "data-uc-settings-id" attribute of the <html> element.
  // 'XXXXXXXXX',
));

// Initialize HCaptcha inputs
export const hCaptcha = new HCaptcha();
hCaptcha.init(consentManager);
