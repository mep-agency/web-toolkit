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
  private readonly uploadButton: HTMLInputElement;
  private readonly deleteButton: HTMLButtonElement;
  private readonly apiUrl: string;
  private readonly csrfToken: string;
  private readonly errorButton: HTMLElement;
  private readonly errorList: HTMLElement;
  private readonly widget: HTMLElement;
  private fileData = {
    fileURL: '',
    fileType: '',
    fileSize: ''
  };

  public constructor(input: HTMLInputElement)
  {
    this.input = input;
    this.widget = document.getElementById('mwt-upload-widget') as HTMLElement;
    this.fileInput = document.getElementById(`${input.id}__file`) as HTMLInputElement;
    this.uploadButton = document.getElementById(`${input.id}__upload_button`) as HTMLInputElement;
    this.errorButton = document.getElementById(`${input.id}__error_button`) as HTMLElement;
    this.deleteButton = document.getElementById(`${input.id}__delete_button`) as HTMLButtonElement;
    this.errorList = document.getElementById(`${input.id}__error_list`) as HTMLElement;
    this.apiUrl = this.input.getAttribute('data-api-url')!;
    this.csrfToken = this.input.getAttribute('data-csrf-token')!;
    this.fileData.fileURL = this.input.getAttribute('data-public-url')!;
  }

  public init()
  {
    if(this.fileData.fileURL != '') {
      this.getFileData(this.fileData);
    }
    else
    {
      AttachmentField.passFileData();
    }

    this.uploadButton.addEventListener('change', (e) => {
      e.preventDefault();

      const formData = new FormData();
      formData.append('file', this.fileInput.files![0]);
      formData.append('_token', this.csrfToken);

      this.uploadFile(formData, this.apiUrl).then(result => {
        this.errorDisplay(false);

        console.log(`Success: ${result.publicUrl}`);

        this.input.value = result.uuid;
        this.fileData.fileURL = result.publicUrl;

        this.getFileData(this.fileData);
      }).catch(error => {
        this.errorDisplay(true);

        console.error(`ERROR: ${error.message}`);

        for (const err of error.errors) {
          let entry = document.createElement('li');
          entry.appendChild(document.createTextNode(err.message));

          this.errorList.appendChild(entry);

          console.error(`Validation message: ${err.message}`);
        }
      });
    })

    this.deleteButton.addEventListener('click', (e) => {
      e.preventDefault();

      this.fileInput.value = "";

      if(this.input.value != "") this.input.value = "";

      this.errorDisplay(false);

      AttachmentField.passFileData();
    })
  }

  // === ASYNC FETCH FUNCTIONS
  async uploadFile(formData:FormData ,apiUrl: string) {
      const response:any = await fetch(apiUrl, {
        method: 'POST',
        body: formData,
      });

      if (!response.ok) {
        throw await response.json();
      }

      return await response.json();
    }

  async fetchFileData(fileData:any) {
      const response = await fetch(fileData.fileURL, {
        method: 'HEAD',
      });

      if (!response.ok) {
        throw new Error(`HTTP error! Status Code: ${response.status}`);
      }

      fileData.fileSize=AttachmentField.fileSizeFormatter(response.headers.get('content-length')!);
      fileData.fileType=response.headers.get('content-type');
      fileData.fileName=fileData.fileURL.split('/')[5];

      AttachmentField.passFileData(fileData);
    }

  // === AUX FUNCTIONS
  private getFileData(fileData:any) {
    this.fetchFileData(fileData).catch(error => {
      console.log('Error: ' + error.message);
    })
  }

  private errorDisplay(isError:boolean) {
    if(isError)
    {
      this.widget.classList.add('is-invalid');
      this.errorButton.classList.remove('visually-hidden');
      this.errorList.innerHTML = '';
    }
    else
    {
      this.errorButton.classList.add('visually-hidden');
      this.widget.classList.remove('is-invalid');
    }
  }

  // === STATIC FUNCTIONS
  private static passFileData(fileData?:any) {
    const container = document.getElementById('display') as HTMLElement;
    let fileVariables;

    if(!fileData) {
      fileVariables = {
        fileSize: 'Empty',
        fileType: 'Empty',
        fileName: 'Empty',
        fileURL: ''
      }
    }
    else
    {
      fileVariables = fileData;
    }

    document.getElementById('file-size')!.textContent = '\xa0'+fileVariables.fileSize;
    document.getElementById('file-type')!.textContent = '\xa0'+fileVariables.fileType;
    document.getElementById('file-name')!.textContent = '\xa0'+fileVariables.fileName;

    this.createDisplayElement(container, fileVariables.fileType.split('/')[0], fileVariables.fileURL);

  }

  private static createDisplayElement(parent:HTMLElement, type:string, url:string) {
    parent.innerHTML = '';

    let container = document.createElement('a');
    container.href = url;
    container.target = '_blank';
    let displayElement;

    if(type != 'Empty') {
      if (type == 'image') {
        displayElement = document.createElement('img');
        displayElement.setAttribute('src', url);
        container.appendChild(displayElement);
      } else {
        displayElement = document.createElement('i');
        displayElement.classList.add('fas');
        displayElement.classList.add('fa-file');
        container.appendChild(displayElement);
      }
      parent.appendChild(container);
    }
  }

  private static fileSizeFormatter(size:string) {
    const bytes = parseFloat(size);
    const kiloBytes = Math.round((bytes/1024) * 100) / 100;

    if(kiloBytes > 1000) {
      return (Math.round((kiloBytes/1024) * 100) / 100) + ' MB';
    }
    else
    {
      return kiloBytes + ' kB';
    }
  }
}

FieldsManager.registerField('mwt-attachment-field', AttachmentField);
