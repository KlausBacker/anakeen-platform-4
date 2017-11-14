# Deprecated function from Dynacase 3.2

## getDbAccess

Alternative `Dcp\Core\DbManager::getDbAccess()`

## getDbid

Alternative `Dcp\Core\DbManager::getDbId()`

**Changement**: il n'est plus possible d'adresser une base de données quelconque. Seule la base de donnée de Anakeen Platform est utilisée.


## getParam

Alternative `Dcp\Core\ContextManager::getApplicationParam`

## getCoreParam

Alternative `Dcp\Core\ContextManager::getCoreParam`
## getCurrentUser
Alternative `Dcp\Core\ContextManager::getCurrentUser`


## simpleQuery

Alternative `Dcp\Core\DbManager::query`

**Changement**: Le paramètre "useStrict=false" (déjà déprécié) n'est plus utilisable. Il faut faire un try/catch si on n'est pas sûr de la requête pour attraper l'erreur. 

**Changement**: Seules des requêtes sur la base Anakeen Platform peuvent être effectuées.

## WhatInitialisation

Alternative `Dcp\Core\ContextManager::initContext`.

**Changement**: Le compte utilisateur doit être initialisé avant l'initialisation du contexte.

## setSystemLogin

Alternative `Dcp\Core\ContextManager::sudo`

**Changement**: Le paramètre est un objet "Account" et non un login

## setLanguage

Alternative `Dcp\Core\ContextManager::setLanguage`

## clearCacheDoc

Alternative `Dcp\Core\DocManager::cache()->clear()`

Alternative `Dcp\Core\DocManager::cache()->removeDocumentById($id)`


## new_Doc

Alternative `Dcp\Core\DocManager::getDocument()`

**Changement**: Par défaut c'est la dernière révision qui est retournée. Si le document n'est pas trouvé, `null` est retourné.
 
**Changement**:  Le document n'est pas mis en cache.

La fonction `new_Doc` mets en cache si le nombre de document déjà mis en cache est inférieur à 20. (Pour garder, une compatibilité avec l'ancienne fonction)

## createDoc


Alternative `Dcp\Core\DocManager::createDoc()`


**Changement**: En case de problème de droit de création, une exception est levée au lieu d'un retour `false`.

## createTmpDoc

Alternative `Dcp\Core\DocManager::createTemporaryDocument()`

## getFromId

Alternative `Dcp\Core\DocManager::getFromId()`

**Changement**: Retourne `null` eu lieu de `false` si pas trouvé.

## getFromName
Alternative `Dcp\Core\DocManager::getFromName()`

**Changement**: Retourne `null` eu lieu de `false` si pas trouvé.
## getTDoc


Alternative `Dcp\Core\DocManager::getRawDocument()`


Alternative `Dcp\Core\DocManager::getRawData()`


**Changement**: Par défaut c'est la dernière révision qui est retournée. Si le document n'est pas trouvé, `null` est retourné.
La variable `uperm` (pour les droits) n'est plus retournée.


**Changement**: Plus de paramètre DbAccess. Seules des requêtes sur la base Anakeen Platform peuvent être effectuées.

## getDocObject


Alternative `Dcp\Core\DocManager::getDocumentFromRawDocument()`


## getFamIdFromName
Alternative `Dcp\Core\DocManager::getFamilyIdFromName()`

**Changement**: Plus de paramètre DbAccess. Seules des requêtes sur la base Anakeen Platform peuvent être effectuées.

## getIdFromName
Alternative `Dcp\Core\DocManager::getIdFromName()`
**Changement**: Plus de paramètre DbAccess. Seules des requêtes sur la base Anakeen Platform peuvent être effectuées.

## getInitidFromName
Alternative `Dcp\Core\DocManager::getInitIdFromName()`
**Changement**: Plus de paramètre DbAccess. Seules des requêtes sur la base Anakeen Platform peuvent être effectuées.

## getNameFromId
Alternative `Dcp\Core\DocManager::getNameFromId()`

**Changement**: Plus de paramètre DbAccess. Seules des requêtes sur la base Anakeen Platform peuvent être effectuées.

## getLatestTDoc

Alternative `Dcp\Core\DocManager::getRawDocument()`

**Changement**: Plus de paramètre DbAccess. Seules des requêtes sur la base Anakeen Platform peuvent être effectuées.

## DbObj::savePoint

Alternative `Dcp\Core\DbManager::savePoint()`

## DbObj::lockPoint

Alternative `Dcp\Core\DbManager::lockPoint()`

## DbObj::setMasterLock

Alternative `Dcp\Core\DbManager::setMasterLock()`

## DbObj::rollbackPoint

Alternative `Dcp\Core\DbManager::rollbackPoint()`

## DbObj::commitPoint

Alternative `Dcp\Core\DbManager::commitPoint()`
