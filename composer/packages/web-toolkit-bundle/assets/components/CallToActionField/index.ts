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

interface CustomCTAData {
  buttonText: string;
  buttonUrl: string;
  additionalText?: string;
  cssPreset?: string;
}

interface CustomConfigData {
  api: any;
  block: Object;
  config: {
    validCssPresets: string[] | null;
  },
  data: CustomCTAData;
}

class CustomCTA implements BlockTool {
  public data: CustomConfigData;

  constructor(params: CustomConfigData) {
    this.data = params;
  }

  static get toolbox() {
    return {
      title: 'Call To Action',
      icon: '<svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 0 512 512" width="20"><path d="m237.102 366v-90.018h-90c-11.046 0-20-8.954-20-20s8.954-20 20-20h90v-90.982c0-11.046 8.954-20 20-20s20 8.954 20 20v90.982h90c11.046 0 20 8.954 20 20s-8.954 20-20 20h-90v90.018c0 11.046-8.954 20-20 20s-20-8.954-20-20zm254.898-15c11.046 0 20-8.954 20-20v-251c0-44.112-35.888-80-80-80h-352c-44.112 0-80 35.888-80 80v352c0 44.112 35.888 80 80 80h352c44.112 0 80-35.888 80-80 0-11.046-8.954-20-20-20s-20 8.954-20 20c0 22.056-17.944 40-40 40h-352c-22.056 0-40-17.944-40-40v-352c0-22.056 17.944-40 40-40h352c22.056 0 40 17.944 40 40v251c0 11.046 8.954 20 20 20z"/></svg>',
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
    buttonName.value = CustomCTA.validateData(this.data.data.buttonText);

    const buttonLinkLabel = document.createElement('label');
    buttonLinkLabel.classList.add('mwt-cta-button-link-label');
    buttonLinkLabel.innerText = 'Button URL';

    const buttonLink = document.createElement('input');
    buttonLink.classList.add('mwt-cta-button-link');
    buttonLink.classList.add(this.data.api.styles.input);
    buttonLink.value = CustomCTA.validateData(this.data.data.buttonUrl);

    const additionalTextLabel = document.createElement('label');
    additionalTextLabel.classList.add('mwt-cta-add-text-label');
    additionalTextLabel.innerText = 'Additional Text (optional)';

    const additionalText = document.createElement('textarea');
    additionalText.classList.add('mwt-cta-add-text');
    additionalText.classList.add(this.data.api.styles.input);

    if (this.data.data.additionalText !== undefined) {
      additionalText.value = CustomCTA.validateData(this.data.data.additionalText);
    }

    const presetListLabel = document.createElement('label');
    presetListLabel.classList.add('mwt-cta-preset-list-label');
    presetListLabel.innerText = 'Style Preset';

    const presetList = document.createElement('select');
    presetList.classList.add('mwt-cta-preset-list');
    presetList.classList.add(this.data.api.styles.button);

    if (this.data.config.validCssPresets !== null) {
      Object.entries(this.data.config.validCssPresets).forEach((e) => {
        const newEntry = document.createElement('option');
        newEntry.value = e[0] as string;
        newEntry.text = e[1] as string;

        if (this.data.data.cssPreset === e[0]) {
          newEntry.selected = true;
        }

        presetList.appendChild(newEntry);
      });
    } else {
      presetList.hidden = true;
      presetListLabel.hidden = true;
    }

    container.appendChild(buttonNameLabel);
    container.appendChild(buttonName);
    container.appendChild(buttonLinkLabel);
    container.appendChild(buttonLink);
    container.appendChild(additionalTextLabel);
    container.appendChild(additionalText);
    container.appendChild(presetListLabel);
    container.appendChild(presetList);

    return container;
  }

  static validateData(content: string) {
    if (content !== '' && content !== undefined) {
      return content;
    }
    return '';
  }

  // TODO: fix saving and temp eslint disable
  // eslint-disable-next-line class-methods-use-this
  save(block: HTMLElement): BlockToolData {
    const cssPresets = block.querySelector('select.mwt-cta-preset-list') as HTMLSelectElement;

    return {
      buttonText: (block.querySelector('input.mwt-cta-button-name') as HTMLInputElement).value,
      buttonUrl: (block.querySelector('input.mwt-cta-button-link') as HTMLInputElement).value,
      additionalText: (block.querySelector('textarea.mwt-cta-add-text') as HTMLInputElement).value,
      cssPreset: cssPresets.options[cssPresets.selectedIndex].value,
    };
  }
}

export default CustomCTA as unknown as BlockToolConstructable;
