<template>
    <div class="profile-acls-view">
        <div ref="profileContent" class="profile-content">
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
                    <div v-if="refProfile" class="col-sm">
                        Computed from : <a href="#" @click="onClickRefProfile"><img :src="refProfile.icon"/> {{refProfile.title || refProfile.name || refProfile.id}}</a>
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

<style scoped lang="scss">
    @import "./profile.scss";
</style>