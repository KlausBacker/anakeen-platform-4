<template>
    <div class="documentsList__documents__wrapper">
        <div class="documentsList__documents" v-show="collection">
            <div class="documentsList__documents__logo">LOGO</div>

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
                        <span class="input-group-btn">
                            <button class="btn btn-default documentsList__documents__search__reset__button">
                                <span class="fa fa-times"></span>
                            </button>
                        </span>
                        <input type="text"
                               class="form-control documentsList__documents__search__keyword"
                               placeholder="Mot clé" value="">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default documentsList__documents__search__button">
                                            <span class="fa fa-search"></span>
                                        </button>
                                    </span>
                    </div>
                </div>
            </div>

            <div class="documentsList__documents__summary__wrapper">
                <div class="documentsList__documents__summary" ref="summaryPager">

                </div>
            </div>

            <div class="documentsList__documents__list" ref="listView"></div>

            <div class="documentsList__documents__list__pager_wraper">
                <div class="documentsList__documents__list__pagerCounter" ref="pagerCounter"></div>
                <div class="documentsList__documents__list__pager" ref="pager"></div>
            </div>
        </div>
    </div>
</template>
<script src="./documents.controller.js"></script>