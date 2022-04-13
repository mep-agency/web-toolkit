/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// @ts-ignore
import ajax from '@codexteam/ajax';

interface UploaderConfig {
  endpoint: string,
  field: string,
  types?: string,
  buttonText?: string | null,
  errorMessage?: string | null,
  additionalRequestHeaders: object,
  additionalRequestData: object,
}

interface UploaderData {
  config: UploaderConfig,
  onUpload: Function,
  onError: Function,
}

export default class CustomUploader {
  private config: any;

  private onUpload: Function;

  private readonly onError: Function;

  constructor({ config, onUpload, onError }: UploaderData) {
    this.config = config;
    this.onUpload = onUpload;
    this.onError = onError;
  }

  uploadSelectedFile({ onPreview }: any) {
    ajax.transport({
      url: this.config.endpoint || '',
      accept: this.config.types || '*',
      data: this.config.additionalRequestData || {},
      headers: this.config.additionalRequestHeaders || {},
      beforeSend: () => onPreview(),
      fieldName: this.config.field || 'file',
    }).then((response: any) => {
      this.onUpload(response.body);
    }).catch((error: { message: any; }) => {
      const message = (error && error.message) ? error.message : this.config.errorMessage || 'File upload failed';

      this.onError(message);
    });
  }
}
