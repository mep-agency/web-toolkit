/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const INITIALIZED_FIELD_DATA_ATTRIBUTE = 'data-mwt-initialized';

interface FieldConstructor {
  new (input: HTMLInputElement): Field;
}

export interface Field {
  init(): void;
}

class FieldsManager {
  private readonly fieldClasses: { [inputCssClass: string]: FieldConstructor } = {};

  public registerField(inputCssClass: string, fieldClass: FieldConstructor) {
    this.fieldClasses[`input.${inputCssClass}:not([${INITIALIZED_FIELD_DATA_ATTRIBUTE}])`] = fieldClass;
  }

  public initFields() {
    for (const selector of Object.keys(this.fieldClasses)) {
      for (const input of document.querySelectorAll<HTMLInputElement>(selector)) {
        const field = new this.fieldClasses[selector](input);

        input.setAttribute(INITIALIZED_FIELD_DATA_ATTRIBUTE, 'true');

        field.init();
      }
    }
  }
}

const FieldsManagerInstance = new FieldsManager();

document.addEventListener('DOMContentLoaded', () => FieldsManagerInstance.initFields());
document.addEventListener('ea.collection.item-added', () => FieldsManagerInstance.initFields());

export default FieldsManagerInstance;
