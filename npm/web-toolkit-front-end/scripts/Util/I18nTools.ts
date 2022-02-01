/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

let fallbackLanguage = 'en';

const { lang } = document.documentElement;

export interface Dictionary {
  [key: string]: string;
}

export interface LocalizationData {
  id: string;
  dictionaries: {
    [localeCode: string]: Dictionary;
  };
}

export function setFallbackLanguage(newFallbackLanguage: string) {
  fallbackLanguage = newFallbackLanguage;
}

export function pickLanguageData(languageData: LocalizationData): typeof ret extends Dictionary
  ? typeof ret
  : never {
  const preferredOptions = [lang.toLowerCase().replace('-', '_')];
  let bestFallbackOption: string | null = null;

  const matchedLanguageCode = preferredOptions[0].match(/([a-zA-Z]+)[-_].+/);
  if (matchedLanguageCode !== null) {
    preferredOptions.push(matchedLanguageCode[1]);
  }

  preferredOptions.push(fallbackLanguage);

  for (const languageCode of preferredOptions) {
    for (const dictionaryLanguageCode of Object.keys(languageData.dictionaries)) {
      const lowerCaseCode = dictionaryLanguageCode.toLowerCase();

      if (lowerCaseCode === languageCode) {
        return languageData.dictionaries[dictionaryLanguageCode];
      }

      if (bestFallbackOption === null && lowerCaseCode.startsWith(languageCode)) {
        bestFallbackOption = dictionaryLanguageCode;
      }
    }
  }

  if (bestFallbackOption === null) {
    throw new Error(`Cannot find any useful dictionary in "${languageData.id}". Language options: ${preferredOptions.toString()}`);
  }
  const ret = languageData.dictionaries[bestFallbackOption];
  return ret;
}
