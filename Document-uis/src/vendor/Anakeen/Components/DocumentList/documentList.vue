<template>
    <div class="documentsList__documents__wrapper" ref="wrapper">
        <div class="documentsList__documents" v-show="collection">
            <div class="documentsList__documents__logo">
                <img class="documentsList__documents__logo__img" :src="logoUrl"/>
            </div>

            <div class="documentsList__documents__header__wrapper">
                <div class="documentsList__documents__header">
                    <div class="documentsList__documents__header__label">
                        {{collection ? collection.html_label : ''}}
                    </div>
                </div>
            </div>

            <div class="documentsList__documents__search__wrapper">
                <div class="documentsList__documents__search">
                    <div class="input-group">
                        <input type="text"
                               class="form-control documentsList__documents__search__keyword"
                               :placeholder="`Rechercher${collection ? ' dans '+collection.html_label:''}`" v-model="filterInput" @change="filterDocumentsList(filterInput)"/>
                        <i class="input-group-addon material-icons documentsList__documents__search__keyword__remove" @click="clearDocumentsListFilter">
                            close
                        </i>
                        <i class="input-group-addon material-icons documentsList__documents__search__button" @click="filterDocumentsList(filterInput)">
                            search
                        </i>
                    </div>
                </div>
            </div>

            <div class="documentsList__documents__summary__wrapper">
                <div class="documentsList__documents__summary" ref="summaryPager">

                </div>
            </div>

            <div class="documentsList__documents__list" ref="listView"></div>

            <div class="documentsList__documents__list__pager_wraper">
                <input class="documentsList__documents__list__pagerCounter" ref="pagerCounter"/>
                <div class="documentsList__documents__list__pager" ref="pager"></div>
            </div>
        </div>
    </div>
</template>
<style lang="scss">
    @import './documentList.scss';
</style>
<script src="./documentList.controller.js"></script>