<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */

function generate_data(Action & $action)
{

    $u = new \Account(getDbAccess());
    $u->setLoginName("admin");
    $action->user = $u;
    $action->savePoint("documentTestImportSavePoint");
    $importDocument = new ImportDocument();
    $analyze = $importDocument->importDocuments(
        $action, "TEST_DOCUMENT/testElements/TEST_DOCUMENT_ALL__STRUCT.csv"
    );
    //var_export($analyze);
    $analyze = $importDocument->importDocuments(
        $action, "TEST_DOCUMENT/testElements/test_document.zip", false, true
    );
    //var_export($analyze);
    $analyze = $importDocument->importDocuments(
        $action, "TEST_DOCUMENT/testElements/test_document.zip", false, true
    );
    //var_export($analyze);
    $values = [];
    $view = new \Dcp\Ui\Crud\View();

    $doc = new_Doc("", "TEST_DOCUMENT_1", true);
    $view->setUrlParameters(
        array(
            "viewIdentifier" => \Dcp\Ui\Crud\View::defaultViewConsultationId
        )
    );
    $values["document_1!defaultConsultation"] = [
        "data" => $view->read(
            $doc->getPropertyValue("initid")
        )
    ];
    $view->setUrlParameters(
        array(
            "viewIdentifier" => \Dcp\Ui\Crud\View::defaultViewEditionId
        )
    );
    $values["document_1!defaultEdition"] = [
        "data" => $view->read(
            $doc->getPropertyValue("initid")
        )
    ];

    $doc = new_Doc("", "TEST_DOCUMENT_2", true);
    $view->setUrlParameters(
        array(
            "viewIdentifier" => \Dcp\Ui\Crud\View::defaultViewConsultationId
        )
    );
    $values["document_2!defaultConsultation"] = [
        "data" => $view->read(
            $doc->getPropertyValue("initid")
        )
    ];
    $view->setUrlParameters(
        array(
            "viewIdentifier" => \Dcp\Ui\Crud\View::defaultViewEditionId
        )
    );
    $values["document_2!defaultEdition"] = [
        "data" => $view->read(
            $doc->getPropertyValue("initid")
        )
    ];
    $familyStructure = new \Dcp\Ui\Crud\FamilyStructure();
    $doc = new_Doc("", "TEST_DOCUMENT_ALL__STRUCT", true);
    $values["testStructure"] = [
        "data" => $familyStructure->read(
            $doc->getPropertyValue("initid")
        )
    ];

    header('Content-Type: application/json');
    $action->lay->template = json_encode($values);
    $action->lay->noparse = true;
    $action->rollbackPoint("documentTestImportSavePoint");

}
