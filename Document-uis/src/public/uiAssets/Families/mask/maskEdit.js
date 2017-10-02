require(['jquery', 'datatables'], function ($, datatables) {
  "use strict";
  var myTable = [];
  var cellIdx = 0;
  window.dcp.document.documentController("addEventListener", "change", {
      "name": "getMaskAttributes"
    },
    function getMaskAttributes(event, documentObject, attributeObject, values) {
      var attrs = [];
      $.getJSON("api/v1/families/" + values.current.value + "/views/structure", function (data) {
        $.each(data.data.family.structure, function () {
          var nneeded = "";
          var nvisibility= "";
          var need = neededValue(this.needed);
          var visible = visibilityValue(this.visibility);
          attrs.push({id: this.id,frame: ' ', order:this.logicalOrder,type:this.type, label: this.label,nvisibility: nvisibility, nneeded: nneeded, visibility: visible, needed: need});
          if (this.content) {
            for (var key in this.content) {
              if (this.content.hasOwnProperty(key)) {
                recursiveWalkThrough(this.content[key], this);
              }
            }
          }
        });
      }).done(function () {
        attrs.sort(function (a,b){
          if (a.order > b.order){
            return 1;
          } else if (a.order < b.order) {
            return -1;
          } else {
            return 0;
          }
        });
        $(document).ready(function () {
          myTable = $(".dcpArray__table").DataTable({
            searching: false, // remove this line to allow searching fields
            info: false,
            paging: false,
            data: attrs,
            ordering:false,
            autowidth: false,
            columns: [
              {title: "Ordre", data:"order",visible: false},
              {title: "Cadre" , data: "frame"},
              {title: "Label", data: "label"},
              {title: "Nouvelle visibilité", data:"nvisibility",
              render: function () {
                return '<select class="visibilityBtn"></select>';
              }
              },
              {title: "Nouvelle Obligation", data:"nneeded",
              render: function() {
                return '<select class="neededBtn"></select>';
              }},
              {title: "Visibilité par défaut", data:"visibility"},
              {title: "Obligation par défaut", data:"needed"}
            ],
            fnRowCallback: function (nRow, aData) {
              switch(aData.type){
                case 'frame': $(nRow).css('background-color','#6EB9D5')
                  break;
                case 'menu': $(nRow).css('background-color','#A4DEA8')
                  break;
                case 'array': $(nRow).css('background-color','#F3CC81')
                  break;
                case 'action': $(nRow).css('background-color','#E9EEDA')
                  break;
                default: $(nRow).css('background-color','#FFFFFF')
                  break;
              }
            }
          }).on("click", "td", function() {
            cellIdx = table.cell(this).index();
          });

         /*
          *
          * Columns searching fields
          *
          */

          // $('.dcpArray__table thead th').each( function () {
          //   var title = $('.dcpArray__table thead th').eq( $(this).index() ).text();
          //   $(this).html( '<input type="text" placeholder="Search '+title+'" />' +
          //     '<span label=""+title></span>' );
          // });
          // table.columns().every(function () {
          //   var column = this;
          //   $('input', this.header()).on('keyup change', function () {
          //     column.search(this.value).draw();
          //   });
          // });
         var animationVar = {
           close: {
             effects: "fadeOut",
             duration: 300
           },
           open: {
             effects: "fadeIn",
             duration: 300
           }
         };
         var visibilityData = [
           { value:" ", label:" "},
           { value:"W" , label:"Lecture et Ecriture"},
           { value:"R", label:"Lecture seule"},
           { value:"O", label:"Ecriture seule"},
           { value:"H", label:"Caché"},
           { value:"S", label:"Statique"},
           { value:"U", label:"Tableau statique"},
           { value:"I", label:"Invisible"}
         ];
         var neededData = [
           { value:" ", label: " "},
           { value:"Y", label: "O"},
           { value:"N", label: "N"},
         ];
          $(".neededBtn").kendoDropDownList({
            animation: animationVar,
            dataTextField: "label",
            dataValueField: "value",
            dataSource: neededData
          }).bind("change", onNeededChange);
          $(".visibilityBtn").kendoDropDownList({
            animation: animationVar,
            dataTextField: "label",
            dataValueField: "value",
            dataSource: visibilityData,
          }).bind("change", onVisibilityChange);
        });
        $(".dcpArray__table").empty();
      });
      function recursiveWalkThrough(tree, parent) {
        var nneeded = "";
        var nvisibility= "";
        var need = neededValue(tree.needed);
        var visible = visibilityValue(tree.visibility);
        attrs.push({id: tree.id, frame:parent.label ,order:tree.logicalOrder,type: tree.type, label: tree.label,nvisibility: nvisibility, nneeded: nneeded, visibility: visible, needed: need});
        if (tree.content) {
          for (var key in tree.content) {
            if (tree.content.hasOwnProperty(key)) {
              recursiveWalkThrough(tree.content[key], tree);
            }
          }
        }
      }
      function neededValue(needAttr){
        if (needAttr === 'Y' || needAttr) {
          return 'Obligatoire';
        } else if (needAttr ==='N' || !needAttr){
          return 'Optionnel';
        } else {
            return ' ';
        }
      }
      function visibilityValue(visibilityAttr){
        switch (visibilityAttr){
          case "W": return "Lecture et Ecriture";
          case "R": return "Lecture seule";
          case "O": return "Ecriture seule";
          case "H": return "Caché";
          case "S": return "Statique";
          case "U": return "Tableau statique";
          case "I": return "Invisibile";
          default : return " ";
        }
      }
      function onNeededChange(arg){
        myTable[cellIdx.row].nneeded = arg.target.value;
      }
      function onVisibilityChange(arg){
        myTable[cellIdx.row].nvisibility = arg.target.value;
      }
      this.documentController("addEventListener", "beforeSave", {
          "name": "beforeSave.mask",
        },
        function beforeSaveMask(event, currentDocumentObject){
        /*
         *
         * This function needs to addArrayRow to real document's table with myTable's information.
         *
         */
          myTable.forEach(function (x) {
            window.dcp.document.documentController('setValue', 'msk_attrids', {
              value: x.label, index: x.order
            });
            if (x.nneeded === ""){
              window.dcp.document.documentController('setValue', 'msk_needeeds', {
                value: x.needed, index: x.order
              });
            } else {
              window.dcp.document.documentController('setValue', 'msk_needeeds', {
                value: "* " + x.nneeded, index: x.order
              });
            }
            if (x.nvisibility === "") {
              window.dcp.document.documentController('setValue', 'msk_visibilities', {
                value: x.visibility, index: x.order
              });
            } else {
              window.dcp.document.documentController('setValue', 'msk_visibilities', {
                value: "* " + visibilityValue(x.nvisibility), index: x.order
              });
            }
          });
        }
      );
  });
});
