/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import { EndpointList } from './MwtPrivacyConsentSdkInterface';

export default class BrowserBanner {
  private bannerContainer:HTMLElement | null;

  public constructor() {
    this.bannerContainer = document.getElementById('data-pcm-container');
  }

  public parseEndpoints() {
    return JSON.parse(this.bannerContainer?.getAttribute('data-endpoints')!) as EndpointList;
  }
}
