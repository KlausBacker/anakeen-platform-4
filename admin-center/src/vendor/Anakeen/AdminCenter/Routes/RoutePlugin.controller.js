
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
          { field: 'name', title:'Name'},
          { field: 'method', title: 'Method', width: '6rem' },
          { field: 'pattern', title: 'Pattern'},
          { field: 'description', title: 'Description' },
          { field: 'priority', title: 'Priority',width: '6rem' },
          { field: 'overrided', title: 'Overrided' , width :'10rem'},
          {
            template: '<input type="checkbox" class="activation-switch" aria-label="Activation Switch"/>',
            width: '10rem',
            filterable: false,
          },
        ],
        filterable: false,
        resizable: false,
        expand: true,
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
        this.$('.activation-switch').kendoMobileSwitch();
      },
    })

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