/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
import sha256 from 'crypto-js/sha256';
import {
  Consent,
  EndpointList, ConsentLocalData,
  MwtPrivacyConsentSdkInterface, ConsentSpecs, ServicesStatus, ConsentRequestBody,
} from './MwtPrivacyConsentSdkInterface';

const DEFAULT_EXPIRATION_DELAY = 600000; // 10 minuti
const TOKEN_COOKIE_NAME = 'mwt_privacy_consent_token';
const LOCAL_STORAGE_KEY = 'mwt_privacy_consent';
const TOKEN_PLACEHOLDER = '00000000-0000-0000-0000-000000000000';

export default class MwtPrivacyConsentSdk implements MwtPrivacyConsentSdkInterface {
  private savedSpecsConsents!: ConsentLocalData;

  constructor(
    private apiUrls: EndpointList,
    private cacheExpiration: number = DEFAULT_EXPIRATION_DELAY,
  ) {

  }

  // TODO: function to replace token before fetch

  // COOKIE FUNCTIONS
  private setCookie(key:string, value:string) {
    document.cookie = `${key}=${value || ''}; path=/`;
  }

  private getCookie(key:string): string | null {
    const nameEQ = `${key}=`;
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) == ' ') c = c.substring(1, c.length);
      if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
  }

  private invalidateCookie(key:string) {
    document.cookie = `${key}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
  }
  // END COOKIE FUNCTIONS

  async getCurrentConsent(): Promise<Consent> {
    if (this.getToken() === null) {
      return this.buildNewConsent();
    }

    let localStorageData = localStorage.getItem(LOCAL_STORAGE_KEY);

    if (localStorageData === null) {
      await this.refreshConsent();

      localStorageData = localStorage.getItem(LOCAL_STORAGE_KEY)!;
    }

    let localConsentStorageConsentData: ConsentLocalData = JSON.parse(localStorageData) as ConsentLocalData;

    if ((this.getCurrentTime() - localConsentStorageConsentData.lastCheck) >= this.cacheExpiration) {
      await this.refreshConsent();

      localStorageData = localStorage.getItem(LOCAL_STORAGE_KEY)!;
      localConsentStorageConsentData = JSON.parse(localStorageData) as ConsentLocalData;
    }

    return localConsentStorageConsentData.consent;
  }

  async registerConsent(temporaryConsent: Consent): Promise<any> {
    let apiUrl = this.apiUrls.consentCreate;

    if (temporaryConsent.token !== null) {
      apiUrl = this.generateUrl(this.apiUrls.consentUpdate);
    }

    try {
      const response = await fetch(apiUrl, {
        method: 'POST',
        body: this.buildConsentRequestBody(temporaryConsent),
      });
      const consent = await response.json() as Consent;

      this.setCookie(TOKEN_COOKIE_NAME, consent.token!);
      this.storeConsent(consent);

      return consent;
    } catch (e) {
      throw e;
    }
  }

  private async refreshConsent() {
    try {
      const response = await fetch(this.generateUrl(this.apiUrls.consentGet), {
        method: 'GET',
      });

      this.storeConsent(await response.json() as Consent);
    } catch (e) {
      throw e;
    }
  }

  private async getSpecs(): Promise<ConsentSpecs> {
    try {
      const response = await fetch(this.apiUrls.getSpecs, {
        method: 'GET',
      });

      return await response.json() as ConsentSpecs;
    } catch (e) {
      throw e;
    }
  }

  private getToken(): string | null {
    return this.getCookie(TOKEN_COOKIE_NAME);
  }

  private getCurrentTime(): number {
    return (new Date()).getTime();
  }

  private async buildNewConsent(): Promise<Consent> {
    const specs: ConsentSpecs = await this.getSpecs();
    const servicesStatus: ServicesStatus = {};
    const requiredCategories: string[] = [];

    specs.categories.map((categorySpecs) => {
      if (categorySpecs.required) {
        requiredCategories.push(categorySpecs.id);
      }
    });

    specs.services.map((serviceSpecs) => {
      servicesStatus[serviceSpecs.id] = requiredCategories.includes(serviceSpecs.category);
    });

    const newConsent: Consent = {
      token: null,
      specs,
      consent: servicesStatus,
    };

    this.storeConsent(newConsent);

    return newConsent;
  }

  private generateUrl(url: string): string {
    return url.replace(TOKEN_PLACEHOLDER, this.getToken()!);
  }

  private storeConsent(consent: Consent): void {
    const newConsentLocalData: ConsentLocalData = {
      consent,
      lastCheck: this.getCurrentTime(),
    };

    localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(newConsentLocalData));
  }

  private buildConsentRequestBody(consent: Consent): string {
    const consentRequestBody: ConsentRequestBody = {
      specsHash: sha256(JSON.stringify(consent.specs, null, 0)).toString(),
      consent: consent.consent,
    };

    return JSON.stringify(consentRequestBody, null, 0);
  }
}
