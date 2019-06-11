<template>
    <ul class="smart-structure-hierarchy">
        <li v-for="(item, index) in hierarchy" :key="`${currentStructure}-${item.name}-${index}`" class="smart-structure-hierarchy-item">
            <a data-role="develRouterLink" :href="`/devel/smartStructures/${item.name}/infos`" :class="{ 'smart-structure-hierarchy-label': true, 'current-item': item.name === currentStructure }">{{item.name}}</a>
            <structure-hierarchy v-if="item.children && item.children.length" :data="item.children" :currentStructure="currentStructure"></structure-hierarchy>
        </li>
    </ul>
</template>

<script>
  export default {
    name: "structure-hierarchy",
    props: ["data", "currentStructure"],
    data() {
      return {
        hierarchy: JSON.parse(JSON.stringify(this.data))
      }
    },
  }
</script>

<style lang="scss" scoped>
    .smart-structure-hierarchy {

        .smart-structure-hierarchy-item {

            .smart-structure-hierarchy-label {
                display:inline-block;
                cursor: pointer;
                &.current-item {
                    background: #fdc69f;
                }
                background: #e3edf6;
                border: 2px solid #5f9ccc;

                text-align: center;
                padding: 0.33em 0.66em;

            }
        }
        list-style: none;

        $border-width: 2px;

        ul {
            position: relative;
            padding: 1em 0;
            white-space: nowrap;
            margin: 0 auto;
            text-align: center;
            &::after {
                content: '';
                display: table;
                clear: both;
            }
        }

         li {
            display: inline-block; // need white-space fix
            vertical-align: top;
            text-align: center;
            list-style-type: none;
            position: relative;
            padding: 1em .5em 0 .5em;
            &::before,
            &::after {
                content: '';
                position: absolute;
                top: 0;
                right: 50%;
                border-top: $border-width solid #ccc;
                width: 50%;
                height: 1em;
            }
            &::after {
                right: auto;
                left: 50%;
                border-left: $border-width solid #ccc;
            }
            &:only-child::after,
            &:only-child::before {
                display: none;
            }
            &:only-child {
                padding-top: 0;
            }
            &:first-child::before,
            &:last-child::after {
                border: 0 none;
            }
            &:last-child::before{
                border-right: $border-width solid #ccc;
                border-radius: 0 5px 0 0;
            }
            &:first-child::after{
                border-radius: 5px 0 0 0;
            }
        }

        ul::before{
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            border-left: $border-width solid #ccc;
            width: 0;
            height: 1em;
        }

        li a {
            border: $border-width solid #ccc;
            padding: .5em .75em;
            text-decoration: none;
            display: inline-block;
            border-radius: 5px;
            color: #333;
            position: relative;
            top: $border-width;
        }
    }
</style>