// Useful defaults

import browserUiDriver from './Vendor/Usercentrics/BrowserUiDriver';
import HCaptcha from './Vendor/HCaptcha';

// Initialize Usercentrics' Browser UI driver
browserUiDriver.init();

// Initialize HCaptcha inputs
const hCaptcha = new HCaptcha();
hCaptcha.init();
