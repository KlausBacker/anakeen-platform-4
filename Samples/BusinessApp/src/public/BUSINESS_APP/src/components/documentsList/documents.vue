<template>
    <div class="documentsList__documents__wrapper">
        <div class="documentsList__documents">
            <div class="documentsList__documents__logo">LOGO</div>

            <div class="documentsList__documents__header__wrapper">
                <div class="documentsList__documents__header">
                    <div class="documentsList__documents__header__label">
                        (Nom collection)
                    </div>
                </div>
            </div>

            <div class="documentsList__documents__search__wrapper">
                <div class="documentsList__documents__search">
                    <div class="input-group">
                        <span class="input-group-btn">
                            <button class="btn btn-default documentsList__documents__search__reset__button">
                                <span class="k-icon k-i-close"></span>
                            </button>
                        </span>
                        <input type="text"
                               class="form-control documentsList__documents__search__keyword"
                               placeholder="Mot clé" value="">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default documentsList__documents__search__button">
                                            <span class="k-icon k-i-search"></span>
                                        </button>
                                    </span>
                    </div>
                </div>
            </div>

            <div class="documentsList__documents__summary__wrapper">
                <div class="form-inline documentsList__documents__summary">
                    <div class="form-control-static documentsList__documents__summary__description">
                        0 - 0 sur 0
                    </div>
                    <div class="documentsList__documents__summary__nav btn-group">
                        <button class="documentsList__documents__summary__nav__item documentsList__documents__summary__nav__item--previous btn btn-default"
                                disabled="true" data-page="prev">
                            <span class="k-icon k-i-arrow-double-60-left"></span>
                        </button>
                        <button class="documentsList__documents__summary__nav__item documentsList__documents__summary__nav__item--next btn btn-default"
                                data-page="next" disabled="true">
                            <span class="k-icon k-i-arrow-double-60-right"></span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="documentsList__documents__list__container"></div>

            <div class="documentsList__documents__pagination__wrapper dropup">
                <nav class="documentsList__documents__pagination">
                    <div class="input-group">
                        <div class="input-group-btn">
                            <button class="btn btn-default documentsList__documents__pagination__settings"
                                    data-toggle="dropdown">
                                <span class="fa fa-list-ol"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li class="dropdown-header">
                                    Éléments par page
                                </li>
                                <li class="">
                                    <a class="documentsList__documents__pagination__pageSize"
                                       href="#" data-pagesize="5">
                                        5
                                    </a>
                                </li>
                                <li class="active">
                                    <a class="documentsList__documents__pagination__pageSize"
                                       href="#" data-pagesize="10">
                                        10
                                    </a>
                                </li>
                                <li class="">
                                    <a class="documentsList__documents__pagination__pageSize"
                                       href="#" data-pagesize="25">
                                        25
                                    </a>
                                </li>
                                <li class="">
                                    <a class="documentsList__documents__pagination__pageSize"
                                       href="#" data-pagesize="50">
                                        50
                                    </a>
                                </li>
                                <li class="">
                                    <a class="documentsList__documents__pagination__pageSize"
                                       href="#" data-pagesize="100">
                                        100
                                    </a>
                                </li>
                            </ul>
                            <button class="btn btn-default documentsList__documents__pagination__item documentsList__documents__pagination__item--first"
                                    data-page="first" disabled="disabled">
                                <span class="fa fa-fast-backward"></span>
                            </button>
                            <button class="btn btn-default documentsList__documents__pagination__item documentsList__documents__pagination__item--previous"
                                    data-page="prev" disabled="disabled">
                                <span class="fa fa-backward"></span>
                            </button>
                        </div>
                        <input class="form-control documentsList__documents__pagination__pageNumber"
                               type="text">
                        <span class="input-group-addon">
                            /&nbsp;
                        </span>
                        <div class="input-group-btn">
                            <button class="btn btn-default documentsList__documents__pagination__item documentsList__documents__pagination__item--next"
                                    data-page="next">
                                <span class="fa fa-forward"></span>
                            </button>
                            <button class="btn btn-default documentsList__documents__pagination__item documentsList__documents__pagination__item--last"
                                    data-page="last">
                                <span class="fa fa-fast-forward"></span>
                            </button>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</template>
