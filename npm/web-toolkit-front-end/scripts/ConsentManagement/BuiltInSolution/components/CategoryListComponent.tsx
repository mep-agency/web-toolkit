/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
import React from 'react';
import I18n from '../I18n';
import { ConsentSpecs, PreferencesStatus } from '../ConsentInterfaces';

interface ConsentProps {
  consent: ConsentSpecs;
  checkIfRequired: (categoryName: string) => boolean;
  preferencesStatus: PreferencesStatus;
  callback: (serviceName: string, newValue: boolean) => void;
}

const CategoryListComponent = (props: ConsentProps) => {
  const handleCheck = (e: React.ChangeEvent<HTMLInputElement>, serviceId: string) => {
    props.consent.services.forEach((service) => {
      if (service.category === serviceId) {
        props.callback!(service.id, e.target.checked);
      }
    });
  };

  const checkIfChecked = (categoryId: string): boolean | undefined => {
    const valueArray = props.consent.services.map((service) => {
      if (service.category === categoryId) {
        return props.preferencesStatus[service.id];
      }
      return undefined;
    });

    if (valueArray.includes(false) && valueArray.includes(true)) {
      return undefined;
    }

    return valueArray.includes(true);
  };

  return (
      <dl key="category-list">
        {props.consent.categories.map((category) => (
            <div className="list-element" key={category.id}>
              <dt className={checkIfChecked(category.id) === true ? 'checked' : undefined }>
                <label htmlFor={category.id}>
                  {category.name}{category.required ? <> <span className="required">({I18n.required_message})</span></> : null}
                </label>
                  <input id={category.id}
                         type="checkbox"
                         ref={(input) => {
                           const inputEl = input;
                           if (inputEl) {
                             if (checkIfChecked(category.id) !== undefined) {
                               inputEl.checked = checkIfChecked(category.id)!;
                             } else {
                               inputEl.indeterminate = true;
                             }
                           }
                         }}
                         disabled={category.required}
                         onChange={(e) => handleCheck(e, category.id)}
                  />
              </dt>
              <dd>
                  <p className="text-container">{category.description}</p>
                  {
                    checkIfChecked(category.id) === undefined && <p className="half-category-text">{I18n.half_check_category}</p>
                  }
              </dd>

            </div>
        ))}
      </dl>
  );
};

export default CategoryListComponent;
