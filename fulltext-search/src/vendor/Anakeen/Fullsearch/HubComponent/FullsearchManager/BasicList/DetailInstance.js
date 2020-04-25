import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";

export default {
  props: {
    dataItem: Object
  },
  mixins: [AnkI18NMixin],
  template: `
    <section>
    <p><strong>Stemmer:</strong> {{dataItem.domainStem}}</p>
    <div>
    <strong>Analyzed structures:</strong>
    <ul class="structure-info">
    <li v-for="structure in dataItem.structures"  >
    <span :class="{ success: (structure.stats.totalToIndex === structure.stats.totalIndexed && structure.stats.totalDirty === 0) }">{{ structure.structure }}</span>
    <ul>
    <li>{{$t("AdminCenterFullsearch.Total to index")}} : {{structure.stats.totalToIndex}}</li>
    <li :class="{ warning: (structure.stats.totalToIndex > structure.stats.totalIndexed) }"> {{$t(
      "AdminCenterFullsearch.Total indexed"
    )}} : {{structure.stats.totalIndexed}}</li>
      <li :class="{ warning: (structure.stats.totalDirty > 0) }">{{$t(
        "AdminCenterFullsearch.Total not up to date"
      )}} : {{structure.stats.totalDirty}}</li>
        
        </ul>
        
        </li>
        </ul>
        <strong>Files indexing statuses:</strong>
        <ul  class="file-info"> 
        <li  v-for="fileStatus in dataItem.database.files"><span class="status">{{fileStatus.label}}</span> : <b>{{fileStatus.count}}</b> </li>
        </ul>
        </div>
        <p><strong>{{$t(
          "AdminCenterFullsearch.Database table size"
        )}}:</strong> {{dataItem.database.size.prettySize}}</p>
          </section>`
};
