/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {
  BlockTool,
  BlockToolConstructable,
  BlockToolConstructorOptions,
} from '@editorjs/editorjs';

const AttachesTool: OriginalAttachesToolConstructable = require('@editorjs/attaches');

interface OriginalAttachesTool extends BlockTool {
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
  constructor({
    data,
    config,
    api,
    readOnly,
  }: BlockToolConstructorOptions<CustomAttachesToolData, CustomAttachesToolConfig>) {
    super({
      data,
      config: {
        endpoint: config?.endpoint ?? '',
        field: 'file',
        buttonText: config?.buttonText,
        errorMessage: config?.errorMessage,
        additionalRequestHeaders: {},
      },
      api,
      readOnly,
    } as BlockToolConstructorOptions<CustomAttachesToolData, AttachesToolConfig>);
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
    const data = super.data as AttachesToolData;

    return {
      title: data.title,
      attachment: {
        uuid: data.file.uuid,
        publicUrl: data.file.url,
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

    super.onUpload(validResponse);
  }
}

export default CustomAttachesTool as unknown as BlockToolConstructable;
