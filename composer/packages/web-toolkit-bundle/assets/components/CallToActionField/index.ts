/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {
  BlockTool,
  BlockToolConstructable,
  BlockToolData,
} from '@editorjs/editorjs';

import './call-to-action.scss';

interface CallToActionData {
  buttonText: string;
  buttonUrl: string;
  additionalText?: string;
  cssPreset?: string;
}

interface CustomConfigData {
  api: any;
  block: Object;
  config: {
    cssPresetChoices: string[] | null;
  },
  data: CallToActionData;
}

class CallToAction implements BlockTool {
  public data: CustomConfigData;

  constructor(params: CustomConfigData) {
    this.data = params;
  }

  static get toolbox() {
    return {
      title: 'Call To Action',
      icon: '<svg height="100%" stroke-miterlimit="10" style="fill-rule:nonzero;clip-rule:evenodd;stroke-linecap:round;stroke-linejoin:round;" viewBox="0 0 21 21" width="100%" xml:space="preserve" xmlns="http://www.w3.org/2000/svg"><g opacity="1"><path d="M16.5 14.5L16.5 6.5C16.5 5.39543 15.6046 4.5 14.5 4.5L6.5 4.5C5.39543 4.5 4.5 5.39543 4.5 6.5L4.5 14.5C4.5 15.6046 5.39543 16.5 6.5 16.5L14.5 16.5C15.6046 16.5 16.5 15.6046 16.5 14.5Z" fill="none" fill-rule="evenodd" opacity="1" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/><path d="M10.5 7.5L10.5 13.556" fill="none" fill-rule="evenodd" opacity="1" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/><path d="M13.5 10.5L7.5 10.5" fill="none" fill-rule="evenodd" opacity="1" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/></g></svg>',
    };
  }

  render(): HTMLElement {
    const container = document.createElement('div');
    container.classList.add('mwt-cta-container');
    container.classList.add(this.data.api.styles.block);

    const buttonNameLabel = document.createElement('label');
    buttonNameLabel.classList.add('mwt-cta-button-name-label');
    buttonNameLabel.innerText = 'Button Name';

    const buttonName = document.createElement('input');
    buttonName.classList.add('mwt-cta-button-name');
    buttonName.classList.add(this.data.api.styles.input);
    buttonName.value = CallToAction.validateData(this.data.data.buttonText);

    const buttonLinkLabel = document.createElement('label');
    buttonLinkLabel.classList.add('mwt-cta-button-link-label');
    buttonLinkLabel.innerText = 'Button URL';

    const buttonLink = document.createElement('input');
    buttonLink.classList.add('mwt-cta-button-link');
    buttonLink.classList.add(this.data.api.styles.input);
    buttonLink.value = CallToAction.validateData(this.data.data.buttonUrl);

    const additionalTextLabel = document.createElement('label');
    additionalTextLabel.classList.add('mwt-cta-add-text-label');
    additionalTextLabel.innerText = 'Additional Text (optional)';

    const additionalText = document.createElement('textarea');
    additionalText.classList.add('mwt-cta-add-text');
    additionalText.classList.add(this.data.api.styles.input);

    if (this.data.data.additionalText !== undefined) {
      additionalText.value = CallToAction.validateData(this.data.data.additionalText);
    }

    const presetListLabel = document.createElement('label');
    presetListLabel.classList.add('mwt-cta-preset-list-label');
    presetListLabel.innerText = 'Style Preset';

    const presetList = document.createElement('select');

    const presetInputBox = document.createElement('input');
    presetInputBox.type = 'hidden';
    presetInputBox.classList.add('mwt-cta-preset-list');

    if (this.data.config.cssPresetChoices !== null && this.data.config.cssPresetChoices.length > 0) {
      Object.entries(this.data.config.cssPresetChoices).forEach((e) => {
        const newEntry = document.createElement('option');
        newEntry.value = e[0] as string;
        newEntry.text = e[1] as string;

        if (this.data.data.cssPreset === e[0]) {
          newEntry.selected = true;
          presetInputBox.value = e[0] as string;
        }

        presetList.appendChild(newEntry);
      });
    } else {
      presetList.hidden = true;
      presetListLabel.hidden = true;
    }

    presetList.addEventListener('change', () => {
      presetInputBox.value = presetList.selectedIndex.toString();
    });

    container.appendChild(buttonNameLabel);
    container.appendChild(buttonName);
    container.appendChild(buttonLinkLabel);
    container.appendChild(buttonLink);
    container.appendChild(additionalTextLabel);
    container.appendChild(additionalText);
    container.appendChild(presetListLabel);
    container.appendChild(presetList);
    container.appendChild(presetInputBox);

    return container;
  }

  static validateData(content: string) {
    if (content !== '' && content !== undefined) {
      return content;
    }
    return '';
  }

  /* eslint class-methods-use-this: ["error", { "exceptMethods": ["save"] }] */
  save(block: HTMLElement): BlockToolData {
    return {
      buttonText: (block.querySelector('input.mwt-cta-button-name') as HTMLInputElement).value,
      buttonUrl: (block.querySelector('input.mwt-cta-button-link') as HTMLInputElement).value,
      additionalText: (block.querySelector('textarea.mwt-cta-add-text') as HTMLInputElement).value,
      cssPreset: (block.querySelector('input.mwt-cta-preset-list') as HTMLInputElement).value,
    };
  }
}

export default CallToAction as unknown as BlockToolConstructable;
