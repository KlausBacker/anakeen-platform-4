# Smart Element Component

## Description

Le composant `Smart Element` permet d'utiliser le widget interne `Smart Element` d'Anakeen Platform et d'accéder à ses
fonctionnalités.

Les exemples de ce chapitre utiliseront un ensemble de Smart Elements factices issus de la Smart Structure `DEVBILL`
dont les Smart Fields sont décrits en `xml` ci-dessous :

```xml
<smart:fields>
    <smart:field-set name="bill_fr_ident" type="frame" label="Identification" access="ReadWrite">
        <smart:field-text name="bill_title" label="Title" access="ReadWrite" is-title="true"/>
        <smart:field-longtext name="bill_content" label="Description" access="ReadWrite"/>
        <smart:field-docid name="bill_author" label="Author" access="ReadWrite" relation="DEVPERSON"/>
        <smart:field-text name="bill_author_display" access="Read" is-title="true"/>
        <smart:field-date name="bill_billdate" label="Bill date" access="ReadWrite"/>
        <smart:field-text name="bill_location" label="City" access="ReadWrite"/>
        <smart:field-docid name="bill_clients" label="Clients" access="ReadWrite" relation="DEVCLIENT" multiple="true"/>
        <smart:field-set name="bill_otherclients" type="array" label="Other clients" access="ReadWrite">
            <smart:field-text name="bill_clientname" label="Client name" access="ReadWrite"/>
            <smart:field-text name="bill_society" label="Enterprise" access="ReadWrite"/>
        </smart:field-set>
        <smart:field-money name="bill_cost" label="Cost" access="ReadWrite"/>
    </smart:field-set>
</smart:fields>
```

## Initialisation

Le composant `AnkSmartElement` est défini dans la bibliothèque
"`@anakeen/user-interfaces/components/lib/AnkSmartElement.esm`" et la CSS
"`@anakeen/user-interfaces/components/scss/AnkSmartElement.scss`"

::: warning

Ce composant est [asynchrone](https://fr.vuejs.org/v2/guide/components-dynamic-async.html#Composants-asynchrones), il
faut donc le charger manière asynchrone dans vue (avec une closure). De plus, pour accéder au composant il faut attendre
que celui-ci soit monté, un évènement vue `@smartElementMounted` est mis à disposition pour pouvoir déclencher une
méthode à ce moment.

:::

::: warning

Ce composant doit être rendu dans un contenant ayant une taille donc pas en height=auto.

:::

:::: columns

::: column Exemple d'initialisation

```vue
<template>
  <div>
    <ank-smart-element ref="smartElement" :initid="98135" />
  </div>
</template>
<script>
import AnkSmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement.esm";
import "@anakeen/user-interfaces/components/scss/AnkSmartElement.scss";

export default {
  components: {
    "ank-smart-element": () => AnkSmartElement
  }
};
</script>
```

:::

::: column Aperçu du composant

![default](./Images/seInitidExample.png)

:::

::::

## Propriétés

### `initid (Number | String, default: 0)`

Id ou nom logique du `Smart Element` à afficher.

Dans cet exemple, nous affichons le Smart Element avec l'`initid` à 98135 :

```vue
<template>
  <div>
    <ank-smart-element ref="smartElement" :initid="98135" />
  </div>
</template>
```

![initid example](./Images/seInitidExample.png)

Pour plus d'informations, vous pouvez consulter la documentation sur les
[propriétés des Smart Elements](/apiphp/smartelement/#properties).

### `view-id (String, default: "!defaultConsultation`

Identifiant de la vue à utiliser. Par défaut, la vue utilisée est la vue de consultation (`!defaultConsultation`). Il
existe trois valeurs par défaut pour les vues : `!defaultConsultation`, `defaultEdition` et `defaultCreation`
(uniquement pour les Smart Structures).

Dans cet exemple, nous spécifions la vue par défaut de consultation pour notre Smart Element :

```vue
<template>
  <div>
    <ank-smart-element ref="smartElement" :initid="98135" viewId="!defaultEdition" />
  </div>
</template>
```

![viewId example](./Images/seViewIdEdition.png)

### `revision (Number, default: -1)`

Révision du `Smart Element`. Par défaut, elle vaut `-1` (_-1_ indique la revision la plus récente).

### `custom-client-data (Object, default: null)`

Propriété permettant d'ajouter des données clients au composant. Ces données pourront être accessibles grâce à la
méthode [getClientData](#getcustomclientdataboolean-deleteonce), ajoutées grâce à la méthode
[addCustomClientData](#addcustomclientdataobject-documentcheck-object-value), et supprimées grâce à la méthode
[removeCustomClientData](#removecustomclientdatastring-key).

### `browser-history (Boolean, default: false)`

Définit si le router doit suivre les changements du composant Smart Element. Désactivé par défaut. (TODO example ?)

## Événements

### documentLoaded

#### Déclenchement

L'événement est déclenché dès que le widget interne `Smart Element` est prêt à être utilisé. Il n'est pas déclenché
lorsque l'`initid` du `Smart Element` change. Dans ce cas, c'est l'événement `ready` qui est utilisé.

#### Paramètres passés au callback

Aucun.

### Évènements du widget interne

Les événements du [contrôleur de Smart Element](/ui/controller/) sont propagés au niveau du composant. Les événements
disponibles sont :

- [actionClick](/ui/controller/local-controller/#actionclick)
- [afterDelete](/ui/controller/local-controller/#afterdelete)
- [afterDisplayTransition](/ui/controller/local-controller/#afterdisplaytransition)
- [afterRestore](/ui/controller/local-controller/#afterrestore)
- [afterSave](/ui/controller/local-controller/#aftersave)
- [beforeClose](/ui/controller/local-controller/#beforeclose)
- [beforeDelete](/ui/controller/local-controller/#beforedelete)
- [beforeDisplayTransition](/ui/controller/local-controller/#beforedisplaytransition)
- [beforeRestore](/ui/controller/local-controller/#beforerestore)
- [beforeSave](/ui/controller/local-controller/#beforesave)
- [beforeTransition](/ui/controller/local-controller/#beforetransition)
- [beforeTransitionClose](/ui/controller/local-controller/#beforetransitionclose)
- [close](/ui/controller/local-controller/#close)
- [displayError](/ui/controller/local-controller/#displayerror)
- [displayMessage](/ui/controller/local-controller/#displaymessage)
- [failTransition](/ui/controller/local-controller/#failtransition)
- [ready](/ui/controller/local-controller/#ready)
- [smartFieldAnchorClick](/ui/controller/local-controller/#smartfieldanchorclick)
- [smartFieldAfterTabSelect](/ui/controller/local-controller/#smartfieldaftertabselect)
- [smartFieldArrayChange](/ui/controller/local-controller/#smartfieldarraychange)
- [smartFieldBeforeRender](/ui/controller/local-controller/#smartfieldbeforerender)
- [smartFieldBeforeTabSelect](/ui/controller/local-controller/#smartfieldbeforetabselect)
- [smartFieldChange](/ui/controller/local-controller/#smartfieldchange)
- [smartFieldCreateDialogSmartElementBeforeClose](/ui/controller/local-controller/#smartfieldcreatedialogsmartelementbeforeclose)
- [smartFieldCreateDialogSmartElementBeforeDestroy](/ui/controller/local-controller/#smartfieldcreatedialogsmartelementbeforedestroy)
- [smartFieldCreateDialogSmartElementBeforeSetFormValues](/ui/controller/local-controller/#smartfieldcreatedialogsmartelementbeforesetformvalues)
- [smartFieldCreateDialogSmartElementBeforeSetTargetValues](/ui/controller/local-controller/#smartfieldcreatedialogsmartelementbeforesettargetvalues)
- [smartFieldCreateDialogSmartElementReady](/ui/controller/local-controller/#smartfieldcreatedialogsmartelementready)
- [smartFieldDownloadFile](/ui/controller/local-controller/#smartfielddownloadfile)
- [smartFieldHelperResponse](/ui/controller/local-controller/#smartfieldhelperresponse)
- [smartFieldHelperSearch](/ui/controller/local-controller/#smartfieldhelpersearch)
- [smartFieldHelperSelect](/ui/controller/local-controller/#smartfieldhelperselect)
- [smartFieldReady](/ui/controller/local-controller/#smartfieldready)
- [smartFieldTabChange](/ui/controller/local-controller/#smartfieldtabchange)
- [smartFieldUploadFile](/ui/controller/local-controller/#smartfielduploadfile)
- [smartFieldUploadFileDone](/ui/controller/local-controller/#smartfielduploadfiledone)
- [successTransition](/ui/controller/local-controller/#successtransition)

Contrairement aux événements internes, ces événements n'ont pas de conditions préalables. Ils sont déclenchés si le
composant a déclaré le binding dans le template pour n'importe quel `Smart Element`. Les arguments reçus par le callback
tel que définit pour les événements internes, sont ici contenus dans la propriété `detail` de l'événement reçu.

#### Binding de l'événement `ready`

```vue
<template>
  <div>
    <ank-smart-element @smartElementMounted="smartElementMounted" ref="smartElement" />
  </div>
</template>
<script>
import AnkSmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement.esm";
import "@anakeen/user-interfaces/components/scss/AnkSmartElement.scss";

export default {
  components: {
    AnkSmartElement: () => AnkSmartElement
  },
  smartElementMounted() {
    const se = this.$refs.smartElement;
    se.$on("ready", function(event) {
      console.log("Smart Element ready");
    });
  }
};
</script>
```

## Slots

### `loading`

Le slot nommé `loading` permet de personnaliser l'affichage du chargement en cours. L'affichage du chargement est
automatiquement retiré lorsque l'évènement `ready` du `Smart Element` a été déclenché.  
Par défaut, le chargement est représenté par le logo `Anakeen` ainsi qu'une barre de chargement. Le contenu du slot est
rendu dans la balise encadrant le Smart Element, ainsi l'affichage du chargement occupe l'ensemble de l'espace pris par
le Smart Element.

#### Exemple

Affichage d'un texte centré

```vue
<template>
  <div class="custom-smart-element-wrapper">
    <ank-smart-element class="custom-smart-element">
      <template v-slot:loading>
        <div class="custom-loading">
          <h1>Chargement en cours...</h1>
        </div>
      </template>
    </ank-smart-element>
  </div>
</template>
<style scoped>
.custom-smart-element-wrapper {
  height: 500px;
}
.custom-smart-element > .smart-element-custom-loading > .custom-loading {
  display: flex;
  height: 100%;
  align-items: center;
  justify-content: center;
}
</style>
```

## Méthodes

A l'instar des événements, l'ensemble des méthodes du contrôleur sont accessibles depuis le composant :

- [addConstraint(options, callback)](/ui/controller/local-controller/#addcontsraint)
- [addCustomClientData(smartElementCheck, value)](/ui/controller/local-controller/#addcustomclientdata)
- [addEventListener(eventType, options, callback)](/ui/controller/local-controller/#addeventlistener)
- [appendArrayRow(smartFieldId, values)](/ui/controller/local-controller/#apprendarrayrow)
- [changeStateSmartElement(parameters, reinitOptions, options)](/ui/controller/local-controller/#changestatesmartelement)
- [deleteSmartElement(options)](/ui/controller/local-controller/#deletesmartelement)
- [fetchSmartElement(value, options)](/ui/controller/local-controller/#fetchsmartelement)
- [getCustomClientData(deleteOnce)](/ui/controller/local-controller/#getcustomclientdata)
- [getCustomServerData()](/ui/controller/local-controller/#getcustomserverdata)
- [getMenu(menuId)](/ui/controller/local-controller/#getmenu)
- [getMenus()](/ui/controller/local-controller/#getmenus)
- [getProperties()](/ui/controller/local-controller/#getproperties)
- [getProperty(property)](/ui/controller/local-controller/#getproperty)
- [getSmartField(smartFieldId)](/ui/controller/local-controller/#getsmartfield)
- [getSmartFields()](/ui/controller/local-controller/#getsmartfields)
- [getValue(smartFieldId, type)](/ui/controller/local-controller/#getvalue)
- [getValues()](/ui/controller/local-controller/#getvalues)
- [hasSmartField(smartFieldId)](/ui/controller/local-controller/#hassmartfield)
- [hasMenu(menuId)](/ui/controller/local-controller/#hasmenu)
- [hideSmartField(smartFieldId)](/ui/controller/local-controller/#hidesmartelement)
- [injectCSS(cssToInject)](/ui/controller/local-controller/#injectcss)
- [injectJS(jsToInject)](/ui/controller/local-controller/#injectjs)
- [insertBeforeArrayRow(smartFieldId, values, index)](/ui/controller/local-controller/#insertbeforearrayrow)
- [isLoaded()](/ui/controller/local-controller/#isloaded)
- [isModified()](/ui/controller/local-controller/#ismodified)
- [listConstraints()](/ui/controller/local-controller/#listcontraints)
- [listEventListeners()](/ui/controller/local-controller/#listeventlisteners)
- [maskSmartElement(message, px)](/ui/controller/local-controller/#masksmartelement)
- [reinitSmartElement(values, options)](/ui/controller/local-controller/#reinitsmartelement)
- [removeArrayRow(smartFieldId, index)](/ui/controller/local-controller/#removearrayrow)
- [removeConstraint(constraintName, allKind)](/ui/controller/local-controller/#removeconstraint)
- [removeCustomClientData(key)](/ui/controller/local-controller/#removecustomclientdata)
- [removeEventListener(eventName, allKind)](/ui/controller/local-controller/#removeeventlistener)
- [restoreSmartElement(options)](/ui/controller/local-controller/#restoresmartelement)
- [saveSmartElement(options)](/ui/controller/local-controller/#savesmartelement)
- [setValue(smartFieldId, newValue)](/ui/controller/local-controller/#setvalue)
- [showSmartField(smartFieldId)](/ui/controller/local-controller/#showsmartfield)
- [showMessage(message)](/ui/controller/local-controller/#showmessage)
- [triggerEvent(eventName)](/ui/controller/local-controller/#triggerevent)
- [tryToDestroy()](/ui/controller/local-controller/#trytodestroy)
- [unmaskSmartElement(force)](/ui/controller/local-controller/#unmasksmartelement)

L'ensemble de ces méthodes est documenté dans la
[section concernant le contrôleur de Smart Element](/ui/controller/methods/)

## Exemples

### Ajouter une bordure et la propriété `title` pour tous les Smart Fields

```vue
<template>
  <div>
    <ank-smart-element
      ref="smartElement"
      style="width: 100vw; height: 100vh;"
      :initid="'USER_ADMIN'"
      @smartElementMounted="smartElementMounted"
    />
  </div>
</template>
<script>
import AnkSmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement.esm";
import "@anakeen/user-interfaces/components/scss/AnkSmartElement.scss";

export default {
  components: {
    AnkSmartElement: () => AnkSmartElement
  },
  methods: {
    smartElementMounted: function() {
      const se = this.$refs.smartElement;
      se.$on("smartFieldReady", function(event, smartElement, smartField, $el) {
        $el.prop("title", smartField.id).css("border", "1px solid black");
      });
    }
  }
};
</script>
```

### Afficher un message à chaque modification de valeur de Smart Field

```vue
<template>
  <div>
    <ank-smart-element
      ref="smartElement"
      style="width: 100vw; height: 100vh;"
      :initid="'USER_ADMIN'"
      @smartElementMounted="smartElementMounted"
    />
  </div>
</template>
<script>
import AnkSmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement.esm";
import "@anakeen/user-interfaces/components/scss/AnkSmartElement.scss";

export default {
  components: {
    AnkSmartElement: () => AnkSmartElement
  },
  methods: {
    smartElementMounted: function() {
      const se = this.$refs.smartElement;
      se.$on("smartFieldChange", function(event, smartElement, smartField) {
        se.showMessage("Smart Field " + smartField.id + " has changed");
      });
    }
  }
};
</script>
```
