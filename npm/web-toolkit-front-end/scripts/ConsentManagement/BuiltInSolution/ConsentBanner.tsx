/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import React from 'react';
import { EndpointList, PreferencesStatus, ConsentData } from './ConsentInterfaces';
import ConsentSdk from './ConsentSdk';
import CategoryListComponent from './components/CategoryListComponent';
import ServiceListComponent from './components/ServiceListComponent';
import I18n from './I18n';
import '../../../styles/ConsentManagement/index.scss';

interface Props {
  container: HTMLElement,
  defaultStyle: boolean,
  defaultIcon: boolean,
  cacheExpiration?: number,
}

interface State {
  currentConsent: ConsentData | null,
  isOpen: boolean,
  enableTab: BannerStatus,
  locale: string,
}

enum BannerStatus {
  DEFAULT,
  CATEGORY,
  SERVICE,
}

export default class ConsentBanner extends React.Component<Props, State> {
  private readonly sdk!: ConsentSdk;

  private readonly requiredCategories: string[] = [];

  private changedAlert: boolean = false;

  constructor(props: Props) {
    super(props);
    this.sdk = new ConsentSdk(this.getEndpoints(), this.props.cacheExpiration);

    this.state = {
      currentConsent: null,
      isOpen: false,
      enableTab: BannerStatus.DEFAULT,
      locale: document.documentElement.lang,
    };
  }

  componentDidMount = async () => {
    const consent = await this.sdk.getCurrentConsent();

    if (consent === null) throw new Error('Couldn\'t get consent!');

    if (consent.specs.services.length === 0) {
      if (document.getElementById('consent-banner-trigger') !== null) {
        document.getElementById('consent-banner-trigger')!.hidden = true;
      }

      return;
    }

    if (document.getElementById('consent-banner-trigger') === null) {
      const newNode = document.createElement('button');
      newNode.id = 'consent-banner-trigger';
      newNode.innerText = 'Open';
      this.props.container.parentNode!.insertBefore(newNode, this.props.container.nextSibling);
    }

    this.setState({
      currentConsent: consent,
    });

    this.createRequiredList();

    if (this.state.currentConsent?.timestamp === null) {
      this.setState({
        isOpen: true,
        enableTab: BannerStatus.DEFAULT,
      });
    } else if (this.state.currentConsent?.timestamp === -1) {
      this.changedAlert = true;
      this.setState({
        isOpen: true,
        enableTab: BannerStatus.SERVICE,
      });
    }
    document.getElementById('consent-banner-trigger')!.addEventListener('click', () => {
      this.openPopup();
    });
  };

  openPopup(): void {
    this.setState({ isOpen: !this.state.isOpen, enableTab: BannerStatus.DEFAULT });
  }

  private getEndpoints(): EndpointList {
    return JSON.parse(this.props.container.getAttribute('data-endpoints')!);
  }

  private getPrivacyPolicy(): string {
    return this.props.container.getAttribute('data-privacy')!;
  }

  private getCookiePolicy(): string {
    return this.props.container.getAttribute('data-cookie')!;
  }

  private updatePreferences(serviceName: string, newValue: boolean): void {
    const consent = this.state.currentConsent!;

    if (consent.preferences[serviceName] === undefined) throw new Error('Service does not exist!');

    consent.preferences[serviceName] = newValue;

    this.setState({
      currentConsent: consent,
    });
  }

  private checkIfRequired(categoryName: string): boolean {
    return this.requiredCategories.includes(categoryName);
  }

  private createRequiredList(): void {
    const specs = this.state.currentConsent?.specs!;
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
    const response: ConsentData = await this.sdk.registerConsent(this.state.currentConsent!);
    this.setState({
      currentConsent: response,
      isOpen: false,
    });
  }

  private acceptAllConsent(): void {
    const preferences = this.state.currentConsent?.preferences;

    Object.entries(preferences!).forEach((preferenceElement) => {
      this.updatePreferences(preferenceElement[0], true);
    });

    this.saveConsent();
  }

  private acceptRequired(): void {
    const consentData = this.state.currentConsent!;

    consentData.specs.services.forEach((service) => {
      consentData.preferences[service.id] = this.checkIfRequired(service.category);
    });

    this.saveConsent();
  }

  private async closeAndSave(): Promise<void> {
    const consent: ConsentData = await this.sdk.getCurrentConsent();
    this.setState({
      currentConsent: consent,
    });
    await this.saveConsent();
  }

  private chooseBannerStatus(newType: BannerStatus): void {
    this.setState({ enableTab: newType });
  }

  render() {
    return (
      <>
        {!this.state.isOpen ? ''
          : <div className={`consent-body${this.props.defaultStyle ? ' mwt-default-style' : ''}`}>
              {this.state.enableTab !== BannerStatus.DEFAULT
                ? <div className="floating-window">
                  <div className="navigation">
                    <button aria-label={I18n.close_banner} className="close-button" onClick={() => this.closeAndSave()}/>
                  </div>
                  <div className="banner-header">
                    {
                      this.changedAlert
                        ? <p className="changed-alert">{I18n.changed_notice}</p>
                        : null
                    }
                    <p>{I18n.body}</p>
                    <div className="privacy-links">
                      <a href={this.getPrivacyPolicy()} target="_blank">{I18n.privacy_policy}</a>
                      <a href={this.getCookiePolicy()} target="_blank">{I18n.cookie_policy}</a>
                    </div>
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
                            consent={this.state.currentConsent!.specs!}
                            preferencesStatus={this.state.currentConsent!.preferences}
                            checkIfRequired={
                              (categoryName: string) => this.checkIfRequired(categoryName)
                            }
                            locale={this.state.locale}
                            callback={
                              (serviceName: string, newValue: boolean) => this.updatePreferences(
                                serviceName,
                                newValue,
                              )
                            }
                          />
                        </>
                      : <>
                          <ServiceListComponent
                            consent={this.state.currentConsent!.specs!}
                            preferencesStatus={this.state.currentConsent!.preferences}
                            checkIfRequired={
                              (categoryName: string) => this.checkIfRequired(categoryName)
                            }
                            locale={this.state.locale}
                            callback={
                              (serviceName: string, newValue: boolean) => this.updatePreferences(
                                serviceName,
                                newValue,
                              )
                            }
                          />
                        </>
                  }
                  <div className="button-list">
                    <button className="accept-all" onClick={() => this.acceptAllConsent()}>{I18n.accept_all}</button>
                    <button className="accept-required" onClick={() => this.acceptRequired()}>{I18n.accept_required}</button>
                    <button className="save-button" onClick={() => this.saveConsent()}>{I18n.save}</button>
                  </div>
                </div>
                : <div className="docked-window">
                    <button aria-label={I18n.close_banner} className="close-button" onClick={() => this.closeAndSave()}/>
                    <div className="illustration">
                      <div className={this.props.defaultIcon ? 'default-cookie' : 'cookie-element'}/>
                    </div>
                    <div className="body">
                      <p className="privacy-body">{I18n.body}</p>
                      <div className="privacy-links">
                        <a href={this.getPrivacyPolicy()} target="_blank">{I18n.privacy_policy}</a>
                        <a href={this.getCookiePolicy()} target="_blank">{I18n.cookie_policy}</a>
                      </div>
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
