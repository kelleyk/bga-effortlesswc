/* TODO repace Function by (..params) => void */
interface Dojo {
  place: Function;
  style: Function;
  hitch: Function;
  addClass: (nodeId: string, className: string) => {};
  removeClass: (nodeId: string, className?: string) => {};
  toggleClass: (nodeId: string, className: string, forceValue: boolean) => {};
  connect: Function;
  query: Function;
  subscribe: Function;
  string: any;
  fx: any;
  marginBox: Function;
  fadeIn: Function;
  trim: Function;
}
