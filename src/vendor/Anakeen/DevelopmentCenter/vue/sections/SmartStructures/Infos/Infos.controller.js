import PropertiesView from "devComponents/PropertiesView/PropertiesView.vue";
import InfosNav from "devComponents/SSInfoNav/SSInfoNav.vue";
import StructureHierarchy from "./StructureHierarchy.vue";

const formatChildrenList = (parent, children) => {
  const result = children
    .filter(c => c.parent === parent)
    .map(c => ({
      name: c.name,
      children: formatChildrenList(c.name, children)
    }));
  return result;
};

const URLS = {
  json: {
    structure: "/api/v2/families/<identifier>/views/structure",
    element: "/api/v2/documents/<identifier>.json",
    default: "/api/v2/documents/<identifier>.json"
  },
  xml: {
    structure: "/api/v2/devel/config/smart/structures/<identifier>.xml",
    element: "/api/v2/documents/<identifier>.xml",
    workflow: `/api/v2/devel/config/smart/workflows/<identifier>.xml`,
    default: "/api/v2/documents/<identifier>.xml"
  },
  creation: {
    structure: "/api/v2/documents/<identifier>/views/!defaultCreation.html"
  }
};

export default {
  name: "Infos",
  components: {
    StructureHierarchy,
    PropertiesView,
    InfosNav
  },
  props: ["ssName"],
  watch: {
    ssName(newValue, oldValue) {
      if (newValue !== oldValue) {
        this.fetchStructureInfos();
      }
    },
    isReady(newValue) {
      if (newValue) {
        if (this.structureDetails && this.structureDetails.workflow) {
          this.otherInfoSections.push({
            label: "Workflow",
            path: `/devel/wfl/${this.structureDetails.workflow.name ||
              this.structureDetails.workflow.id}/infos`
          });
        }
      }
    }
  },
  data() {
    return {
      isReady: false,
      structureDetails: {},
      otherInfoSections: []
    };
  },
  computed: {
    urlInfo() {
      return `/api/v2/devel/smart/structures/${this.ssName}/info/`;
    },
    structureProperties() {
      return this.structureDetails
        ? this.structureDetails.properties || {}
        : {};
    },
    structureInfo() {
      return this.structureDetails ? this.structureDetails.info || {} : {};
    },
    structureHierarchy() {
      let data = [];
      let tree = [];
      if (this.structureProperties.parents && this.structureProperties.childs) {
        data = this.structureProperties.parents.reverse().concat(this.ssName);
        data.reduce((acc, curr, index, array) => {
          const element = { name: curr, children: [] };
          if (index === array.length - 1) {
            element.children = formatChildrenList(
              this.ssName,
              this.structureProperties.childs
            );
          }
          acc.push(element);
          return element.children;
        }, tree);
      }
      return tree;
    }
  },
  mounted() {
    this.fetchStructureInfos();
    this.otherInfoSections = [
      { label: "User Interface", path: `/devel/ui/${this.ssName}/infos` },
      {
        label: "Security",
        path: `/devel/security/smartStructures/${this.ssName}/infos`
      }
    ];
  },
  methods: {
    onView(viewType) {
      const url = URLS[viewType].structure.replace("<identifier>", this.ssName);
      if (url) {
        window.open(url);
      }
    },
    onCreate() {
      const url = URLS.creation.structure.replace("<identifier>", this.ssName);
      if (url) {
        window.open(url);
      }
    },
    fetchStructureInfos() {
      this.$http
        .get(this.urlInfo)
        .then(response => {
          this.structureDetails = response.data.data;
          this.isReady = true;
        })
        .catch(err => {
          console.error(err);
        });
    },
    formatTags(tags) {
      if (tags) {
        return Object.keys(tags)
          .map(key => {
            return `<b>${key} :</b> ${tags[key]}`;
          })
          .join("<br/>");
      }
    }
  }
};
