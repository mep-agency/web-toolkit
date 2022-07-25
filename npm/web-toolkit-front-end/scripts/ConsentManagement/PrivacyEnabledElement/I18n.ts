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
      feature_is_disabled: string;
      update_preferences: string;
    }
  }
}

const i18n: Localization = {
  id: 'consent_manager_built_in_solution',
  dictionaries: {
    it: {
      feature_is_disabled: 'Questa funzionalità è disabilitata dalle tue preferenze privacy.',
      update_preferences: 'Modifica preferenze',
    },
    en: {
      feature_is_disabled: 'This feature is disabled by your privacy preferences.',
      update_preferences: 'Update your preferences',
    },
  },
};

export default pickLanguageData(i18n);
