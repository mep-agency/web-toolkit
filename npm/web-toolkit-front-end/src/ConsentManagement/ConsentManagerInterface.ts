/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import ConsentStatusChangedCallbackType from './ConsentStatusChangedCallbackType';

export default interface ConsentManagerInterface {
  addConsentStatusListener(
    serviceName: string,
    callback: ConsentStatusChangedCallbackType,
    options?: AddEventListenerOptions | boolean,
  ): void;

  openPreferencesPanel(): Promise<void>;

  closePreferencesPanel(): Promise<void>;
}
