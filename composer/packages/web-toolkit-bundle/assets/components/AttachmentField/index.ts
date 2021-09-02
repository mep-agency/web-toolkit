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
  private fileData = {
    fileURL: '',
    fileType: '',
    fileSize: ''
  };

  public constructor(input: HTMLInputElement)
  {
    this.input = input;
    this.uploadButton = document.getElementById(`${input.id}__file`) as HTMLInputElement;
    this.errorButton = document.getElementById(`${input.id}__error_button`) as HTMLElement;
    this.errorList = document.getElementById(`${input.id}__error_list`) as HTMLElement;
    this.fileInput = document.getElementById(`${input.id}__file`) as HTMLInputElement;
    this.deleteButton = document.getElementById(`${input.id}__delete_button`) as HTMLButtonElement;
    this.apiUrl = this.input.getAttribute('data-api-url')!;
    this.csrfToken = this.input.getAttribute('data-csrf-token')!;
    this.fileData.fileURL = this.input.getAttribute('data-public-url')!;
  }

  public init()
  {
    if(this.fileData.fileURL != '') {
      getFileData(this.fileData);
    }
    else
    {
      passFileData();
    }

    this.uploadButton.addEventListener('change', (e) => {
      e.preventDefault();

      const formData = new FormData();
      formData.append('file', this.fileInput.files![0]);

      formData.append('_token', this.csrfToken);

      uploadFile(formData, this.apiUrl).then(result => {
        this.errorButton.classList.add('visually-hidden');

        console.log(`Success: ${result.publicUrl}`);

        this.input.value = result.uuid;
        this.fileData.fileURL = result.publicUrl;

        getFileData(this.fileData);
      }).catch(error => {
        this.errorButton.classList.remove('visually-hidden');
        this.errorList.innerHTML = '';

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

      this.errorButton.classList.add('visually-hidden');

      passFileData();
    })
  }
}

// === ASYNC FETCH FUNCTIONS
async function uploadFile(formData:FormData ,apiUrl: string) {
  const response:any = await fetch(apiUrl, {
    method: 'POST',
    body: formData,
  });

  if (!response.ok) {
    throw await response.json();
  }

  return await response.json();
}

async function fetchFileData(fileData:any) {
  const response = await fetch(fileData.fileURL, {
    method: 'HEAD',
  });

  if (!response.ok) {
    throw new Error(`HTTP error! Status Code: ${response.status}`);
  }

  fileData.fileSize=fileSizeFormatter(response.headers.get('content-length')!);
  fileData.fileType=response.headers.get('content-type');
  fileData.fileName=fileData.fileURL.split('/')[5];

  passFileData(fileData);
}

// === AUX FUNCTIONS
function getFileData(fileData:any) {
  fetchFileData(fileData).catch(error => {
    console.log('Error: ' + error.message);
  })
}

function passFileData(fileData?:any) {
  const container = document.getElementById('previewer') as HTMLAnchorElement;
  const doc = document.getElementById('document-preview') as HTMLAnchorElement;
  const image = document.getElementById('image-preview') as HTMLImageElement;
  let fileVariables;

  if(!fileData) {
    fileVariables = {
      fileSize: '\xa0Empty',
      fileType: '\xa0Empty',
      fileName: '\xa0Empty',
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

  // TODO: improve this switch
  if (fileVariables.fileType == '\xa0Empty')
  {
    container.classList.add('visually-hidden');
  }
  else
  {
    container.classList.remove('visually-hidden');
    container.href = fileVariables.fileURL;

    if(fileVariables.fileType.split('/')[0] == 'image')
    {
      image.src = fileVariables.fileURL;
      image.classList.remove('visually-hidden');
      doc.classList.add('visually-hidden');
    }
    else
    {
      doc.href = fileVariables.fileURL;
      doc.classList.remove('visually-hidden');
      image.classList.add('visually-hidden');
    }
  }
}

function fileSizeFormatter(size:string) {
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

FieldsManager.registerField('mwt-attachment-field', AttachmentField);
