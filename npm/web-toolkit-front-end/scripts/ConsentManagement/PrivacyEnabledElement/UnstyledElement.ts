/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import I18n from './I18n';
import ConsentManager from '../ConsentManager';

export default class PrivacyEnabledElement {
  protected readonly element: HTMLElement;

  public constructor(element: HTMLElement, serviceName: string) {
    this.element = element;

    ConsentManager.addConsentStatusListener(
      serviceName,
      (newValue) => {
        if (newValue) {
          this.enable();
          return;
        }

        this.disable();
      },
    );
  }

  protected disable(): void {
    this.element.classList.add('mwt-disabled-by-privacy-preferences');

    const privacyOverlay = document.createElement('div');
    privacyOverlay.className = 'mwt-privacy-overlay';
    const messageElement = document.createElement('div');
    messageElement.className = 'message';
    messageElement.innerHTML = I18n.feature_is_disabled;
    const popupButton = document.createElement('button');
    popupButton.innerHTML = I18n.update_preferences;

    popupButton.addEventListener('click', (e) => {
      e.preventDefault();
      ConsentManager.openPreferencesPanel();
    });

    privacyOverlay.appendChild(messageElement);
    messageElement.appendChild(popupButton);
    this.element.appendChild(privacyOverlay);
  }

  protected enable(): void {
    this.element.classList.remove('mwt-disabled-by-privacy-preferences');

    const privaryOverlayElement = this.element.querySelector('.mwt-privacy-overlay');

    if (privaryOverlayElement) {
      privaryOverlayElement.remove();
    }
  }
}
