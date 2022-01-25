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
  checkIfRequired: (serviceName:string) => boolean;
  preferencesStatus: PreferencesStatus;
  callback: (serviceName:string, newValue:boolean) => void
}

const CategoryListComponent = (props:ConsentProps) => (
        <>
            {props.consent !== undefined && props.consent.categories.map((category, index) => (
                    <div key={index}>
                        <h2>{category.name}{category.required ? ' - REQUIRED' : ''}</h2>
                        <ul key={category.id}>
                            {props.consent !== undefined && props.consent.services.map((service) => {
                              if (service.category === category.id) {
                                return (
                                    <ListItem
                                        key={service.id}
                                        id={service.id}
                                        name={service.name}
                                        description={service.description}
                                        checked={props.preferencesStatus[service.id]}
                                        callback={props.checkIfRequired(category.id) ? null : (serviceName, newValue) => props.callback(serviceName, newValue)}
                                    />
                                );
                              }
                            })}
                        </ul>
                    </div>
            ))}
        </>
);

export default CategoryListComponent;
