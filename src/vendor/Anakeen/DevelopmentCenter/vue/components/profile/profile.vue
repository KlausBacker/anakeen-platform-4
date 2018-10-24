<template>
    <div>
        <div ref="profileContent">
            <div ref="profileDescribe" class="container-fluid">
                <div class="row">
                    <div class="col-sm">
                        Title : {{title}}
                    </div>
                    <div class="col-sm">
                        Logical name : {{name}}
                    </div>
                    <div class="col-sm">
                        Id : {{id}}
                    </div>
                </div>
                <div class="row-sm">
                    <div class="col-sm form-check">
                        <input class="form-check-input" type="checkbox" id="extendedView" v-model="extendedView" @change="updateGrid">
                        <label class="form-check-label" for="extendedView"><template v-if="extendedView"> All groups and role </template><template v-else>Only checked roles and groups</template></label>
                    </div>
                </div>
            </div>
        </div>
        <div ref="profileTreeList"> </div>
    </div>
</template>

<script src="./profile.controller.js">
</script>

<style>
.k-grid .rightColumn {
    text-align: center;
}
.right-set {
    color: greenyellow;
}
.right-inherited {
    color: grey;
}
</style>