// jscs:disable disallowImplicitTypeConversion
// jscs:disable disallowFunctionDeclarations
function computePath(pathObj) {
  const index = this.paths.findIndex(p => p.id === pathObj.id);
  if (index > -1) {
    return (
      (this.ignoreEmptyValue ? this.pathSeparator : "") +
      this.paths
        .slice(0, index + 1)
        .map(p => p.id)
        .join(this.pathSeparator)
    );
  }
  return this.pathSeparator;
}

export default {
  props: {
    rootLabel: {
      type: String,
      default: ""
    },
    breadcrumbSeparator: {
      type: String,
      default: ">"
    },
    ignoreEmptyValue: {
      type: Boolean,
      default: true
    },
    pathSeparator: {
      type: String,
      default: "/"
    },
    path: {
      type: String | Array,
      default: () => [],
      validator: value => {
        if (typeof value === "string") {
          try {
            const jsonObject = JSON.parse(value);
            return !!Array.isArray(jsonObject);
          } catch (e) {
            console.error(e);
            return false;
          }
        }
        return true;
      }
    }
  },
  data() {
    return {
      paths: []
    };
  },
  watch: {
    path() {
      this.paths = this.jsonPaths;
    }
  },
  computed: {
    jsonPaths() {
      let routeProp = [];
      if (typeof this.path === "string") {
        routeProp = JSON.parse(this.path);
      } else {
        routeProp = this.path;
      }
      return routeProp;
    }
  },
  created() {
    this.paths = this.jsonPaths;
    this.$store.subscribeAction(action => {
      if (action.type === "addBreadcrumbEntries") {
        this.paths = this.paths.concat(action.payload);
      }
    });
  },
  methods: {
    onBreadcrumbItemClick(item) {
      this.$emit(
        "itemClick",
        Object.assign({}, item, { path: computePath.call(this, item) })
      );
    },
    onRootClick() {
      this.onBreadcrumbItemClick({ id: this.rootLabel, label: this.rootLabel });
      this.$emit("rootClick");
    }
  }
};
