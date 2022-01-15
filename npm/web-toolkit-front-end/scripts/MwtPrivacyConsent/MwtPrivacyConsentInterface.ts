/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
export interface SpecsList {
  categories: CategoriesList[],
  services: ServicesList[],
}

export interface SpecsConsents {
  specs: SpecsList,
  consent: Map<string, boolean>
}

export interface SpecsHashed {
  specsHash: string,
  consent: string
}

interface CategoriesList {
  id: string,
  name: string,
  description: string,
  required: boolean
}

export interface ServicesList {
  id: string,
  name: string,
  description: string,
  category: string
}
