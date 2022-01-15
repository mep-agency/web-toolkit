/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
import { EndpointList } from './MwtPrivacyConsentSdkInterface';
import { SpecsList } from './MwtPrivacyConsentInterface';

class BrowserBanner {
  private bannerContainer:HTMLElement | null;

  specs:SpecsList = {
    services: [],
    categories: [],
  };

  public constructor() {
    this.bannerContainer = document.getElementById('data-pcm-container');
  }

  public init(specList: SpecsList) {
    this.specs = specList;

    // TODO: Show specs inside banner
  }

  public parseEndpoints() {
    const endpointList:EndpointList = new EndpointList();

    return Object.assign(endpointList, JSON.parse(this.bannerContainer?.getAttribute('data-endpoints')!));
  }
}

let alreadyInitialized = false;

export default function createBrowserUiDriver() {
  if (alreadyInitialized) {
    throw new Error('This function must be called once, please store the returned object');
  }

  alreadyInitialized = true;

  return new BrowserBanner();
}
