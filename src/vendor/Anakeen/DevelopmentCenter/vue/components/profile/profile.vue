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
                    <div class="col-sm">
                        <div class="switch-container">
                            <label class="switch">
                                <input type="checkbox" id="extendedView" v-model="displayAllElements" @change="updateGrid">
                                <span class="slider round"></span>
                            </label>
                            <label class="switch-label" for="extendedView">
                                <template v-if="displayAllElements"> All groups and role</template>
                                <template v-else>Only checked roles and groups</template>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div ref="profileTreeList"></div>
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

    /* The switch - the box around the slider */
    .switch {
        position: relative;
        display: inline-block;
        width: 2rem;
        height: 1rem;
    }

    /* Hide default HTML checkbox */
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    /* The slider */
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 0.76rem;
        width: 0.76rem;
        left: 0.1333rem;
        bottom: 0.1333rem;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }

    input:checked + .slider {
        background-color: #2196F3;
    }

    input:focus + .slider {
        box-shadow: 0 0 1px #2196F3;
    }

    input:checked + .slider:before {
        -webkit-transform: translateX(0.76rem);
        -ms-transform: translateX(0.76rem);
        transform: translateX(0.76rem);
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 1rem;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    .switch-container {
        margin-top: 1rem;
    }

    .switch-label {
        vertical-align: middle;
    }
</style>