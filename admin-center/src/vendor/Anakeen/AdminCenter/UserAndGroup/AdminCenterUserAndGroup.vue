<template>
    <div class="accountManager" ref="contentPart">
        <div ref="treeViewPart">
            <div class="accountManager_treeViewPart_wrapper">
                <div class="accountManager_treeViewPart_wrapper_topPart">
                    <div class="accountManager_treeViewPart_wrapper_topPart_btnPart">
                        <button class="btn btn-link material-icons" @click="updateTreeData">refresh</button>
                        <button class="btn btn-link material-icons" @click="collapseAll">expand_less</button>
                        <button class="btn btn-link material-icons" @click="expandAll">expand_more</button>
                    </div>
                    <div class="accountManager_treeViewPart_wrapper_searchPart">
                        <form @submit="filterGroup">
                            <input type="text" ref="filterTree" class="form-control" placeholder="Search..."/>
                        </form>
                    </div>
                </div>
                <div class="accountManager_treeViewPart_wrapper_content">
                    <kendo-treeview ref="groupTreeView"
                                    :data-source="groupTree"
                                    :template="'#= item.title # (#= item.nbUser #)'"
                                    @change="onGroupSelect"
                                    @expand="registerTreeState"
                                    @collapse="registerTreeState">
                    </kendo-treeview>
                </div>
            </div>
        </div>
        <div class="accountManager_contentPart" ref="centerPart">
            <div class="accountManager_contentPart_groupPart" ref="documentPart">
                <ank-document ref="groupDoc" ></ank-document>
            </div>
            <div class="accountManager_contentPart_gridPart">
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
    </div>
</template>
<style >
    @import "./AdminCenterUserAndGroup.css";
</style>
<script src="./AdminCenterUserAndGroup.controller.js"></script>