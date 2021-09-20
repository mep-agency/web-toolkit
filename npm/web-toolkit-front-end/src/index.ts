// Useful defaults

import browserUiUtil from './Vendor/Usercentrics/BrowserUiUtil';
import HCaptcha from './Vendor/HCaptcha';

// Initialize Usercentrics' Browser UI service
browserUiUtil.init();

// Initialize HCaptcha inputs
const hCaptcha = new HCaptcha();
hCaptcha.init();
