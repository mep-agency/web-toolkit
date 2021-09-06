/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import './editorjs-field.scss';

import FieldsManager, {Field} from '../../scripts/FieldsManager';
import EditorJS, {ToolConstructable, ToolSettings, LogLevels} from '@editorjs/editorjs';

const HeaderTool = require('@editorjs/header');
const NestedListTool = require('@editorjs/nested-list');
const QuoteTool = require('@editorjs/quote');
const DelimiterTool = require('@editorjs/delimiter');
const ImageTool = require('@editorjs/image');
const EmbedTool = require('@editorjs/embed');
const AttachesTool = require('@editorjs/attaches');
const RawTool = require('@editorjs/raw');
const TableTool = require('@editorjs/table');
const WarningTool = require('@editorjs/warning');

const TOOLS_CONFIG_NORMALIZERS: {[toolName: string]: {(config: ToolSettings): ToolConstructable|ToolSettings}} = {
  paragraph: (config) => ({
    inlineToolbar: true,
  }),
  header: (config) => ({
    class: HeaderTool,
    inlineToolbar: true,
    config: config,
  }),
  list: (config) => ({
    class: NestedListTool,
    inlineToolbar: true,
    config: config,
  }),
  quote: (config) => ({
    class: QuoteTool,
    config: config,
  }),
  delimiter: (config) => DelimiterTool,
  image: (config) => ({
    class: ImageTool,
    config: config,
  }),
  embed: (config) => ({
    class: EmbedTool,
    config: {
      services: {
        youtube: {
          regex: /(?:https?:\/\/)?(?:www\.)?(?:(?:youtu\.be\/)|(?:youtube\.com)\/(?:v\/|u\/\w\/|embed\/|watch))(?:(?:\?v=)?([^#&?=]*))?((?:[?&]\w*=\w*)*)/,
          embedUrl: 'https://www.youtube-nocookie.com/embed/<%= remote_id %>',
          html: '<iframe style="width:100%;" height="320" frameborder="0" allowfullscreen></iframe>',
          height: 320,
          width: 580,
          id: ([id, queryParams]: [string, string]) => {
            if (!queryParams && id) {
              return id;
            }

            const paramsMap: {[key: string]: string|undefined} = {
              start: 'start',
              end: 'end',
              t: 'start',
              // eslint-disable-next-line camelcase
              time_continue: 'start',
              list: 'list',
            };

            const params = queryParams.slice(1)
              .split('&')
              .map((param) => {
                const [name, value] = param.split('=');

                if (!id && name === 'v') {
                  // eslint-disable-next-line no-param-reassign
                  id = value;

                  return null;
                }

                if (!(name in paramsMap)) {
                  return null;
                }

                return `${paramsMap[name]}=${value}`;
              })
              .filter((param) => !!param);

            return `${id}?${params.join('&')}`;
          },
        },
      },
    },
  }),
  attaches: (config) => ({
    class: AttachesTool,
    config: config,
  }),
  raw: (config) => ({
    class: RawTool,
    config: config,
  }),
  table: (config) => ({
    class: TableTool,
    inlineToolbar: true,
    config: config,
  }),
  warning: (config) => ({
    class: WarningTool,
    inlineToolbar: true,
    config: config,
  }),
}

// TODO: This is a temporary implementation...

class EditorJsField implements Field {
  private readonly input: HTMLInputElement;
  private readonly editor: HTMLDivElement;

  public constructor(input: HTMLInputElement)
  {
    this.input = input;
    this.editor = document.getElementById(`${input.id}__editor`) as HTMLDivElement;
  }

  public init()
  {
    const toolsOptions = JSON.parse(this.input.getAttribute('data-tools-options')!) as {[toolName: string]: ToolConstructable|ToolSettings};

    // Normalize tools options
    Object.keys(toolsOptions).map((toolName) => {
      toolsOptions[toolName] = TOOLS_CONFIG_NORMALIZERS[toolName](toolsOptions[toolName] as ToolSettings);
    });

    const editor = new EditorJS({
      holder: this.editor,
      tools: toolsOptions,
      data: JSON.parse(this.input.value ? this.input.value : '{}'),
      onChange: async () => {
        const content = await editor.save();

        this.input.value = JSON.stringify(content);
      },
      // see https://github.com/codex-team/editor.js/issues/1576
      logLevel: 'ERROR' as LogLevels,
    });
  }
}

FieldsManager.registerField('mwt-editorjs-field', EditorJsField);
