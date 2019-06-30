const DEFAULT_PROPS = [
  "title",
  "id",
  "name",
  "revision",
  "version",
  "workflow",
  "family",
  "createdBy",
  "security.lock.lockedBy",
  "confidential",
  "creationDate",
  "lastModificationDate",
  "security.profil",
  "security.profil.reference",
  "security.fieldAccess",
  "viewController",
  "tags"
];

const PROPS_LABELS = {
  title: "Title",
  id: "Id",
  name: "Logical Name",
  revision: "Revision",
  version: "Version",
  workflow: "Workflow",
  family: "Structure",
  createdBy: "Created by",
  "security.lock.lockedBy": "Locked by",
  confidential: "Confidential",
  creationDate: "Creation date",
  lastModificationDate: "Last modification date",
  "security.profil": "Profil",
  "security.profil.reference": "Reference profil",
  "security.fieldAccess": "Field Access",
  viewController: "View controller",
  tags: "Tags"
};

const emptyProp = propValue => {
  if (propValue === undefined || propValue === null) {
    return true;
  }
  if (Array.isArray(propValue) && propValue.length === 0) {
    return true;
  }
  if (propValue && typeof propValue === "object") {
    return propValue.id === 0;
  }
  return false;
};

export default {
  props: {
    elementId: {
      type: [String, Number],
      required: true
    },
    properties: {
      type: [String, Array],
      default: () => DEFAULT_PROPS
    },
    showEmpty: {
      type: Boolean,
      default: true
    }
  },
  watch: {
    elementId(newValue, oldValue) {
      if (newValue !== oldValue) {
        this.fetchProperties(newValue);
      }
    }
  },
  data() {
    return {
      propertiesList: []
    };
  },
  computed: {
    isReady() {
      return this.propertiesList.length !== 0;
    }
  },
  mounted() {
    this.fetchProperties(this.elementId);
  },
  devCenterRefreshData() {
    this.fetchProperties(this.elementId);
  },
  methods: {
    fetchProperties(eId) {
      this.$http
        .get(
          `/api/v2/smart-elements/${eId}.json?fields=document.properties.all`
        )
        .then(response => {
          if (response && response.data && response.data.data) {
            this.propertiesList = this.parseProperties(
              response.data.data.document.properties
            );
          }
        })
        .catch(err => {
          console.error(err);
          throw err;
        });
    },
    formatDate(date) {
      if (!date) {
        return "";
      }
      return kendo.toString(new Date(date), "g");
    },
    parseProperties(data) {
      const result = [];
      this.properties.forEach(propid => {
        let propPath = propid.split(".");
        if (propPath.length === 1) {
          propPath = [propid];
        }
        const propValue = propPath.reduce((acc, curr) => {
          if (acc) {
            return acc[curr];
          } else {
            return null;
          }
        }, data);
        if (this.showEmpty || !emptyProp(propValue)) {
          result.push({
            id: propid,
            label: PROPS_LABELS[propid],
            value: propValue
          });
        }
      });
      return result;
    },
    renderPropValue(propId, propValue) {
      if (!emptyProp(propValue)) {
        if (propId === "creationDate" || propId === "lastModificationDate") {
          return this.formatDate(propValue);
        }
        if (propId === "tags") {
          return Object.keys(propValue)
            .map(key => {
              return `<div class="se-property-tag"><b>${key} : </b>${propValue[key]}</div>`;
            })
            .join("&nbsp;");
        }
        if (typeof propValue === "string" || typeof propValue === "number") {
          return propValue;
        }
        if (typeof propValue === "object") {
          if (propValue.id) {
            return `<router-link><img src="${
              propValue.icon
            }"/> ${propValue.title ||
              propValue.name ||
              propValue.id}</router-link>`;
          }
        }
      }
      return "None";
    }
  }
};
