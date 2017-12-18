<template>
    <div class="reportingTab__wrapper">
        <div class="reportingTab__chart" ref="chart"></div>
        <div class="reportingTab__categories">
            <table class="reportingTab__categories__table table-bordered table table-hover">
                <thead>
                    <tr>
                        <th>Catégories</th>
                        <th>Fournisseur</th>
                        <th>Montant</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Logement</td>
                        <td>Toto</td>
                        <td>122 €</td>
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
