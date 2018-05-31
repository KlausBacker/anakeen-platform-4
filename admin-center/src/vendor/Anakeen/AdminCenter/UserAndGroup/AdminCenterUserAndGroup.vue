<template>
    <div class="contentPart" ref="contentPart">
        <div ref="treeViewPart">
            <div class="btnPart">
                <button class="btn btn-link material-icons" @click="updateTreeData">refresh</button>
                <button class="btn btn-link material-icons" @click="collapseAll">expand_less</button>
                <button class="btn btn-link material-icons" @click="expandAll">expand_more</button>
            </div>
            <div>
                <form @submit="filterGroup">
                <input type="text" ref="filterTree" class="form-control" placeholder="Search..."/>
                </form>
            </div>
            <kendo-treeview ref="groupTreeView"
                            :data-source="groupTree"
                            :template="'#= item.title # (#= item.nbUser #)'"
                            @change="onGroupSelect"
                            @expand="registerTreeState"
                            @collapse="registerTreeState">
            </kendo-treeview>
        </div>
        <div ref="centerPart">
            <div ref="documentPart">
                <ank-document ref="groupDoc" class="groupDoc"></ank-document>
            </div>
            <kendo-grid ref="grid"
                        :data-source="gridContent"
                        :pageable="{'alwaysVisible': true, pageSizes: [10, 20, 100]}"
                        :sortable='true'
                        :filterable="{ extra: false, operators: { string: { contains: 'contains' } } }"
                        :persistSelection="true"
                        :autoBind="false">
                <kendo-grid-column :selectable="true" width="50px"></kendo-grid-column>
                <kendo-grid-column field="login" title="Login" type="string"></kendo-grid-column>
                <kendo-grid-column field="firstname" title="First name" type="string"></kendo-grid-column>
                <kendo-grid-column field="lastname" title="Last name" type="string"></kendo-grid-column>
                <kendo-grid-column field="mail" title="E-mail" type="string"></kendo-grid-column>
            </kendo-grid>
        </div>
    </div>
</template>
<style >
    @import "./AdminCenterUserAndGroup.css";
</style>
<script src="./AdminCenterUserAndGroup.controller.js"></script>