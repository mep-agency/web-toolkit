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
  consentCreate: string;
  getHistory: string;
  consentGet: string;
  consentUpdate: string;
}

export interface MwtPrivacyConsentSdkInterface {
  /*
     Restituisce il consenso corrente
   */
  getCurrentConsent(): Promise<Consent>;

  /*
     Invia i consensi creati al server remoto
   */
  registerConsent(consentElement:Consent): Promise<Consent>;
}

export interface ServicesStatus {
  [key: string]: boolean;
}

export interface ConsentSpecs {
  categories: CategorySpecs[];
  services: ServiceSpecs[];
}

export interface ConsentRequestBody {
  specsHash: string;
  // TODO: Change consent to servicesStatus
  consent: ServicesStatus;
}

export interface Consent {
  token: string | null;
  specs: ConsentSpecs | null;
  // TODO: Change consent to servicesStatus
  consent: ServicesStatus;
}

export interface ConsentLocalData {
  lastCheck: number;
  consent: Consent;
}

interface CategorySpecs {
  id: string;
  name: string;
  description: string;
  required: boolean;
}

interface ServiceSpecs {
  id: string;
  name: string;
  description: string;
  category: string;
}
