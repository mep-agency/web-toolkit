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
  EndpointList,
  ConsentLocalData,
  ConsentSpecs,
  PreferencesStatus,
  ConsentRequestBody,
  ResponseConsent,
} from './ConsentInterfaces';

const DEFAULT_EXPIRATION_DELAY = 600000; // 10 minuti
const TOKEN_COOKIE_NAME = 'mwt_privacy_consent_token';
const LOCAL_STORAGE_KEY = 'mwt_privacy_consent';
const TOKEN_PLACEHOLDER = '00000000-0000-0000-0000-000000000000';

export default class ConsentSdk {
  constructor(
    private apiUrls: EndpointList,
    private cacheExpiration: number = DEFAULT_EXPIRATION_DELAY,
  ) {
  }

  async getCurrentConsent(): Promise<ResponseConsent> {
    if (ConsentSdk.getToken() === undefined) {
      return this.buildNewConsent();
    }

    let localStorageData = localStorage.getItem(LOCAL_STORAGE_KEY);

    if (localStorageData === null) {
      await this.refreshConsent();

      localStorageData = localStorage.getItem(LOCAL_STORAGE_KEY)!;
    }

    let localStorageConsentData = JSON.parse(localStorageData) as ConsentLocalData;
    const timeDif = ConsentSdk.getCurrentTime() - localStorageConsentData.lastCheck;

    if (timeDif >= this.cacheExpiration) {
      await this.refreshConsent();

      localStorageData = localStorage.getItem(LOCAL_STORAGE_KEY)!;
      localStorageConsentData = JSON.parse(localStorageData) as ConsentLocalData;
    }

    return localStorageConsentData.consent;
  }

  async registerConsent(temporaryConsent: ResponseConsent): Promise<ResponseConsent> {
    let apiUrl = this.apiUrls.consentCreate;

    if (temporaryConsent.token !== null) {
      const localStorageElement = JSON.parse(localStorage.getItem(LOCAL_STORAGE_KEY)!);

      if (localStorageElement) {
        if (JSON.stringify(localStorageElement.consent) === JSON.stringify(temporaryConsent)) {
          return localStorageElement.consent;
        }
        apiUrl = ConsentSdk.generateUrl(this.apiUrls.consentUpdate);
      }
    }

    const response = await fetch(apiUrl, {
      method: 'POST',
      body: ConsentSdk.buildConsentRequestBody(temporaryConsent),
    });

    if (apiUrl === this.apiUrls.consentCreate) {
      const consent:ResponseConsent = await response.json();

      Cookies.set(TOKEN_COOKIE_NAME, consent.token!);
      ConsentSdk.storeConsent(consent);

      return consent;
    }

    const consent = await response.json();

    ConsentSdk.storeConsent(consent);

    return consent;
  }

  private async refreshConsent() {
    const response = await fetch(ConsentSdk.generateUrl(this.apiUrls.consentGet), {
      method: 'GET',
    });

    ConsentSdk.storeConsent(await response.json() as ResponseConsent);
  }

  private async getSpecs(): Promise<ConsentSpecs> {
    const response = await fetch(this.apiUrls.getSpecs, {
      method: 'GET',
    });

    return await response.json() as ConsentSpecs;
  }

  private static getToken(): string | undefined {
    return Cookies.get(TOKEN_COOKIE_NAME);
  }

  private static getCurrentTime(): number {
    return (new Date()).getTime();
  }

  private async buildNewConsent(): Promise<ResponseConsent> {
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

    const newConsent: ResponseConsent = {
      token: null,
      datetime: null,
      data: {
        specs,
        preferences: servicesStatus,
        specsHash: null,
        userAgent: null,
      },
    };

    ConsentSdk.storeConsent(newConsent);

    return newConsent;
  }

  private static generateUrl(url: string): string {
    return url.replace(TOKEN_PLACEHOLDER, ConsentSdk.getToken()!);
  }

  private static storeConsent(consent: ResponseConsent): void {
    const newConsentLocalData: ConsentLocalData = {
      consent,
      lastCheck: ConsentSdk.getCurrentTime(),
    };
    localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(newConsentLocalData));
  }

  private static buildConsentRequestBody(consent: ResponseConsent): string {
    const consentRequestBody: ConsentRequestBody = {
      specsHash: sha256(JSON.stringify(consent.data.specs, null, 0)).toString(),
      preferences: consent.data.preferences,
    };

    return JSON.stringify(consentRequestBody, null, 0);
  }
}
