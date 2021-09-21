export default interface UcConsentStatusEventInterface {
  event: 'consent_status';
  type: 'explicit' | 'implicit';
  [key: string]: string | boolean;
}
