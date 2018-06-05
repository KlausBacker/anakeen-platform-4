<template>
    <div class="accountManager" >
        <div class="accountManager_wrapper" ref="accountManager_wrapper">
            <div v-show="!userModeSelected" class="accountManager_gridAndTreePart" ref="gridAndTreePart">
                <div ref="treeViewPart">
                    <div class="accountManager_treeViewPart_wrapper">
                        <div class="accountManager_treeViewPart_wrapper_topPart">
                            <div class="accountManager_treeViewPart_wrapper_topPart_btnPart">
                                <kendo-toolbar>
                                    <kendo-toolbar-item type="button" icon="refresh" @click="updateTreeData"></kendo-toolbar-item>
                                    <kendo-toolbar-item type="button" icon="sort-asc-sm" @click="collapseAll"></kendo-toolbar-item>
                                    <kendo-toolbar-item type="button" icon="sort-desc-sm" @click="expandAll"></kendo-toolbar-item>
                                </kendo-toolbar>
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
                    <div  class="accountManager_contentPart_groupPart" ref="documentPart">
                        <div v-show="displayGroupDocument" class="accountManager_contentPart_groupPart_wrapper">
                            <kendo-toolbar ref="groupToolbar">
                                <kendo-toolbar-item type="button" icon="plus-sm"></kendo-toolbar-item>
                            </kendo-toolbar>
                            <ank-document ref="groupDoc" ></ank-document>
                        </div>
                        <div v-show="!displayGroupDocument">
                            No group selected
                        </div>
                    </div>
                    <div class="accountManager_contentPart_gridPart">
                        <kendo-toolbar ref="userToolbar">
                            <kendo-toolbar-item type="button" icon="plus-sm"></kendo-toolbar-item>
                        </kendo-toolbar>
                        <kendo-grid ref="grid"
                                    :data-source="gridContent"
                                    :pageable="{'alwaysVisible': true, pageSizes: [10, 20, 100]}"
                                    :sortable='true'
                                    :filterable="{ extra: false, operators: { string: { contains: 'contains' } } }"
                                    :persistSelection="true"
                                    :autoBind="false">
                            <kendo-grid-column :selectable="true" width="50px"></kendo-grid-column>
                            <kendo-grid-column field="id" template='<a class="btn btn-link material-icons openButton" data-initid="#= id #" href="/api/v2/documents/#= id #.html" >open_in_new</a>' width="50px" title=" " :filterable=false ></kendo-grid-column>
                            <kendo-grid-column field="login" title="Login" type="string"></kendo-grid-column>
                            <kendo-grid-column field="firstname" title="First name" type="string"></kendo-grid-column>
                            <kendo-grid-column field="lastname" title="Last name" type="string"></kendo-grid-column>
                            <kendo-grid-column field="mail" title="E-mail" type="string"></kendo-grid-column>
                        </kendo-grid>
                    </div>
                </div>
            </div>
            <div v-show="userModeSelected" class="accountManager_userPart">
                <kendo-toolbar>
                    <kendo-toolbar-item type="button" icon="arrow-double-60-left" @click="toggleUserMode"></kendo-toolbar-item>
                </kendo-toolbar>
                <ank-document ref="openDoc" ></ank-document>
            </div>
        </div>
    </div>
</template>
<style >
    @import "./AdminCenterAccount.css";
</style>
<script src="./AdminCenterAccount.controller.js"></script>