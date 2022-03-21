/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
export interface EndpointList {
  getSpecs: string;
  getHistory: string;
  consentGet: string;
  consentCreate: string;
}

export interface PreferencesStatus {
  [key: string]: boolean;
}

export interface ConsentSpecs {
  categories: CategorySpecs[];
  services: ServiceSpecs[];
}

export interface ConsentData {
  timestamp: number | null;
  previousConsentDataHash: string | null;
  preferences: PreferencesStatus;
  specs: ConsentSpecs;
}

export interface LocalConsent {
  publicKey?: string;
  publicKeyHash?: string;
  signature: string | null;
  data: string;
}

export interface ResponseConsent {
  systemPublicKey: string;
  systemSignature: string;
  userPublicKey: string;
  userSignature: string;
  data: string;
}

export interface ConsentLocalData {
  lastCheck: number;
  consent: ResponseConsent;
}

export interface CategorySpecs {
  id: string;
  name: string;
  description: string;
  required: boolean;
}

export interface ServiceSpecs {
  id: string;
  name: string;
  description: string;
  category: string;
}
