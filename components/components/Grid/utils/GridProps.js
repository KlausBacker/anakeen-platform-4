export default {
  persistStateKey: {
    type: String,
    default: ""
  },
  contextTitles: {
    type: Boolean,
    default: true
  },
  contextTitlesSeparator: {
    type: String,
    default: "-"
  },
  urlConfig: {
    type: String,
    default: "/api/v2/grid/config/<collection>"
  },
  urlExport: {
    type: String,
    default: "/api/v2/grid/export/<transaction>/<collection>"
  },
  urlContent: {
    type: String,
    default: "/api/v2/grid/content/<collection>"
  },
  collection: {
    type: String,
    default: ""
  },
  emptyCell: {
    type: String,
    default: ""
  },
  notExistValue: {
    type: String,
    default: "N/A"
  },
  sortable: {
    type: String,
    default: "multiple"
  },
  serverSorting: {
    type: Boolean,
    default: true
  },
  filterable: {
    type: String,
    default: "menu"
  },
  reorderable: {
    type: Boolean,
    default: false
  },
  serverFiltering: {
    type: Boolean,
    default: true
  },
  pageable: {
    type: Boolean,
    default: true
  },
  pageSizes: {
    type: [Boolean, Array],
    default: () => [10, 20, 50]
  },
  serverPaging: {
    type: Boolean,
    default: true
  },
  resizable: {
    type: Boolean,
    default: true
  },
  data: {
    type: Array,
    default: () => []
  },
  selectable: {
    type: Boolean | String,
    default: false
  },
  checkable: {
    type: Boolean,
    default: false
  },
  persistSelection: {
    type: Boolean,
    default: true
  }
};
