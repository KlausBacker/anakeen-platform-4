<template>
    <div class="reportingTab__wrapper" ref="wrapper">
        <h1 class="reportingTab__title">Dépenses pour l'année {{year}}</h1>
        <div class="reportingTab__chart" ref="chart"></div>
        <div class="reportingTab__categories">
            <table class="reportingTab__categories__table table-bordered table table-hover">
                <thead>
                    <tr>
                        <th>Catégories</th>
                        <th>Montant</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="cat in categoriesArray">
                        <td>{{cat.name}}</td>
                        <td>{{cat.amount}} €</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./reportingTab.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./reportingTab.controller.js"></script>
