/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
import sha256 from 'crypto-js/sha256';
import Cookies from 'js-cookie';

import {
  Consent,
  EndpointList, ConsentLocalData,
  MwtPrivacyConsentSdkInterface, ConsentSpecs, PreferencesStatus, ConsentRequestBody,
} from './MwtPrivacyConsentSdkInterface';

const DEFAULT_EXPIRATION_DELAY = 600000; // 10 minuti
const TOKEN_COOKIE_NAME = 'mwt_privacy_consent_token';
const LOCAL_STORAGE_KEY = 'mwt_privacy_consent';
const TOKEN_PLACEHOLDER = '00000000-0000-0000-0000-000000000000';

export default class MwtPrivacyConsentSdk implements MwtPrivacyConsentSdkInterface {
  constructor(
    private apiUrls: EndpointList,
    private cacheExpiration: number = DEFAULT_EXPIRATION_DELAY,
  ) {
  }

  async getCurrentConsent(): Promise<Consent> {
    if (MwtPrivacyConsentSdk.getToken() === undefined) {
      return this.buildNewConsent();
    }

    let localStorageData = localStorage.getItem(LOCAL_STORAGE_KEY);

    if (localStorageData === null) {
      await this.refreshConsent();

      localStorageData = localStorage.getItem(LOCAL_STORAGE_KEY)!;
    }

    let localStorageConsentData = JSON.parse(localStorageData) as ConsentLocalData;
    const timeDif = MwtPrivacyConsentSdk.getCurrentTime() - localStorageConsentData.lastCheck;

    if (timeDif >= this.cacheExpiration) {
      await this.refreshConsent();

      localStorageData = localStorage.getItem(LOCAL_STORAGE_KEY)!;
      localStorageConsentData = JSON.parse(localStorageData) as ConsentLocalData;
    }
    return localStorageConsentData.consent;
  }

  async registerConsent(temporaryConsent: Consent): Promise<any> {
    let apiUrl = this.apiUrls.consentCreate;

    if (temporaryConsent.token !== null) {
      const localStorageElement = JSON.parse(localStorage.getItem(LOCAL_STORAGE_KEY)!);

      if (localStorageElement) {
        if (JSON.stringify(localStorageElement.consent) === JSON.stringify(temporaryConsent)) {
          return localStorageElement.consent;
        }
        apiUrl = MwtPrivacyConsentSdk.generateUrl(this.apiUrls.consentUpdate);
      }
    }
    // eslint-disable-next-line no-useless-catch
    try {
      const response = await fetch(apiUrl, {
        method: 'POST',
        body: MwtPrivacyConsentSdk.buildConsentRequestBody(temporaryConsent),
      });

      if (apiUrl === this.apiUrls.consentCreate) {
        const token = await response.json();

        const consent: Consent = {
          preferences: temporaryConsent.preferences,
          specs: temporaryConsent.specs,
          token: token.token,
        };

        Cookies.set(TOKEN_COOKIE_NAME, consent.token!);
        MwtPrivacyConsentSdk.storeConsent(consent);

        return consent;
      }

      const consent = await response.json();

      MwtPrivacyConsentSdk.storeConsent(consent.token);

      return consent;
    } catch (e) {
      throw e;
    }
  }

  private async refreshConsent() {
    // eslint-disable-next-line no-useless-catch
    try {
      const response = await fetch(MwtPrivacyConsentSdk.generateUrl(this.apiUrls.consentGet), {
        method: 'GET',
      });

      MwtPrivacyConsentSdk.storeConsent(await response.json() as Consent);
    } catch (e) {
      throw e;
    }
  }

  private async getSpecs(): Promise<ConsentSpecs> {
    // eslint-disable-next-line no-useless-catch
    try {
      const response = await fetch(this.apiUrls.getSpecs, {
        method: 'GET',
      });

      return await response.json() as ConsentSpecs;
    } catch (e) {
      throw e;
    }
  }

  private static getToken(): string | undefined {
    return Cookies.get(TOKEN_COOKIE_NAME);
  }

  private static getCurrentTime(): number {
    return (new Date()).getTime();
  }

  private async buildNewConsent(): Promise<Consent> {
    const specs: ConsentSpecs = await this.getSpecs();
    const servicesStatus: PreferencesStatus = {};
    const requiredCategories: string[] = [];

    specs.categories.forEach((categorySpecs) => {
      if (categorySpecs.required) {
        requiredCategories.push(categorySpecs.id);
      }
    });

    specs.services.forEach((serviceSpecs) => {
      servicesStatus[serviceSpecs.id] = requiredCategories.includes(serviceSpecs.category);
    });

    const newConsent: Consent = {
      token: null,
      specs,
      preferences: servicesStatus,
    };

    MwtPrivacyConsentSdk.storeConsent(newConsent);

    return newConsent;
  }

  private static generateUrl(url: string): string {
    return url.replace(TOKEN_PLACEHOLDER, MwtPrivacyConsentSdk.getToken()!);
  }

  private static storeConsent(consent: Consent): void {
    const newConsentLocalData: ConsentLocalData = {
      consent,
      lastCheck: MwtPrivacyConsentSdk.getCurrentTime(),
    };
    localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(newConsentLocalData));
  }

  private static buildConsentRequestBody(consent: Consent): string {
    const consentRequestBody: ConsentRequestBody = {
      specsHash: sha256(JSON.stringify(consent.specs, null, 0)).toString(),
      preferences: consent.preferences,
    };

    return JSON.stringify(consentRequestBody, null, 0);
  }
}
