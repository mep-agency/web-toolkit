/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export interface PemKeyPair {
  privateKey: string;
  publicKey: string;
  publicKeyHash: string | undefined;
}

export async function generateKey(bits: number = 2048): Promise<CryptoKeyPair> {
  return window.crypto.subtle.generateKey(
    {
      name: 'RSA-PSS',
      modulusLength: bits,
      publicExponent: new Uint8Array([1, 0, 1]),
      hash: 'SHA-256',
    },
    true,
    ['sign', 'verify'],
  );
}

function uint8ArrayToBase64(buffer: Uint8Array): string {
  return window.btoa(
    String.fromCharCode.apply(null, Array.from(buffer)),
  );
}

function base64ToUint8Array(message: string): Uint8Array {
  const byteString = window.atob(message);
  const byteArray = new Uint8Array(byteString.length);

  for (let i = 0; i < byteString.length; i++) {
    byteArray[i] = byteString.charCodeAt(i);
  }

  return byteArray;
}

function pemToArrayBuffer(pemKey: string): Uint8Array {
  const cleanInput = pemKey
    .replace('\n', '')
    .replace('-----BEGIN PRIVATE KEY-----', '')
    .replace('-----END PRIVATE KEY-----', '')
    .replace('-----BEGIN PUBLIC KEY-----', '')
    .replace('-----END PUBLIC KEY-----', '');

  return base64ToUint8Array(cleanInput);
}

async function importPemKey(format: 'pkcs8' | 'spki', key: string): Promise<CryptoKey> {
  return window.crypto.subtle.importKey(
    format,
    pemToArrayBuffer(key),
    {
      name: 'RSA-PSS',
      hash: { name: 'SHA-256' },
    },
    true,
    [format === 'pkcs8' ? 'sign' : 'verify'],
  );
}

export async function importFromPem(pemKeyPair: PemKeyPair): Promise<CryptoKeyPair> {
  return {
    privateKey: await importPemKey('pkcs8', pemKeyPair.privateKey),
    publicKey: await importPemKey('spki', pemKeyPair.publicKey),
  };
}

export async function importPublicPem(publicKeyPem: string): Promise<CryptoKey> {
  return importPemKey('spki', publicKeyPem);
}

export async function exportToPem(keyPair: CryptoKeyPair): Promise<PemKeyPair> {
  if (keyPair.privateKey === undefined || keyPair.publicKey === undefined) {
    throw new Error('Keys not defined!');
  }
  return {
    privateKey: `-----BEGIN PRIVATE KEY-----\n${uint8ArrayToBase64(
      new Uint8Array(
        await crypto.subtle.exportKey(
          'pkcs8',
          keyPair.privateKey,
        ),
      ),
    )}\n-----END PRIVATE KEY-----`,
    publicKey: `-----BEGIN PUBLIC KEY-----\n${uint8ArrayToBase64(
      new Uint8Array(
        await crypto.subtle.exportKey(
          'spki',
          keyPair.publicKey,
        ),
      ),
    )}\n-----END PUBLIC KEY-----`,
    publicKeyHash: undefined,
  };
}

export async function sign(privateKey: CryptoKey, message: string): Promise<string> {
  const signature = await crypto.subtle.sign(
    {
      name: 'RSA-PSS',
      saltLength: 32,
    },
    privateKey,
    base64ToUint8Array(message),
  );
  return uint8ArrayToBase64(new Uint8Array(signature));
}

export async function verify(
  publicKey: CryptoKey,
  signature: string,
  message: string,
): Promise<boolean> {
  return crypto.subtle.verify(
    {
      name: 'RSA-PSS',
      saltLength: 32,
    },
    publicKey,
    base64ToUint8Array(signature),
    base64ToUint8Array(message),
  );
}
