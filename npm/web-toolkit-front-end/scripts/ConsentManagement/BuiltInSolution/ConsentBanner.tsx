/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import React from 'react';
import { ResponseConsent, EndpointList, PreferencesStatus } from './ConsentInterfaces';
import ConsentSdk from './ConsentSdk';
import CategoryListComponent from './components/CategoryListComponent';

interface Props {
  container: HTMLElement,
}

interface State {
  currentConsent: ResponseConsent | null,
  isOpen: boolean,
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
    };
  }

  componentDidMount = async () => {
    this.setState({
      currentConsent: await this.sdk.getCurrentConsent(),
    });

    if (this.state.currentConsent?.token === null) {
      this.setState({
        isOpen: true,
      });
    }

    this.createRequiredList();
  };

  private getEndpoints(): EndpointList {
    return JSON.parse(this.props.container.getAttribute('data-endpoints')!);
  }

  private updatePreferences(serviceName:string, newValue: boolean): void {
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
    const response:ResponseConsent = await this.sdk.registerConsent(this.state.currentConsent!);
    this.setState({
      currentConsent: response,
      isOpen: false,
    });
  }

  private acceptAllConsent(): void {
    const preferences = this.state.currentConsent?.data.preferences;

    for (const preferencesKey in preferences) {
      this.updatePreferences(preferencesKey, true);
    }



    this.saveConsent();
  }

  private acceptRequired(): void {
    const consentData = this.state.currentConsent!.data;

    consentData.specs.services.map((service) => {
      consentData.preferences[service.id] = this.checkIfRequired(service.category);
    });

    this.saveConsent();
  }

  render() {
    return (
      <>
        {this.state.currentConsent === null
          ? 'NO CONSENT'
          : <>
            <button onClick={() => this.setState({ isOpen: !this.state.isOpen })}>{this.state.isOpen ? 'Close' : 'Open'}Banner</button>
            {!this.state.isOpen ? ''
              : <div className="consent-body">
                <button onClick={() => this.saveConsent()}>Salva</button>
                <button onClick={() => this.acceptAllConsent()}>Accetta tutti</button>
                <button onClick={() => this.acceptRequired()}>Accetta solo necessari</button>

                <CategoryListComponent
                  consent={this.state.currentConsent.data.specs!}
                  preferencesStatus={this.state.currentConsent.data.preferences}
                  checkIfRequired={(serviceName:string) => this.checkIfRequired(serviceName)}
                  callback={(serviceName:string, newValue:boolean) => this.updatePreferences(serviceName, newValue)}
                />
              </div>
            }
          </>
        }
      </>
    );
  }
}
