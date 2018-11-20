<template>
    <div class="profile-acls-view">
        <div ref="profileContent">
            <div ref="profileDescribe" class="profile-describe container-fluid">
                <div class="row profile-infos">
                    <div class="col-sm">
                        Title : <b>{{title}}</b>
                    </div>
                    <div class="col-sm">
                        Logical name : <b>{{name}}</b>
                    </div>
                    <div class="col-sm">
                        Id : <b>{{id}}</b>
                    </div>
                    <div v-if="detachable" class="detach-button">
                        <button class="k-button k-button-icon" @click="onDetachProfile"><i class="k-icon k-i-hyperlink-open"></i></button>
                    </div>
                </div>
                <div class="row-sm profile-toolbar">
                    <div class="col-sm">
                        <div class="switch-container">
                            <label class="switch">
                                <input type="checkbox" id="extendedView" v-model="displayAllElements" @change="updateGrid">
                                <span class="slider round"></span>
                            </label>
                            <label class="switch-label" for="extendedView">
                                <span>Show all</span>
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

<style lang="scss">
    .profile-acls-view {
        display: flex;
        flex-direction: column;
        min-height: 0;
        .k-grid-header {
            background: #F5F5F5;
            border-bottom: 1px solid rgba(33, 37, 41, 0.125);
        }
        .k-grid-header .k-header {
            border: 1px solid rgba(33, 37, 41, 0.125);
            border-bottom-width: 0;
            border-right-width: 0;

            &[data-index="0"] {
                border-left-width: 0;
                border-right-width: 0;
                border-top-width: 0;
                & + .k-header {
                    border-top-width: 0;
                }
            }
        }

        .k-grid table {
            border-right: 1px solid rgba(33, 37, 41, 0.125);;
            border-bottom: 1px solid rgba(33, 37, 41, 0.125);;
        }

        .k-grid-content {
            tr[role=row] {
                & > td[role=gridcell] {
                    padding: 0.5rem;
                }
            }
        }

        .k-treelist.k-grid {
            flex: 1;
            border-width: 0;
        }

        .profile-toolbar {
            border-top: 1px solid rgba(33, 37, 41, 0.125);
            border-bottom: 1px solid rgba(33, 37, 41, 0.125);
            background-color: #f5f5f5;
            justify-content: space-between;
        }

        .profile-infos {
            background: #f1f1f1;
            align-items: center;
            padding: 0.75rem 0;
            .detach-button {
                margin-right: .75rem;
            }
        }

        .container-fluid {
            padding-right: 0;
            padding-left: 0;

            .row {
                margin-right: auto;
                margin-left: auto;
            }
        }
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
    }
</style>