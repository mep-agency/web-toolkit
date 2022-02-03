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
    ConsentSdk.getPemKeyPair().then((r) => {
      PEMKEYPAIR = r;
    });
  }

  async getCurrentConsent(): Promise<ConsentData> {
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

    return JSON.parse(localStorageConsentData.consent.data);
  }

  async registerConsent(temporaryConsent: ConsentData): Promise<ConsentData> {
    let isNewConsent = true;

    if (ConsentSdk.getPublicKeyHash() !== undefined) {
      let localStorageElement = JSON.parse(localStorage.getItem(LOCAL_STORAGE_KEY)!) as ConsentLocalData;

      if (localStorageElement) {
        if (JSON.stringify(localStorageElement.consent.data) === JSON.stringify(temporaryConsent)) {
          return JSON.parse(localStorageElement.consent.data);
        }
        isNewConsent = false;
      } else {
        await this.refreshConsent();
        localStorageElement = JSON.parse(localStorage.getItem(LOCAL_STORAGE_KEY)!);
        return JSON.parse(localStorageElement.consent.data);
      }
    }

    const localBuiltConsent = await ConsentSdk.buildConsentRequestBody(temporaryConsent, isNewConsent);
    const response = await fetch(this.apiUrls.consentCreate, {
      method: 'POST',
      body: localBuiltConsent,
    });

    if (!response.ok) throw new Error('Invalid response');

    const consent:ResponseConsent = await response.json();

    if (!await this.checkConsent(consent, JSON.parse(localBuiltConsent))) throw new Error('Consent invalid!');

    if (isNewConsent) {
      Cookies.set(HASH_COOKIE_NAME, sha256(PEMKEYPAIR.publicKey).toString());
    }

    ConsentSdk.storeConsent(consent);

    return JSON.parse(consent.data);
  }

  private async checkConsent(remoteConsent: ResponseConsent, sentConsent: LocalConsent): Promise<boolean> {
    if (sentConsent.signature !== remoteConsent.userSignature) throw new Error('User signatures not match!');

    /* TODO: implement checks
    console.log(sentConsent.data)
    console.log(JSON.parse(remoteConsent.data) as ConsentData)
    if(sentConsent.data !== JSON.parse(remoteConsent.data) as ConsentData) throw new Error('Data sent and received not match!');

    const userRemotePublicKey: CryptoKey = await Rsa.importPublicPem(window.atob(remoteConsent.userPublicKey));
    const userPublicKey: CryptoKey = await Rsa.importPublicPem(PEMKEYPAIR.publicKey);
    if(userRemotePublicKey !== userPublicKey) throw new Error('User public keys not match!');
    */

    const systemPublicKey: CryptoKey = await Rsa.importPublicPem(window.atob(remoteConsent.systemPublicKey));
    const systemVerify = await Rsa.verify(systemPublicKey, remoteConsent.systemSignature, window.btoa(remoteConsent.data));
    if (!systemVerify) throw new Error('System signature not valid!');

    return true;
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

  private static async buildConsentRequestBody(consent: ConsentData, isCreate: boolean): Promise<string> {
    let publicKey;
    let publicKeyHash;

    const cryptoKeyPair: CryptoKeyPair = await Rsa.importFromPem(PEMKEYPAIR);
    const data = {
      userAgent: navigator.userAgent,
      timestamp: Math.ceil(new Date().getTime() / 1000),
      specs: consent.specs,
      preferences: consent.preferences,
    };
    const signature = await Rsa.sign(cryptoKeyPair.privateKey!, window.btoa(JSON.stringify(data)));

    if (isCreate) {
      publicKey = PEMKEYPAIR.publicKey;
    } else {
      publicKeyHash = sha256(PEMKEYPAIR.publicKey).toString();
    }

    return JSON.stringify({
      data: JSON.stringify(data),
      signature,
      ...(publicKey !== undefined) && { publicKey },
      ...(publicKey === undefined) && { publicKeyHash },
    }, null, 0);
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
