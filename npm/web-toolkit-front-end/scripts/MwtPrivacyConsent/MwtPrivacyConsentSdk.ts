/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
import { SpecsHashed, SpecsList } from './MwtPrivacyConsentInterface';
import EndpointList from './MwtPrivacyConsentSdkInterface';

export default class MwtPrivacyConsentSdk {
  URLPaths:EndpointList = new EndpointList();

  public async init(endpoints: EndpointList) {
    this.URLPaths = endpoints;

    return this.getSpecs();
  }

  public sendConsent(hashedSpecs: SpecsHashed) {
    // eslint-disable-next-line no-console
    console.log('Consent Send!');
    this.consentCreate(hashedSpecs);
  }

  private async getSpecs() {
    try {
      const response = await fetch(this.URLPaths.getSpecs, {
        method: 'GET',
      });
      return await response.json();
    } catch (e) {
      // eslint-disable-next-line no-console
      console.error(e);
      return null;
    }
  }

  private async consentCreate(data: SpecsHashed) {
    try {
      const response = await fetch(this.URLPaths.consentCreate, {
        method: 'POST',
        body: JSON.stringify(data),
      });
      // eslint-disable-next-line no-console
      console.log(response);
      return await response.json();
    } catch (e) {
      // eslint-disable-next-line no-console
      console.error(e);
      return null;
    }
  }

  private async getHistory(token: string) {
    this.URLPaths.getHistory.replace('00000000-0000-0000-0000-000000000000', token);

    try {
      const response = await fetch(this.URLPaths.getHistory, {
        method: 'GET',
      });
      return await response.json();
    } catch (e) {
      // eslint-disable-next-line no-console
      console.error(e);
      return null;
    }
  }

  private async consentGet(token: string) {
    // Temporary implementation
    this.URLPaths.consentGet.replace('00000000-0000-0000-0000-000000000000', token);
    try {
      const response = await fetch(this.URLPaths.consentGet, {
        method: 'GET',
      });
      return await response.json();
    } catch (e) {
      // eslint-disable-next-line no-console
      console.error(e);
      return null;
    }
  }

  private async consentUpdate(token: string, data: SpecsList) {
    this.URLPaths.consentUpdate.replace('00000000-0000-0000-0000-000000000000', token);

    try {
      const response = await fetch(this.URLPaths.consentUpdate, {
        method: 'POST',
        body: JSON.stringify(data),
      });
      return await response.json();
    } catch (e) {
      // eslint-disable-next-line no-console
      console.error(e);
      return null;
    }
  }

  // TODO: reimplement localstorage saving of token and lastCheckDate

  // TODO: Debouncer
}
