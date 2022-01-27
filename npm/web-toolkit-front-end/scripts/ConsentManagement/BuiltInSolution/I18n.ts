/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import { pickLanguageData, LocalizationData } from '../../I18nTools';

interface Localization extends LocalizationData {
  dictionaries: {
    [localeCode: string]: {
      title: string;
      body: string;
      open_banner: string;
      close_banner: string;
      save: string;
      accept_all: string;
      accept_required: string;
      open_pref: string;
      services: string;
      categories: string;
    }
  }
}

const i18n: Localization = {
  id: 'consent_manager_built_in_solution',
  dictionaries: {
    it: {
      title: 'Titolo del banner',
      body: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi varius condimentum tortor non suscipit. Fusce mattis augue elit, ac lacinia massa dignissim in. Cras vel pulvinar metus.',
      open_banner: 'Apri banner',
      close_banner: 'Chiudi banner',
      save: 'Salva',
      accept_all: 'Accetta tutti',
      accept_required: 'Accetta necessari',
      open_pref: 'Apri preferenze',
      services: 'Servizi',
      categories: 'Categorie',
    },
    en: {
      title: 'Title of the banner',
      body: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi varius condimentum tortor non suscipit. Fusce mattis augue elit, ac lacinia massa dignissim in. Cras vel pulvinar metus.',
      open_banner: 'Open banner',
      close_banner: 'Close banner',
      save: 'Save',
      accept_all: 'Accept all',
      accept_required: 'Accept required',
      open_pref: 'Open preferences',
      services: 'Services',
      categories: 'Categories',
    },
  },
};

export default pickLanguageData(i18n);
