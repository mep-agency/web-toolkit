import ConsentStatusChangedCallbackType from './ConsentStatusChangedCallbackType';
import ConsentStatusChangedEvent from './ConsentStatusChangedEvent';

class PrivacyManager {
  private events: EventTarget = document.createElement('div');

  private consentStatusByService: Map<string, boolean> = new Map<string, boolean>();

  public updateService(service: string, newValue: boolean) {
    if (this.consentStatusByService.get(service) !== newValue) {
      const isInit = this.consentStatusByService.get(service) === undefined;
      this.consentStatusByService.set(service, newValue);

      this.events.dispatchEvent(
        new ConsentStatusChangedEvent(PrivacyManager.buildEventKey(service), {
          detail: {
            value: newValue,
            isInit,
          },
        }),
      );
    }
  }

  public updateStatus(consentStatusMap: Map<string, boolean>) {
    for (const service of consentStatusMap.keys()) {
      this.updateService(service, consentStatusMap.get(service)!);
    }
  }

  public addConsentStatusListener(
    serviceName: string,
    callback: ConsentStatusChangedCallbackType,
    options?: AddEventListenerOptions | boolean,
  ) {
    this.events.addEventListener(PrivacyManager.buildEventKey(serviceName), {
      handleEvent(evt: ConsentStatusChangedEvent) {
        callback(evt.detail.value, evt.detail.isInit);
      },
    }, options);

    // Run the callback if the status is already available...
    if (this.consentStatusByService.has(serviceName)) {
      callback(this.consentStatusByService.get(serviceName)!, true);
    }
  }

  private static buildEventKey(service: string) {
    return `consent_status_changed_${service}`;
  }
}

const privacyManager = new PrivacyManager();

export default privacyManager;
