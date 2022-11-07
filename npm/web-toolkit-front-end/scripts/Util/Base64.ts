/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import CryptoJS from 'crypto-js';

/*
 * The window.atob() and window.btoa() functions are not compatible with
 * base64_encode() and base64_decode() from PHP, so we need to use the
 * functions below to make sure we get predictable results with any input
 * characters.
 */

export function encode(message: string): string {
  const encodedWord = CryptoJS.enc.Utf8.parse(message);

  return CryptoJS.enc.Base64.stringify(encodedWord);
}

export function decode(message: string): string {
  const encodedWord = CryptoJS.enc.Base64.parse(message);

  return CryptoJS.enc.Utf8.stringify(encodedWord);
}
