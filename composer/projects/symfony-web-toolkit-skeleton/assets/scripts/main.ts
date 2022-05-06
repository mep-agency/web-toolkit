/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import '../styles/main.scss';

import ConsentIndex from '@mep-agency/web-toolkit-front-end/scripts/ConsentManagement/BuiltInSolution/ConsentIndex';

// The following line imports some useful tools with a default configuration
// import '@mep-agency/web-toolkit-front-end';

// The following line initializes the built-in consent manager with default styling and settings.
ConsentIndex({ defaultStyle: true, defaultIcon: true }, 30000);
