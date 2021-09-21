import privacyManager from '../../Privacy/PrivacyManager';

export default class HCaptcha {
  private isInitialized = false;

  public init(serviceName: string = 'hCaptcha', reloadOnDisable: boolean = true) {
    privacyManager.addConsentStatusListener(
      serviceName,
      (newValue, isInit) => {
        if (newValue) {
          if (this.isInitialized) {
            throw new Error('Cannot initialize multiple times!');
          }

          this.isInitialized = true;

          const hCaptchaScript = document.createElement('script');
          hCaptchaScript.src = 'https://hcaptcha.com/1/api.js';

          document.body.append(hCaptchaScript);

          return;
        }

        if (!isInit && reloadOnDisable) {
          document.location.reload();
        }
      });
  }
}
