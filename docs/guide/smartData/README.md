# Configuration et développement

Pour fonctionner, le module a besoin d'être configurer à l'aide d'une SmartStructure de configuration.
Ainsi, la SmartStructure `Hub Configuration`permet d'apporter la configuration des différents éléments présentés dans le hub.

Chaque module contient une SmartStructure de configuration héritant de la SmartStructure `Hub Configuration` pour créer un élément de configuration des spécificités de l'élément d'interface qu'il apporte.

## Instanciation

L'instance d'un `hub station` se structure comme tel :
* Un `nom logique` (référence unique au hub station)
* Un `tableau de titres` aves les colonnes suivantes :
    * Un `titre` (champ texte)
    * Une `langue` (champ texte avec aide à la saisie sur les langues proposées par Anakeen-Platform 4)
    * Un `code langue` (champ texte caché contenant le code de la langue sélectionnée pour ce hub station)
* Une icone `favIcon`
* Un `tableau de rôle` pour les droits d'accès à ce hub station

## Technique de paramétrage

La SmartStructure de configuration `Hub Configuration` se structure comme tel:
* Section `configuration` :
    * Un `docid` permettant de d'identifier le hub station courant
    * Un `tableau de label` avec les colonnes suivantes :
        * Un `titre` (champ texte)
        * Une `langue` (champ texte avec aide à la saisie sur les langues proposées par Anakeen-Platform 4)
        * Un `code langue` (champ texte caché contenant le code de la langue sélectionnée pour cette entrée)
    * Une `icône` provenant de l'un des trois types suivant :
        * `Font` : sélection d'icône `Font Awesome`
        * `Image` : téléchargement d'image au format `jpeg|jpg|png`
        * `Html` : personnalisation au travers de code html
    * Un paramètre `order` qui indique dans quel ordre trier les éléments dans le hub
    * Un paramètre `position` qui indique l'emplacement de l'élément dans l'un des docks disponibles.
    * Un paramètre `activé` ayant pour valeur par défaut `oui`
    * Un paramètre `priorité` qui indique l'ordre de priorité d'activation des éléments
* Section `paramètres` :
    * Cette section référence tous les paramètres appartenant à l'élément
* Section `sécurité` :
    * Un `tableau de rôle` déterminant les droits d'accès d'utilisation de l'élément par un utilisateur.

Vous pouvez retrouver l'ensemble de la documentation sur la configuration d'une SmartStructure, cliquez sur le lien suivant: [Configuration d'une SmartStructure][docSS] 

[docSS]:#sde-ref:50dbde1d-c202-46bb-9be1-9c6ea3eca1fb