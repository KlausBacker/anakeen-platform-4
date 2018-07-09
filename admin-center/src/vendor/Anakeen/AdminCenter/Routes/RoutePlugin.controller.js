
export default {
  name: 'admin-center-list-routes',
  data() {
    return {
      allRoutesDataSource: new kendo.data.TreeListDataSource({
        transport: {
          read: '/api/v2/admin/routes/'
        },
      }),
      allMiddlewareDataSource: new kendo.data.TreeListDataSource({
        transport: {
          read: '/api/v2/admin/middlewares/'
        },
      }),
    }
  },
  methods: {
    initTreeList(){
      let refreshBtn = `
                <div class="routes-toolbar">
                    <button class="btn btn-secondary toolbar-btn refresh-btn">
                        <i class="material-icons">refresh</i>
                    </button>
                </div>
            `;
      this.$('.routes-tab').select(
        this.$('.routes-tree').kendoTreeList({
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
          toolbar: refreshBtn,
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
            this.$('.activation-switch:not(.activation-switch[data-role=switch])').kendoMobileSwitch({
              change: (e) => {
                this.$ankApi.post('admin/routes/', { toggleValue: e.checked });
              },
            });
          },
        })
          .on('click', '.refresh-btn', () => this.allRoutesDataSource.read())
      );
      this.$('.middlewares-tab').select(
        this.$('.middlewares-tree').kendoTreeList({
          dataSource: this.allMiddlewareDataSource,
          columns: [
            { field: 'name', title: 'Name', sortable: true, width: '30%',filterable: true,sortable: true},
            { field: 'method', title: 'Method', width: '5%',filterable: true,sortable: false},
            { field: 'pattern', title: 'Pattern', width: '30%',filterable: true,sortable: true},
            { field: 'description', title: 'Description', width: '30%',filterable: true,sortable: false},
            { field: 'priority', title: 'Priority',width: '5%',filterable: false,sortable: false},
          ],
          toolbar: refreshBtn,
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
          }
        })
          .on('click', '.refresh-btn', () => this.allMiddlewareDataSource.read())
      );
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
    this.$('.tabstrip').kendoTabStrip({
      animation: {
        open: {
          effects: 'fadeIn',
        }
      }
    });
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