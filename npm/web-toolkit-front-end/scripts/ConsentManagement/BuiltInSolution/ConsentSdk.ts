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
import * as Rsa from '../../Util/Rsa';

import {
  ConsentData,
  ConsentLocalData,
  ConsentSpecs,
  EndpointList,
  LocalConsent,
  PreferencesStatus,
  ResponseConsent,
} from './ConsentInterfaces';

const DEFAULT_EXPIRATION_DELAY = 600000; // 10 minuti
const HASH_COOKIE_NAME = 'mwt_privacy_consent_hash';
const LOCAL_STORAGE_KEY = 'mwt_privacy_consent';
const PEM_RSA_STORAGE_KEY = 'mwt_privacy_consent_rsa_pem';
const HASH_PLACEHOLDER = '0000000000000000000000000000000000000000000000000000000000000000';
let PEMKEYPAIR: Rsa.PemKeyPair;

export default class ConsentSdk {
  constructor(
    private apiUrls: EndpointList,
    private cacheExpiration: number = DEFAULT_EXPIRATION_DELAY,
  ) {
    ConsentSdk.getPemKeyPair();
  }

  async getCurrentConsent(): Promise<ConsentData> {
    if (ConsentSdk.getPublicKeyHash() === undefined) {
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

    return JSON.parse(localStorageConsentData.consent.data) as ConsentData;
  }

  async registerConsent(temporaryConsent: ConsentData): Promise<ConsentData> {
    let isNewConsent = true;

    if (ConsentSdk.getPublicKeyHash() !== undefined) {
      const localStorageElement = localStorage.getItem(LOCAL_STORAGE_KEY);

      if (localStorageElement) {
        const localStorageConsent = JSON.parse(localStorageElement).consent as ResponseConsent;

        if (JSON.stringify(localStorageConsent.data) === JSON.stringify(temporaryConsent)) {
          return JSON.parse(localStorageConsent.data) as ConsentData;
        }
        isNewConsent = false;
      } else {
        await this.refreshConsent();
        return JSON.parse(
          JSON.parse(localStorage.getItem(LOCAL_STORAGE_KEY)!).consent!.data,
        ) as ConsentData;
      }
    }

    const builtConsent = await ConsentSdk.buildConsentRequestBody(temporaryConsent, isNewConsent);
    const response = await fetch(this.apiUrls.consentCreate, {
      method: 'POST',
      body: builtConsent,
    });

    if (!response.ok) throw new Error('Invalid response');

    const consent: ResponseConsent = await response.json();

    await ConsentSdk.checkConsent(consent, JSON.parse(builtConsent) as LocalConsent);

    if (isNewConsent) {
      Cookies.set(HASH_COOKIE_NAME, sha256(PEMKEYPAIR.publicKey).toString());
    }

    ConsentSdk.storeConsent(consent);

    return JSON.parse(consent.data) as ConsentData;
  }

  private static async checkConsent(
    remoteConsent: ResponseConsent,
    sentConsent: LocalConsent,
  ): Promise<void> {
    if (sentConsent.signature !== remoteConsent.userSignature) throw new Error('User signatures not match!');

    if (sentConsent.data !== remoteConsent.data) throw new Error('Data not match!');

    if (window
      .atob(remoteConsent.userPublicKey)
      .includes(
        PEMKEYPAIR
          .publicKey
          .replace(/.{64}/g, '$&\n'),
      )
    ) throw new Error('User public keys not match!');

    const systemVerify = await Rsa.verify(
      await Rsa.importPublicPem(window.atob(remoteConsent.systemPublicKey)),
      remoteConsent.systemSignature,
      window.btoa(remoteConsent.data),
    );

    if (!systemVerify) throw new Error('System signature not valid!');
  }

  private async refreshConsent() {
    const response = await fetch(ConsentSdk.generateUrl(this.apiUrls.consentGet), {
      method: 'GET',
    });
    const consent: ResponseConsent = await response.json();

    ConsentSdk.storeConsent(consent);
  }

  private async getSpecs(): Promise<ConsentSpecs> {
    const response = await fetch(this.apiUrls.getSpecs, {
      method: 'GET',
    });

    return await response.json() as ConsentSpecs;
  }

  private static getPublicKeyHash(): string | undefined {
    return Cookies.get(HASH_COOKIE_NAME);
  }

  private static getCurrentTime(): number {
    return (new Date()).getTime();
  }

  private async buildNewConsent(): Promise<ConsentData> {
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

    return {
      previousConsentDataHash: null,
      userAgent: null,
      timestamp: null,
      specs,
      preferences: servicesStatus,
    };
  }

  private static generateUrl(url: string): string {
    return url.replace(HASH_PLACEHOLDER, ConsentSdk.getPublicKeyHash()!);
  }

  private static storeConsent(consent: ResponseConsent): void {
    const newConsentLocalData: ConsentLocalData = {
      consent,
      lastCheck: ConsentSdk.getCurrentTime(),
    };
    localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(newConsentLocalData));
  }

  private static async buildConsentRequestBody(
    consent: ConsentData,
    isCreate: boolean,
  ): Promise<string> {
    let publicKey;
    let publicKeyHash;
    let previousConsentDataHash = null;

    if (isCreate) {
      publicKey = PEMKEYPAIR.publicKey;
    } else {
      publicKeyHash = sha256(PEMKEYPAIR.publicKey).toString();

      if (publicKeyHash !== ConsentSdk.getPublicKeyHash()) throw new Error('CookieHash and KeyHash not match!');

      const previousConsent = JSON.parse(localStorage.getItem(LOCAL_STORAGE_KEY)!);
      previousConsentDataHash = sha256(previousConsent.consent.data).toString();
    }

    const cryptoKeyPair: CryptoKeyPair = await Rsa.importFromPem(PEMKEYPAIR);

    const data = {
      previousConsentDataHash,
      userAgent: navigator.userAgent,
      timestamp: Math.ceil(new Date().getTime() / 1000),
      specs: consent.specs,
      preferences: consent.preferences,
    };
    const signature = await Rsa.sign(cryptoKeyPair.privateKey!, window.btoa(JSON.stringify(data)));

    return JSON.stringify({
      data: JSON.stringify(data),
      signature,
      ...(publicKey !== undefined) && { publicKey },
      ...(publicKey === undefined) && { publicKeyHash },
    }, null, 0);
  }

  private static async getPemKeyPair(): Promise<void> {
    if (localStorage.getItem(PEM_RSA_STORAGE_KEY)) {
      PEMKEYPAIR = JSON.parse(localStorage.getItem(PEM_RSA_STORAGE_KEY)!) as Rsa.PemKeyPair;
      if (Cookies.get(HASH_COOKIE_NAME) === undefined) {
        Cookies.set(HASH_COOKIE_NAME, sha256(PEMKEYPAIR.publicKey).toString());
      }
    } else {
      PEMKEYPAIR = await Rsa.exportToPem(await Rsa.generateKey());
      localStorage.setItem(PEM_RSA_STORAGE_KEY, JSON.stringify(PEMKEYPAIR));
    }
  }
}
