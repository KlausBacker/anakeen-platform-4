import Navigo from "navigo";

const merge = (opts, newOpts) => {
  const result = Object.assign({}, opts);
  Object.keys(opts).forEach(key => {
    if (newOpts && newOpts[key] !== undefined) {
      result[key] = newOpts[key];
    }
  });
  return result;
};

const DEFAULT_OPTIONS = {
  root: null,
  useHash: false,
  hash: "#",
  vueInject: "$ankHubRouter"
};

export default function install(Vue, navigoOptions) {
  const options = merge(DEFAULT_OPTIONS, navigoOptions);
  if (!Vue.prototype[options.vueInject]) {
    Vue.prototype[options.vueInject] = {
      internal: new Navigo(options.root, options.useHash, options.hash),
      external: new Navigo(options.root, options.useHash, options.hash)
    };
  }
}
