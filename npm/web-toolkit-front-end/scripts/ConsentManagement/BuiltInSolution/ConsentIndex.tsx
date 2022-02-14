/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import ReactDOM from 'react-dom';
import ConsentBanner from './ConsentBanner';

function ConsentIndex(cacheExpirationMs?: number) {
  document.addEventListener('DOMContentLoaded', () => {
    const bannerContainer = document.getElementById('consent-banner-container')!;
    ReactDOM.render(
            <ConsentBanner container={bannerContainer}
                           cacheExpiration={cacheExpirationMs || undefined}
            />, bannerContainer);
  });
}

export default ConsentIndex;
