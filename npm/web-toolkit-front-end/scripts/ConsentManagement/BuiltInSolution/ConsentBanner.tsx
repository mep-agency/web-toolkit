/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import React from 'react';
import {Consent, EndpointList} from './ConsentInterfaces';
import ConsentSdk from './ConsentSdk';

interface Props {
  container: HTMLElement,
}

interface State {
  currentConsent: Consent|null,
}


export default class ConsentBanner extends React.Component<Props, State> {
  private readonly sdk!: ConsentSdk;

  constructor(props: Props) {
    super(props);

    this.sdk = new ConsentSdk(this.getEndpoints());

    this.state = {
      currentConsent: null,
    };
  }

  componentDidMount = async () => {
    this.setState({
      currentConsent: await this.sdk.getCurrentConsent(),
    });
  }

  private getEndpoints() {
    return JSON.parse(this.props.container.getAttribute('data-endpoints')!) as EndpointList;
  }

  render() {
    return (
      <>
        {this.state.currentConsent === null ?
          'NO CONSENT'
          :
          <>
            {JSON.stringify(this.state.currentConsent)}
          </>
        }
      </>
    );
  }
}
