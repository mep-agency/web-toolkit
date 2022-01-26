/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
import React from 'react';
import { ConsentSpecs, PreferencesStatus } from '../ConsentInterfaces';
import ListItem from './ListItem';

interface ConsentProps {
  consent: ConsentSpecs;
  checkIfRequired: (categoryName: string) => boolean;
  preferencesStatus: PreferencesStatus;
  callback: (serviceName: string, newValue: boolean) => void
}

const ServiceListComponent = (props: ConsentProps) => (
    <>
        <ul>
            {props.consent !== undefined && props.consent.services.map((service, index) => (
                    <ListItem
                        key={index}
                        id={service.id}
                        name={service.name}
                        description={service.description}
                        checked={props.preferencesStatus[service.id]}
                        callback={props.checkIfRequired(service.category) ? null : (serviceName, newValue) => props.callback(serviceName, newValue)}
                    />
            ))}
        </ul>
    </>
);

export default ServiceListComponent;
