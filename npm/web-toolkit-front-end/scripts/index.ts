/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// eslint-disable-next-line @typescript-eslint/no-unused-vars
import HCaptcha from './Vendor/HCaptcha';

import BrowserBanner from './MwtPrivacyConsent/BrowserBanner';
import MwtPrivacyConsentSdk from './MwtPrivacyConsent/MwtPrivacyConsentSdk';

const bannerUi = new BrowserBanner();
// eslint-disable-next-line @typescript-eslint/no-unused-vars
const privacyConsent = new MwtPrivacyConsentSdk(bannerUi.parseEndpoints());

/*
// Initialize HCaptcha inputs
export const hCaptcha = new HCaptcha();
hCaptcha.init(consentManager);
*/
