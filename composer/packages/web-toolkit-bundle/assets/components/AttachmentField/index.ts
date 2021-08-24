/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import './attachment-field.scss';

import FieldsManager, { Field } from "../../scripts/FieldsManager";

// TODO: This is a temporary implementation...

class AttachmentField implements Field {
  private readonly input: HTMLInputElement;
  private readonly fileInput: HTMLInputElement;
  private readonly contextInput: HTMLInputElement;
  private readonly uploadButton: HTMLButtonElement;
  private readonly apiUrl: string;
  private readonly csrfToken: string;

  public constructor(input: HTMLInputElement)
  {
    this.input = input;
    this.fileInput = document.getElementById(`${input.id}__file`) as HTMLInputElement;
    this.contextInput = document.getElementById(`${input.id}__context`) as HTMLInputElement;
    this.uploadButton = document.getElementById(`${input.id}__upload_button`) as HTMLButtonElement;
    this.apiUrl = this.input.getAttribute('data-api-url')!;
    this.csrfToken = this.input.getAttribute('data-csrf-token')!;
  }

  public init()
  {
    this.uploadButton.addEventListener('click', (e) => {
      e.preventDefault();

      const formData = new FormData();
      formData.append('file', this.fileInput.files![0]);
      formData.append('context', this.contextInput.value);
      formData.append('_token', this.csrfToken);

      fetch(this.apiUrl, {
        method: 'POST',
        body: formData,
      }).then(response => {
        if (!response.ok) {
          throw response.json();
        }

        return response.json();
      })
        .then(result => {
          console.log(`Success: ${result.publicUrl}`);
          this.input.value = result.uuid;
        })
        .catch(error => {
          error.then((result: any) => {
            console.error(`ERROR: ${result.message}`);

            for (const error of result.errors) {
              console.error(`Validation message: ${error.message}`);
            }
          });
        });
    })
  }
}

FieldsManager.registerField('mwt-attachment-field', AttachmentField);
