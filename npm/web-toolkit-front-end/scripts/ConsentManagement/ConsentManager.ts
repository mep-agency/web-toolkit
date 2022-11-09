/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import ConsentStatusChangedCallbackType from './ConsentStatusChangedCallbackType';
import ServiceConsentStatusChangedEvent from './ServiceConsentStatusChangedEvent';
import ConsentManagementDriverInterface from './ConsentManagementDriverInterface';
import DriverConsentStatusChangedEvent from './DriverConsentStatusChangedEvent';
import ConsentManagerInterface from './ConsentManagerInterface';

class ConsentManager implements ConsentManagerInterface {
  private consentManagementDriver!: ConsentManagementDriverInterface;

  private events: EventTarget = document.createElement('div');

  private consentStatusByService: Map<string, boolean> = new Map<string, boolean>();

  public registerDriver(consentManagementDriver: ConsentManagementDriverInterface) {
    if (this.consentManagementDriver !== undefined) {
      throw new Error('Driver already registered.');
    }

    this.consentManagementDriver = consentManagementDriver;

    this.consentManagementDriver.events.addEventListener(
      this.consentManagementDriver.STATUS_UPDATE_EVENT_NAME,
      {
        handleEvent: (event: DriverConsentStatusChangedEvent) => {
          this.updateFullConsentStatus(event.detail.newConsentStatus);
        },
      },
    );

    this.consentManagementDriver.init();
  }

  public addConsentStatusListener(
    serviceNameOrNames: string | string[],
    callback: ConsentStatusChangedCallbackType,
    options?: AddEventListenerOptions | boolean,
  ) {
    this.checkDriverOrThrow();

    const serviceNames: string[] = typeof serviceNameOrNames === 'string' ? [serviceNameOrNames] : serviceNameOrNames;
    const servicesStatus = new Map<string, boolean>(serviceNames.map((key) => [key, false]));
    const areAllEnabled = () => Array
      .from(servicesStatus.values())
      .reduce((value, newValue) => value && newValue, true);

    let thereIsAtLeastOneAvailable = false;
    serviceNames.forEach((serviceName) => {
      if (this.consentStatusByService.has(serviceName)) {
        thereIsAtLeastOneAvailable = true;

        servicesStatus.set(serviceName, this.consentStatusByService.get(serviceName)!);
      }

      this.events.addEventListener(ConsentManager.buildEventKey(serviceName), {
        handleEvent(event: ServiceConsentStatusChangedEvent) {
          servicesStatus.set(serviceName, event.detail.value);

          callback(areAllEnabled(), event.detail.isInit);
        },
      }, options);
    });

    // Run the callback if the status is already available...
    if (thereIsAtLeastOneAvailable) {
      callback(areAllEnabled(), true);
    }
  }

  public async openPreferencesPanel() {
    await this.getConsentManagementDriver().openPreferencesPanel();
  }

  public async closePreferencesPanel() {
    await this.getConsentManagementDriver().closePreferencesPanel();
  }

  private getConsentManagementDriver(): ConsentManagementDriverInterface {
    this.checkDriverOrThrow();

    return this.consentManagementDriver;
  }

  private checkDriverOrThrow(): void {
    if (this.consentManagementDriver === undefined) {
      throw new Error('You can\'t use the consent manager before registering a driver.');
    }
  }

  private static buildEventKey(service: string) {
    return `consent_status_changed_${service}`;
  }

  private updateServiceConsentStatus(service: string, newValue: boolean) {
    if (this.consentStatusByService.get(service) !== newValue) {
      const isInit = this.consentStatusByService.get(service) === undefined;
      this.consentStatusByService.set(service, newValue);

      this.events.dispatchEvent(
        new ServiceConsentStatusChangedEvent(ConsentManager.buildEventKey(service), {
          detail: {
            value: newValue,
            isInit,
          },
        }),
      );
    }
  }

  private updateFullConsentStatus(consentStatusMap: Map<string, boolean>) {
    for (const service of consentStatusMap.keys()) {
      this.updateServiceConsentStatus(service, consentStatusMap.get(service)!);
    }
  }
}

export default new ConsentManager();
