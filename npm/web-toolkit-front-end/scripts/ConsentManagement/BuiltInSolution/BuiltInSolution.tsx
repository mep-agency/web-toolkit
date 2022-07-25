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

function BuiltInSolution({ defaultIcon = true }, cacheExpirationMs?: number) {
  document.addEventListener('DOMContentLoaded', () => {
    const bannerContainer = document.getElementById('mwt-consent-banner-container')!;

    ReactDOM.render(
            <ConsentBanner container={bannerContainer}
                           cacheExpiration={cacheExpirationMs || undefined}
                           defaultIcon={defaultIcon}
            />, bannerContainer);
  });
}

export default BuiltInSolution;
