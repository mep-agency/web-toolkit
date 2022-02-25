/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import { pickLanguageData, LocalizationData } from '../../Util/I18nTools';

interface Localization extends LocalizationData {
  dictionaries: {
    [localeCode: string]: {
      body: string;
      open_banner: string;
      close_banner: string;
      save: string;
      accept_all: string;
      accept_required: string;
      open_pref: string;
      services: string;
      categories: string;
      half_check_category: string;
      required_message: string;
      privacy_policy: string;
      cookie_policy: string;
      changed_notice: string;
    }
  }
}

const i18n: Localization = {
  id: 'consent_manager_built_in_solution',
  dictionaries: {
    it: {
      body: 'Questo sito utilizza cookie, propri e di terze parti, e altre tecnologie per offrirti una migliore esperienza utente. Ti preghiamo di accettare l\'utilizzo di queste tecnologie per procedere.',
      open_banner: 'Apri banner',
      close_banner: 'Chiudi banner',
      save: 'Salva',
      accept_all: 'Accetta tutti',
      accept_required: 'Accetta necessari',
      open_pref: 'Apri preferenze',
      services: 'Servizi',
      categories: 'Categorie',
      half_check_category: 'Uno o più servizi in questa categoria sono stati selezionati manualmente.',
      required_message: 'obbligatorio',
      privacy_policy: 'Privacy policy',
      cookie_policy: 'Cookie policy',
      changed_notice: 'Abbiamo apportato delle modifiche ai servizi utilizzati da questo sito, ti preghiamo di confermare le preferenze selezionate.',
    },
    en: {
      body: 'This site uses its own and third-party cookies and other technologies to offer you a better user experience. Please accept the use of these technologies to proceed.',
      open_banner: 'Open banner',
      close_banner: 'Close banner',
      save: 'Save',
      accept_all: 'Accept all',
      accept_required: 'Accept required',
      open_pref: 'Open preferences',
      services: 'Services',
      categories: 'Categories',
      half_check_category: 'One or more services in this category have been selected manually.',
      required_message: 'required',
      privacy_policy: 'Privacy policy',
      cookie_policy: 'Cookie policy',
      changed_notice: 'We made changes to the services used by this site, please confirm your preferences.',
    },
  },
};

export default pickLanguageData(i18n);
