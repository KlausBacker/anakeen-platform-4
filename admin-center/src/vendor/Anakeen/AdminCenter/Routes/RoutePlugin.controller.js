
export default {
  name: 'admin-center-list-routes',
  data() {
    return {
      allRoutesDataSource: new kendo.data.TreeListDataSource({
        transport: {
          read: '/api/v2/admin/routes/'
        },
      }),
    }
  },
  methods: {
    initTreeList(){
      this.$('#routes-tree').kendoTreeList({
        dataSource: this.allRoutesDataSource,
        columns: [
          { field: 'name', title:'Name', sortable: true,width: '20%'},
          { field: 'method', title: 'Method', width: '5%',sortable: false},
          { field: 'pattern', title: 'Pattern', sortable: true,width: '30%'},
          { field: 'description', title: 'Description',sortable: false ,width: '30%'},
          { field: 'priority', title: 'Priority',width: '6rem', filterable: false,sortable: false,width: '5%' },
          { field: 'overrided', title: 'Overrided' , width :'9rem', filterable: false,sortable: false,width: '5%'},
          {
            template: '<input type="checkbox" class="activation-switch" aria-label="Activation Switch"/>',
            width: '5%',
            filterable: false,
            sortable: false,
          },
        ],
        filterable: true,
        sortable: true,
        resizable: false,
        expand: (e) => {
          this.addClassToRow(e.sender);
          this.saveTreeState();
        },
        collapse: (e) => {
          this.addClassToRow(e.sender);
          this.saveTreeState();
        },
        dataBound: (e) => {
          this.addClassToRow(e.sender);
          this.restoreTreeState();
          this.$('.activation-switch:not(.activation-switch[data-role=switch])').kendoMobileSwitch();
        },
    })
    .on('click', '.expand-btn', () => this.expand(true))
    .on('click', '.collapse-btn', () => this.expand(false))
    },
    addClassToRow(treeList) {
      let items = treeList.items();
      setTimeout(() => {
        items.each(function addTypeClass() {
          let dataItem = treeList.dataItem(this);
          if (dataItem.rowLevel) {
            $(this).addClass('tree-level-' + dataItem.rowLevel);
          }
        });
      }, 0);
    },
    expand(expansion) {
      let treeList = this.$('.routes-tree').data('kendoTreeList');
      let $rows = this.$('tr.k-treelist-group', treeList.tbody);
      this.$.each($rows, (idx, row) => {
        expansion?treeList.expand(row):treeList.collapse(row);
      });
      this.saveTreeState();
      this.addClassToRow(treeList);
    },
    saveTreeState() {
      setTimeout(() => {
        let treeState = [];
        let treeList = this.$('.routes-tree').data('kendoTreeList');
        let items = treeList.items();
        items.each((index, item) => {
          if ($(item).attr('aria-expanded') === 'true')
            treeState.push(index);
        });
        window.localStorage.setItem('admin.routes.treeState', treeState);
      }, 0);
    },
    restoreTreeState() {
      let treeState = window.localStorage.getItem('admin.routes.treeState');
      if (treeState) {
        let treeList = this.$('.routes-tree').data('kendoTreeList');
        let $rows = this.$('tr', treeList.tbody);
        this.$.each($rows, (idx , row) => {
          treeState.includes(idx)?treeList.expand(row):treeList.collapse(row);
        });
        this.addClassToRow(treeList);
      }
    },
  },

  mounted() {
    this.initTreeList();
    this.restoreTreeState();
    window.addEventListener('resize', () => {
      let $tree = this.$('.routes-tree');
      let ktree = $tree.data('kendoTreeList');
      if (ktree) {
        $tree.height(this.$(window).height() - $tree.offset().top - 4);
        ktree.resize();
      }
    });
  },
};