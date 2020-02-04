// Grid filter cell mixin
import { Component, Prop, Vue } from "vue-property-decorator";
import GridController from './AnkSEGrid.component';

@Component
export default class FilterCellMixin extends Vue {
    @Prop()
    public grid!: GridController;
    @Prop()
    public field!: string;
    @Prop()
    public filterType!: string;
    @Prop()
    public value!: string | number | boolean | Date;
    @Prop()
    public operator!: string;

    public get columnConfig() {
        if (this.grid && this.grid.columns) {
            const match = this.grid.columns.filter(c => c.field === this.field);
            if (match && match.length) {
                return match[0];
            }
        }
        return null;
    }
}