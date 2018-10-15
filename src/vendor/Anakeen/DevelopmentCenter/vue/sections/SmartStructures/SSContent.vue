<template>
    <div class="ss-section">
        <div class="ss-nav">
            <router-link class="smart-structures-nav-link" tag="div" :to="{name: 'SmartStructures::infos'}">Infos
            </router-link>
            <router-link class="smart-structures-nav-link" tag="div" :to="{name: 'SmartStructures::others'}">Others
            </router-link>
        </div>
        <router-multi-view class="ss-content"></router-multi-view>
    </div>
</template>

<script>
  export default {
    name: "SSContent"
  }
</script>

<style lang="scss" scoped>
    .ss-section {
        display: flex;
        flex-direction: column;

        .ss-nav {
            display: flex;
            .smart-structures-nav-link {
                cursor: pointer;
                border-radius: .25rem .25rem 0 0;
                padding: .5rem;
                background: white;
                &.router-link-active {
                    z-index: 10;
                    margin-bottom: -1px;
                    border: 1px solid rgba(33, 37, 41, .125);
                    border-bottom-color: transparent;
                }
            }
        }


        .ss-content {
            flex: 1;
            padding: 1rem;
            border: 1px solid rgba(33, 37, 41, .125);
            border-radius: .25rem;
        }

    }
</style>