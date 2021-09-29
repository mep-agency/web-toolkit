/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default interface UcConsentStatusEventInterface {
  event: 'consent_status';
  type: 'explicit' | 'implicit';
  [key: string]: string | boolean;
}
