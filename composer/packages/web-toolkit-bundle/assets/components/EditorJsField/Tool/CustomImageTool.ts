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

const ImageTool: OriginalImageToolConstructable = require('@editorjs/image');

interface OriginalImageTool extends BlockTool {
  get data(): ImageToolData | CustomImageToolData;

  set data(data: ImageToolData | CustomImageToolData);

  onUpload(response: UploadResponseFormat | CustomUploadResponseFormat): void;
}

interface OriginalImageToolConstructable {
  new(config: BlockToolConstructorOptions<CustomImageToolData, ImageToolConfig>): OriginalImageTool;
}

interface ImageToolData {
  caption: string,
  withBorder: boolean,
  withBackground: boolean,
  stretched: boolean,
  file: {
    url: string,
    uuid: string,
  },
}

interface ImageToolConfig {
  endpoints: {
    byFile: string,
    byUrl: string,
  },
  field: string,
  types: string,
  captionPlaceholder: string,
  additionalRequestData: object,
  additionalRequestHeaders: object,
  buttonContent: string,
  uploader?: {
    uploadByFile: Promise<UploadResponseFormat>,
    uploadByUrl: Promise<UploadResponseFormat>,
  },
}

interface UploadResponseFormat {
  success: number,
  file: {
    url: string,
    [key: string]: any,
  },
}

interface CustomImageToolConfig {
  api_token: string,
  endpoint: string,
  captionPlaceholder: string | null,
  buttonContent: string | null,
}

interface CustomImageToolData {
  caption: string,
  withBorder: boolean,
  withBackground: boolean,
  stretched: boolean,
  attachment: CustomUploadResponseFormat,
}

interface CustomUploadResponseFormat {
  uuid: string,
  publicUrl: string,
}

class CustomImageTool extends ImageTool implements BlockTool {
  constructor({
    data,
    config,
    api,
    readOnly,
  }: BlockToolConstructorOptions<CustomImageToolData, CustomImageToolConfig>) {
    super({
      data,
      config: {
        endpoints: {
          byFile: config?.endpoint ?? '',
        },
        field: 'file',
        types: 'image/*',
        captionPlaceholder: config?.captionPlaceholder,
        additionalRequestData: {
          _token: config?.api_token,
        },
        additionalRequestHeaders: {},
        buttonContent: config?.buttonContent,
      },
      api,
      readOnly,
    } as BlockToolConstructorOptions<CustomImageToolData, ImageToolConfig>);
  }

  set data(data) {
    super.data = {
      caption: data.caption,
      withBorder: data.withBorder,
      withBackground: data.withBackground,
      stretched: data.stretched,
      file: {
        // On tool initialization an empty object is passed as data
        uuid: data.attachment?.uuid,
        url: data.attachment?.publicUrl,
      },
    } as ImageToolData;
  }

  get data() {
    const data = super.data as ImageToolData;

    return {
      caption: data.caption,
      withBorder: data.withBorder,
      withBackground: data.withBackground,
      stretched: data.stretched,
      attachment: {
        uuid: data.file.uuid,
        publicUrl: data.file.url,
      },
    } as CustomImageToolData;
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

export default CustomImageTool as unknown as BlockToolConstructable;
