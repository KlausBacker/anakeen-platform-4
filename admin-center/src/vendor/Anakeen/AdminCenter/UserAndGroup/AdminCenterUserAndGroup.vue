<template>
    <div class="contentPart" ref="contentPart">
        <div ref="treeViewPart">
            <div class="btnPart">
                <button class="btn btn-link material-icons" @click="updateData">refresh</button>
                <button class="btn btn-link material-icons" @click="collapseAll">expand_less</button>
                <button class="btn btn-link material-icons" @click="expandAll">expand_more</button>
            </div>
            <div>
                <form>
                <input type="text" class="form-control" ref="searchInput" placeholder="Search..."/>
                </form>
            </div>
            <kendo-treeview ref="groupTreeView" :data-source="groupTree" :data-text-field="['title']" @change="onGroupSelect" @expand="registerTreeState" @collapse="registerTreeState">
            </kendo-treeview>
        </div>
        <div ref="centerPart">
            <div ref="documentPart"></div>
            <kendo-grid :data-source="gridContent" :pageable='true' :sortable='true' :filterable="true" :persistSelection="true">
                <kendo-grid-column selectable="true" width="50px"></kendo-grid-column>
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