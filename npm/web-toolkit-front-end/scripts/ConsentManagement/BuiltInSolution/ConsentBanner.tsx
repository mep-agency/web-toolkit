/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import React from 'react';
import { LocalConsent, EndpointList, PreferencesStatus } from './ConsentInterfaces';
import ConsentSdk from './ConsentSdk';
import CategoryListComponent from './components/CategoryListComponent';
import ServiceListComponent from './components/ServiceListComponent';
import I18n from './I18n';
import '../../../styles/ConsentManagement/index.scss';
// TODO: temporary asset
const CookieLogo = require('../../../images/cookie-logo.svg') as string;

interface Props {
  container: HTMLElement,
  activator: HTMLElement
}

interface State {
  currentConsent: LocalConsent | null,
  isOpen: boolean,
  enableTab: BannerStatus,
}

enum BannerStatus {
  DEFAULT,
  CATEGORY,
  SERVICE,
}

export default class ConsentBanner extends React.Component<Props, State> {
  private readonly sdk!: ConsentSdk;

  private readonly requiredCategories: string[] = [];

  constructor(props: Props) {
    super(props);
    this.sdk = new ConsentSdk(this.getEndpoints());

    this.state = {
      currentConsent: null,
      isOpen: false,
      enableTab: BannerStatus.DEFAULT,
    };
  }

  componentDidMount = async () => {
    const consent = await this.sdk.getCurrentConsent();
    if (consent === null) throw new Error('Couldn\'t get consent!');

    this.setState({
      currentConsent: await this.sdk.getCurrentConsent(),
    });

    this.createRequiredList();

    if (this.state.currentConsent?.publicKeyHash === undefined) {
      this.setState({
        isOpen: true,
      });
    }

    this.props.activator.addEventListener('click', () => {
      this.openPopup();
    });
  };

  openPopup(): void {
    this.setState({ isOpen: !this.state.isOpen });
  }

  private getEndpoints(): EndpointList {
    return JSON.parse(this.props.container.getAttribute('data-endpoints')!);
  }

  private updatePreferences(serviceName: string, newValue: boolean): void {
    const consent = this.state.currentConsent!;

    if (consent.data.preferences[serviceName] === undefined) throw new Error('Service does not exist!');

    consent.data.preferences[serviceName] = newValue;

    this.setState({
      currentConsent: consent,
    });
  }

  private checkIfRequired(categoryName: string): boolean {
    return this.requiredCategories.includes(categoryName);
  }

  private createRequiredList(): void {
    const specs = this.state.currentConsent?.data.specs!;
    const servicesStatus: PreferencesStatus = {};

    specs.categories.forEach((categorySpecs) => {
      if (categorySpecs.required) {
        this.requiredCategories.push(categorySpecs.id);
      }
    });

    specs.services.forEach((serviceSpecs) => {
      servicesStatus[serviceSpecs.id] = this.requiredCategories.includes(serviceSpecs.category);
    });
  }

  private async saveConsent(): Promise<void> {
    const response: LocalConsent = await this.sdk.registerConsent(this.state.currentConsent!);
    this.setState({
      currentConsent: response,
      isOpen: false,
    });
  }

  private acceptAllConsent(): void {
    const preferences = this.state.currentConsent?.data.preferences;

    Object.entries(preferences!).forEach((preferenceElement) => {
      this.updatePreferences(preferenceElement[0], true);
    });

    this.saveConsent();
  }

  private acceptRequired(): void {
    const consentData = this.state.currentConsent!.data;

    consentData.specs.services.forEach((service) => {
      consentData.preferences[service.id] = this.checkIfRequired(service.category);
    });

    this.saveConsent();
  }

  private chooseBannerStatus(newType: BannerStatus): void {
    this.setState({ enableTab: newType });
  }

  render() {
    return (
      <>
        {!this.state.isOpen ? ''
          : <div className="consent-body">
              {this.state.enableTab !== BannerStatus.DEFAULT
                ? <div className="floating-window">
                  <div className="banner-header">
                    <h2>{I18n.title}</h2>
                    <p>{I18n.body}</p>
                  </div>
                  <div className="banner-status-buttons">
                    <button className="category-button" disabled={this.state.enableTab === BannerStatus.CATEGORY}
                            onClick={() => this.chooseBannerStatus(BannerStatus.CATEGORY)}>
                      {I18n.categories}
                    </button>
                    <button className="service-button" disabled={this.state.enableTab === BannerStatus.SERVICE}
                            onClick={() => this.chooseBannerStatus(BannerStatus.SERVICE)}>
                      {I18n.services}
                    </button>
                  </div>
                  {
                    this.state.enableTab === BannerStatus.CATEGORY
                      ? <>
                          <CategoryListComponent
                            consent={this.state.currentConsent!.data.specs!}
                            preferencesStatus={this.state.currentConsent!.data.preferences}
                            checkIfRequired={(categoryName: string) => this.checkIfRequired(categoryName)}
                            callback={(serviceName: string, newValue: boolean) => this.updatePreferences(serviceName, newValue)}
                          />
                        </>
                      : <>
                          <ServiceListComponent
                            consent={this.state.currentConsent!.data.specs!}
                            preferencesStatus={this.state.currentConsent!.data.preferences}
                            checkIfRequired={(categoryName: string) => this.checkIfRequired(categoryName)}
                            callback={(serviceName: string, newValue: boolean) => this.updatePreferences(serviceName, newValue)}
                          />
                        </>
                  }
                  <div className={'button-list'}>
                    <button className="save-button" onClick={() => this.saveConsent()}>{I18n.save}</button>
                    <button className="accept-required" onClick={() => this.acceptRequired()}>{I18n.accept_required}</button>
                    <button className="accept-all" onClick={() => this.acceptAllConsent()}>{I18n.accept_all}</button>
                  </div>
                </div>
                : <div className="docked-window">
                  <div className="illustration">
                    <img src={CookieLogo} alt="Cookie Logo"/>
                  </div>
                  <div className="body">
                    <p>{I18n.body}</p>
                    <button className="preferences" onClick={() => this.chooseBannerStatus(BannerStatus.CATEGORY)}>
                      {I18n.open_pref}
                    </button>
                  </div>
                  <div className="button-list">
                    <button className="preferences" onClick={() => this.chooseBannerStatus(BannerStatus.CATEGORY)}>
                      {I18n.open_pref}
                    </button>
                    <button className="accept-required" onClick={() => this.acceptRequired()}>{I18n.accept_required}</button>
                    <button className="accept-all" onClick={() => this.acceptAllConsent()}>{I18n.accept_all}</button>
                  </div>
                </div>
              }
            </div>
        }
      </>
    );
  }
}
