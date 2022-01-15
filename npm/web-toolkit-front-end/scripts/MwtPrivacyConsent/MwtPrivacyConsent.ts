/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
import { ServicesList, SpecsConsents, SpecsHashed } from './MwtPrivacyConsentInterface';
import MwtPrivacyConsentSdk from './MwtPrivacyConsentSdk';

class MwtPrivacyConsent {
  private privacySdk = new MwtPrivacyConsentSdk();

  private specsConsents: SpecsConsents = {
    specs: {
      categories: [],
      services: [],
    },
    consent: new Map<string, boolean>(),
  };

  constructor(browserUi:any) {
    this.init(browserUi);
  }

  private async init(browserUi:any) {
    const getServices = await this.privacySdk.init(browserUi.parseEndpoints());
    Object.assign(this.specsConsents.specs.services, getServices.specs.services);
    Object.assign(this.specsConsents.specs.categories, getServices.specs.categories);

    getServices.specs.services.forEach((el: ServicesList) => {
      // TODO: Set true/false based on category 'required' attribute
      this.specsConsents.consent.set(el.id, true);
    });

    // eslint-disable-next-line no-console
    console.log('SpecsConsents: ', this.specsConsents);

    browserUi.init(this.specsConsents);
    this.listener();
  }

  private listener() {
    document.querySelector('section.privacyConsent')!
      .addEventListener('click', (event) => {
        const target = event.target as HTMLElement;
        if (target.classList.contains('privacy_consent_service')) {
          // eslint-disable-next-line no-console
          console.log('You selected: ', target.id);

          this.updateConsent(target.id, !target.classList.contains('selected'));
          target.classList.toggle('selected');
        }
      });

    document.querySelector('button.sendPrivacyConsent')!
      .addEventListener('click', () => {
        MwtPrivacyConsent.hashSpecs(this.specsConsents);
        this.privacySdk.sendConsent(MwtPrivacyConsent.hashSpecs(this.specsConsents));
      });
  }

  private updateConsent(serviceId: string, value: boolean) {
    if (this.specsConsents.specs.services.map((a) => a.id).find((e) => e === serviceId)) {
      this.specsConsents.consent.set(serviceId, value);
    }
    // eslint-disable-next-line no-console
    console.log(this.specsConsents.consent);
  }

  private static hashSpecs(specList: SpecsConsents) {
    const jsonObject = Object.fromEntries(specList.consent);

    // TODO: create an actual hash instead of stringify
    // Need to decide hash format
    const hashedSpecs:SpecsHashed = {
      consent: JSON.stringify(jsonObject),
      specsHash: JSON.stringify(specList.specs),
    };

    return hashedSpecs;
  }
}

let alreadyInitialized = false;

export default function createPrivacyConsentManager(browserUi: any) {
  if (alreadyInitialized) {
    throw new Error('This function must be called once, please store the returned object');
  }

  alreadyInitialized = true;

  return new MwtPrivacyConsent(browserUi);
}
