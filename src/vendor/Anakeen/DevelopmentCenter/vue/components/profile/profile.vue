<template>
    <div>
        <div>
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
            </div>
        </div>
        <div ref="profileTreeList"> </div>
    </div>
</template>

<script src="./profile.controller.js">
</script>

<style>

</style>