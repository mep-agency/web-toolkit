export default class ConsentStatusChangedEvent extends CustomEvent<{
  value: boolean;
  isInit: boolean;
}> {}
