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

export interface PreferencesStatus {
  [key: string]: boolean;
}

export interface ConsentSpecs {
  categories: CategorySpecs[];
  services: ServiceSpecs[];
}

export interface ResponseConsent {
  token: string | null;
  datetime: string | null;
  data: {
    specsHash: string | null;
    preferences: PreferencesStatus;
    specs: ConsentSpecs;
    userAgent: string | null;
  }
}

export interface ConsentRequestBody {
  specsHash: string;
  preferences: PreferencesStatus;
}

export interface ConsentLocalData {
  lastCheck: number;
  consent: ResponseConsent;
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
