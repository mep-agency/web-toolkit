import browserUiUtil from '../Usercentrics/BrowserUiUtil';

export default class HCaptcha {
  private isInitialized = false;

  public init(serviceName: string = 'hCaptcha', reloadOnDisable: boolean = true) {
    browserUiUtil.addConsentListener((e) => {
      if (e[serviceName] === true) {
        this.isInitialized = true;

        const hCaptchaScript = document.createElement('script');
        hCaptchaScript.src = 'https://hcaptcha.com/1/api.js';

        document.body.append(hCaptchaScript);

        return false;
      }

      return this.isInitialized && reloadOnDisable;
    });
  }
}
