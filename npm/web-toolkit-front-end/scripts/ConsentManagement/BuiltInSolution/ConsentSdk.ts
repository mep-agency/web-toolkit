/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import sha256 from 'crypto-js/sha256';
import * as Rsa from '../../Util/Rsa';

import * as Base64 from '../../Util/Base64';
import {
  CategorySpecs,
  ConsentData,
  ConsentLocalData,
  ConsentSpecs,
  EndpointList,
  LocalConsent,
  PreferencesStatus,
  ResponseConsent,
  ServiceSpecs,
} from './ConsentInterfaces';

const DEFAULT_EXPIRATION_DELAY = 300000; // Five minutes expiration delay in ms
const LOCAL_STORAGE_KEY = 'mwt_privacy_consent';
const PEM_RSA_STORAGE_KEY = 'mwt_privacy_consent_rsa_pem';
const HASH_PLACEHOLDER = '0000000000000000000000000000000000000000000000000000000000000000';
const ERROR_LIST = [
  'invalid_signature',
  'cannot_update_consent_for_unexisting_public_key',
  'previous_consent_hash_has_to_be_null',
  'previous_consent_hash_does_not_match',
];
let PEMKEYPAIR: Rsa.PemKeyPair;

export default class ConsentSdk {
  constructor(
    private apiUrls: EndpointList,
    private cacheExpiration: number = DEFAULT_EXPIRATION_DELAY,
  ) {
    ConsentSdk.getPemKeyPair();
  }

  async getCurrentConsent(): Promise<ConsentData> {
    if (PEMKEYPAIR === undefined) await ConsentSdk.getPemKeyPair();

    if (ConsentSdk.getPublicKeyHash() === undefined) {
      return this.buildNewConsent();
    }

    let localStorageData = localStorage.getItem(LOCAL_STORAGE_KEY);
    if (localStorageData === null) {
      const updatedConsent = await this.refreshConsent();
      if (updatedConsent !== undefined) {
        return updatedConsent;
      }
      localStorageData = localStorage.getItem(LOCAL_STORAGE_KEY)!;
    }

    let localStorageConsentData = JSON.parse(localStorageData) as ConsentLocalData;
    const timeDif = ConsentSdk.getCurrentTime() - localStorageConsentData.lastCheck;

    if (timeDif >= this.cacheExpiration) {
      const updatedConsent = await this.refreshConsent();
      if (updatedConsent !== undefined) {
        return updatedConsent;
      }

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
        if (localStorageConsent.data === JSON.stringify(temporaryConsent)) {
          return JSON.parse(localStorageConsent.data) as ConsentData;
        }
        isNewConsent = false;
      }
    } else {
      await ConsentSdk.getPemKeyPair();
    }

    const builtConsent = await ConsentSdk.buildConsentRequestBody(temporaryConsent, isNewConsent);
    const response = await fetch(this.apiUrls.consentCreate, {
      method: 'POST',
      body: builtConsent,
    });
    const responseMessage = await response.json();

    if (!response.ok) {
      if (ERROR_LIST.includes(responseMessage.code)) {
        ConsentSdk.cleanupStorage();
        return this.registerConsent(temporaryConsent);
      }

      // The response is not in the manageable list of errors
      // so the local consent remain untouched and force
      // a refresh through the Banner.
      return {
        preferences: {},
        previousConsentDataHash: null,
        specs: {
          categories: [],
          services: [],
        },
        timestamp: -1,
      };
    }

    const consent: ResponseConsent = responseMessage;

    await ConsentSdk.checkConsent(consent, JSON.parse(builtConsent) as LocalConsent);

    if (isNewConsent) {
      PEMKEYPAIR.publicKeyHash = sha256(PEMKEYPAIR.publicKey).toString();
      localStorage.setItem(PEM_RSA_STORAGE_KEY, JSON.stringify(PEMKEYPAIR));
    }

    ConsentSdk.storeConsent(consent);

    return JSON.parse(consent.data) as ConsentData;
  }

  private static cleanupStorage() {
    localStorage.removeItem(PEM_RSA_STORAGE_KEY);
    localStorage.removeItem(LOCAL_STORAGE_KEY);
    PEMKEYPAIR.publicKeyHash = undefined;
  }

  private static async checkConsent(
    remoteConsent: ResponseConsent,
    sentConsent: LocalConsent,
  ): Promise<void> {
    if (sentConsent.signature !== remoteConsent.userSignature) throw new Error('User signatures not match!');

    if (sentConsent.data !== remoteConsent.data) throw new Error('Data not match!');

    if (Base64
      .decode(remoteConsent.userPublicKey)
      .includes(
        PEMKEYPAIR
          .publicKey
          .replace(/.{64}/g, '$&\n'),
      )
    ) throw new Error('User public keys not match!');

    const systemVerify = await Rsa.verify(
      await Rsa.importPublicPem(Base64.decode(remoteConsent.systemPublicKey)),
      remoteConsent.systemSignature,
      Base64.encode(remoteConsent.data),
    );

    if (!systemVerify) throw new Error('System signature not valid!');
  }

  private async refreshConsent(): Promise<ConsentData | undefined> {
    const response = await fetch(ConsentSdk.generateUrl(this.apiUrls.consentGet), {
      method: 'GET',
    });
    const consent: ResponseConsent = await response.json();
    const remoteSpecs = await this.getSpecs();

    if (JSON.stringify(JSON.parse(consent.data).specs) !== JSON.stringify(remoteSpecs)) {
      return ConsentSdk.updateConsent(JSON.parse(consent.data), remoteSpecs);
    }

    ConsentSdk.storeConsent(consent);

    return undefined;
  }

  private static updateConsent(consentData: ConsentData, remoteSpecs: ConsentSpecs): ConsentData {
    let timestampValue = null;
    const consentSpecs = consentData.specs;
    const consentPreferences = consentData.preferences;
    let changedServices: ServiceSpecs[] = [];
    let changedCategories: CategorySpecs[] = [];
    const requiredCategories: string[] = [];
    let newConsentPreferences: PreferencesStatus = {};

    if (JSON.stringify(consentSpecs.categories) !== JSON.stringify(remoteSpecs.categories)) {
      changedCategories = remoteSpecs.categories.filter(
        (remoteCategory) => (consentSpecs.categories.findIndex(
          (consentCategory) => this.checkCategoryEquality(remoteCategory, consentCategory),
        ) < 0));
    }

    if (JSON.stringify(consentSpecs.services) !== JSON.stringify(remoteSpecs.services)) {
      changedServices = remoteSpecs.services.filter(
        (remoteService) => (consentSpecs.services.findIndex(
          (consentService) => this.checkServiceEquality(consentService, remoteService),
        ) < 0));
    }

    if (
      (JSON.stringify(consentSpecs.categories) !== JSON.stringify(remoteSpecs.categories))
      || (JSON.stringify(consentSpecs.services) !== JSON.stringify(remoteSpecs.services))
    ) {
      timestampValue = -1;

      remoteSpecs.categories.forEach((categorySpecs) => {
        if (categorySpecs.required) {
          requiredCategories.push(categorySpecs.id);
        }
      });

      remoteSpecs.services.forEach((el) => {
        if (
          consentPreferences[el.id] === undefined
          || changedServices.includes(el)
          || changedCategories.filter((cat) => cat.id === el.category).length === 1
          || requiredCategories.includes(el.category)
        ) {
          newConsentPreferences[el.id] = requiredCategories.includes(el.category);
          return;
        }

        newConsentPreferences[el.id] = consentPreferences[el.id];
      });
    } else {
      newConsentPreferences = consentPreferences;
    }

    return {
      timestamp: timestampValue,
      previousConsentDataHash: consentData.previousConsentDataHash,
      preferences: newConsentPreferences,
      specs: remoteSpecs,
    };
  }

  private static checkServiceEquality(
    consentService: ServiceSpecs,
    remoteService: ServiceSpecs,
  ): boolean {
    return consentService.id === remoteService.id
      && consentService.category === remoteService.category
      && JSON.stringify(consentService.names) === JSON.stringify(remoteService.names)
      && JSON.stringify(consentService.descriptions) === JSON.stringify(remoteService.descriptions);
  }

  private static checkCategoryEquality(
    consentCat: CategorySpecs,
    remoteCat: CategorySpecs,
  ): boolean {
    return consentCat.id === remoteCat.id
      && consentCat.required === remoteCat.required
      && JSON.stringify(consentCat.names) === JSON.stringify(remoteCat.names)
      && JSON.stringify(consentCat.descriptions) === JSON.stringify(remoteCat.descriptions);
  }

  private async getSpecs(): Promise<ConsentSpecs> {
    const response = await fetch(this.apiUrls.getSpecs, {
      method: 'GET',
    });

    return await response.json() as ConsentSpecs;
  }

  private static getPublicKeyHash(): string | undefined {
    return PEMKEYPAIR.publicKeyHash;
  }

  private static getCurrentTime(): number {
    return (new Date()).getTime();
  }

  private async buildNewConsent(): Promise<ConsentData> {
    const specs: ConsentSpecs = await this.getSpecs();
    const servicesStatus: PreferencesStatus = {};
    const requiredCategories: string[] = [];

    localStorage.removeItem(LOCAL_STORAGE_KEY);

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
      timestamp: Math.ceil(new Date().getTime() / 1000),
      specs: consent.specs,
      preferences: consent.preferences,
    };
    const signature = await Rsa.sign(
      cryptoKeyPair.privateKey!,
      Base64.encode(JSON.stringify(data)),
    );

    return JSON.stringify({
      data: JSON.stringify(data),
      signature,
      ...(publicKey !== undefined) && { publicKey },
      ...(publicKey === undefined) && { publicKeyHash },
    }, null, 0);
  }

  private static async getPemKeyPair(): Promise<void> {
    if (localStorage.getItem(PEM_RSA_STORAGE_KEY) !== null) {
      PEMKEYPAIR = JSON.parse(localStorage.getItem(PEM_RSA_STORAGE_KEY)!) as Rsa.PemKeyPair;
      return;
    }
    PEMKEYPAIR = await Rsa.exportToPem(await Rsa.generateKey());
    localStorage.setItem(PEM_RSA_STORAGE_KEY, JSON.stringify(PEMKEYPAIR));
  }
}
