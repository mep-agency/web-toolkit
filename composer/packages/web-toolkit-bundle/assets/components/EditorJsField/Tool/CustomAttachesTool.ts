/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {
  API,
  BlockTool,
  BlockToolConstructable,
  BlockToolConstructorOptions,
  BlockToolData,
} from '@editorjs/editorjs';

import CustomUploader from './CustomAttachesUploader';

const AttachesTool: OriginalAttachesToolConstructable = require('@editorjs/attaches');

interface OriginalAttachesTool extends BlockTool {
  uploader: CustomUploader;

  uploadingFailed(error: string): void;

  get data(): AttachesToolData | CustomAttachesToolData;

  set data(data: AttachesToolData | CustomAttachesToolData);

  onUpload(response: UploadResponseFormat | CustomUploadResponseFormat): void;
}

interface OriginalAttachesToolConstructable {
  new(
    config: BlockToolConstructorOptions<CustomAttachesToolData, AttachesToolConfig>,
  ): OriginalAttachesTool;
}

interface AttachesToolData {
  title: string,
  file: {
    url: string,
    uuid: string,
  },
}

interface AttachesToolConfig {
  endpoint: string,
  uploader?: {
    uploadByFile: Promise<UploadResponseFormat>,
  },
  field: string,
  types: string,
  buttonText: string,
  errorMessage: string,
  additionalRequestHeaders: object,
}

interface UploadResponseFormat {
  success: number,
  file: {
    url: string,
    [key: string]: any,
  },
}

interface CustomAttachesToolConfig {
  api_token: string,
  endpoint: string,
  buttonText: string | null,
  errorMessage: string | null,
}

interface CustomAttachesToolData {
  title: string,
  attachment: CustomUploadResponseFormat,
}

interface CustomUploadResponseFormat {
  uuid: string,
  publicUrl: string,
}

class CustomAttachesTool extends AttachesTool implements BlockTool {
  private dataElement;

  private api: API;

  constructor({
    data,
    config,
    api,
    readOnly,
  }: BlockToolConstructorOptions<CustomAttachesToolData, CustomAttachesToolConfig>) {
    super({
      data,
      config,
      api,
      readOnly,
    } as BlockToolConstructorOptions<CustomAttachesToolData, AttachesToolConfig>);

    this.api = api;
    this.dataElement = {
      title: data.title ? data.title : '',
      file: {
        uuid: data.attachment?.uuid,
        url: data.attachment?.publicUrl,
      },
      attachment: {
        uuid: data.attachment?.uuid,
        publicUrl: data.attachment?.publicUrl,
      },
    };

    super.uploader = new CustomUploader({
      config: {
        endpoint: config?.endpoint ?? '',
        field: 'file',
        buttonText: config?.buttonText,
        errorMessage: config?.errorMessage,
        additionalRequestHeaders: {},
        additionalRequestData: {
          _token: config?.api_token,
        },
      },
      onUpload: (response: CustomUploadResponseFormat) => this.onUpload(response),
      onError: (error: string) => super.uploadingFailed(error),
    });
  }

  set data(data) {
    super.data = {
      title: data.title,
      file: {
        // On tool initialization an empty object is passed as data
        uuid: data.attachment?.uuid,
        url: data.attachment?.publicUrl,
      },
    } as AttachesToolData;
  }

  get data() {
    return {
      title: this.dataElement.title,
      file: {
        url: this.dataElement.attachment.publicUrl,
      },
      attachment: {
        uuid: this.dataElement.attachment.uuid,
        publicUrl: this.dataElement.attachment.publicUrl,
      },
    } as CustomAttachesToolData;
  }

  onUpload(response: CustomUploadResponseFormat) {
    const validResponse: UploadResponseFormat = {
      success: 1,
      file: {
        url: response.publicUrl,
        uuid: response.uuid,
      },
    };

    this.dataElement = {
      title: response.publicUrl.substring(response.publicUrl.lastIndexOf('/') + 1),
      file: {
        url: validResponse.file.url,
        uuid: validResponse.file.uuid,
      },
      attachment: {
        publicUrl: validResponse.file.url,
        uuid: validResponse.file.uuid,
      },
    };

    super.data = {
      title: this.dataElement.title,
      file: {
        url: this.dataElement.attachment.publicUrl,
        uuid: this.dataElement.attachment.uuid,
      },
    };

    super.onUpload(validResponse);

    this.api.saver.save();
  }

  save(block: HTMLElement): BlockToolData {
    this.dataElement.title = block.innerText;
    return this.dataElement;
  }
}

export default CustomAttachesTool as unknown as BlockToolConstructable;
