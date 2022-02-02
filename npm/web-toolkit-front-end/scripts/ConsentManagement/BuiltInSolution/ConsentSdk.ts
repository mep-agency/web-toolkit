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
let PEMKEYPAIR:Rsa.PemKeyPair;

export default class ConsentSdk {
  constructor(
    private apiUrls: EndpointList,
    private cacheExpiration: number = DEFAULT_EXPIRATION_DELAY,
  ) {
    ConsentSdk.getPemKeyPair().then((r) => {
      PEMKEYPAIR = r;
    });
  }

  async getCurrentConsent(): Promise<LocalConsent> {
    // Se non ho salvato la hash dei system, il consento non esiste in locale
    if (ConsentSdk.getPublicKeyHash() === undefined) {
      return this.buildNewConsent();
    }

    // prendo il consento dal locale
    let localStorageData = localStorage.getItem(LOCAL_STORAGE_KEY);

    // Se il consento non esiste in locale, c'è stato un problema e lo richiedo dal system
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

  async registerConsent(temporaryConsent: LocalConsent): Promise<LocalConsent> {
    let isNewConsent = true;

    if (ConsentSdk.getPublicKeyHash() !== undefined) {
      let localStorageElement = JSON.parse(localStorage.getItem(LOCAL_STORAGE_KEY)!) as ConsentLocalData;

      if (localStorageElement) {
        if (JSON.stringify(localStorageElement.consent) === JSON.stringify(temporaryConsent)) {
          return localStorageElement.consent;
        }
        isNewConsent = false;
      } else {
        await this.refreshConsent();
        localStorageElement = JSON.parse(localStorage.getItem(LOCAL_STORAGE_KEY)!);
        return localStorageElement.consent;
      }
    }

    const response = await fetch(this.apiUrls.consentCreate, {
      method: 'POST',
      body: await ConsentSdk.buildConsentRequestBody(temporaryConsent, isNewConsent),
    });
    const consent: ResponseConsent = await response.json();

    const systemPublicKey: CryptoKey = await Rsa.importPublicPem(consent.systemPublicKey);
    const systemVerify = await Rsa.verify(systemPublicKey, consent.systemSignature, JSON.stringify(consent.data));

    if (!systemVerify && (consent.userPublicKey !== PEMKEYPAIR.publicKey)) {
      throw new Error('Signature not match!');
    }

    if (isNewConsent) {
      Cookies.set(HASH_COOKIE_NAME, JSON.stringify(consent.userPublicKey));
    }

    const localConsent: LocalConsent = {
      publicKeyHash: sha256(consent.userPublicKey).toString(),
      data: consent.data,
      signature: consent.userSignature,
    };

    ConsentSdk.storeConsent(consent);

    return localConsent;
  }

  private async refreshConsent() {
    const response = await fetch(ConsentSdk.generateUrl(this.apiUrls.consentGet), {
      method: 'GET',
    });
    const consent = await response.json() as ResponseConsent;

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

  private async buildNewConsent(): Promise<LocalConsent> {
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
      data: {
        userAgent: null,
        timestamp: null,
        specs,
        preferences: servicesStatus,
      },
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

  private static async buildConsentRequestBody(consent: LocalConsent, isCreate: boolean): Promise<string> {
    const newConsent = consent;
    const cryptoKeyPair: CryptoKeyPair = await Rsa.importFromPem(PEMKEYPAIR);

    newConsent.signature = await Rsa.sign(cryptoKeyPair.privateKey!, JSON.stringify(consent.data));

    if (isCreate) {
      newConsent.publicKey = PEMKEYPAIR.publicKey;
    } else {
      newConsent.publicKeyHash = sha256(PEMKEYPAIR.publicKey).toString();
    }

    return JSON.stringify(newConsent, null, 0);
  }

  private static async getPemKeyPair(): Promise<Rsa.PemKeyPair> {
    if (localStorage.getItem(PEM_RSA_STORAGE_KEY)) {
      return JSON.parse(localStorage.getItem(PEM_RSA_STORAGE_KEY)!) as Rsa.PemKeyPair;
    }
    const pemKeyPair = await Rsa.exportToPem(await Rsa.generateKey());
    localStorage.setItem(PEM_RSA_STORAGE_KEY, JSON.stringify(pemKeyPair));
    return pemKeyPair;
  }
}
