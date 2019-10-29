<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use \Anakeen\Routes\Ui\DocumentView;
use Anakeen\Core\SEManager;
use Anakeen\SmartElement;

class ViewAccess
{
    const defaultViews = [
        DocumentView::coreViewCreationId,
        DocumentView::defaultViewConsultationId,
        DocumentView::defaultViewEditionId,
        DocumentView::defaultViewCreationId,
        DocumentView::coreViewConsultationId,
        DocumentView::coreViewEditionId
    ];

    /** @var SmartElement */
    protected $smartElement;
    /** @var SmartElement */
    protected $viewController;
    protected $viewId;

    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\Response
     */
    public function __invoke(
        \Slim\Http\request $request,
        \Slim\Http\response $response,
        $args
    ) {

        $this->initParameters($request, $args);

        $this->checkViewAccess();

        return ApiV2Response::withData($response, $this->getSmartElementdata());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $seId = $args['seId'] ?? null;
        if (empty($seId)) {
            $exception = new Exception("ANKTEST004", 'seId');
            $exception->setHttpStatus("400", "smart element identifier is required");
            throw $exception;
        }
        $this->smartElement = SEManager::getDocument($seId);
        if (empty($this->smartElement)) {
            $exception = new Exception("ANKTEST001", $seId);
            $exception->setHttpStatus("404", "Cannot get Smart Element");
            throw $exception;
        }

        $this->viewId = $args['viewId'] ?? null;

        if (!in_array($this->viewId, self::defaultViews)) {
            $cvid = $this->smartElement->cvid;
            if (empty($cvid)) {
                $msg = sprintf("SE %s has no cvid, thus view %s does not exists", $seId, $this->viewId);
                $exception = new Exception($msg);
                $exception->setHttpStatus("404", $msg);
                throw $exception;
            }
            $this->viewController = SEManager::getDocument($cvid);
            if (empty($this->viewController)) {
                $exception = new Exception("ANKTEST001", $cvid);
                $exception->setHttpStatus("500", sprintf("Referenced view controller (%s) for %s does not exists"), $cvid, $seId);
                throw $exception;
            }
            $this->viewController->set($this->smartElement);
        }
    }

    protected function checkViewAccess()
    {
        if (in_array($this->viewId, self::defaultViews)) {
            //FIXME: check default view access
        } else {
            $currentUser = \Anakeen\Core\ContextManager::getCurrentUser();
            $err = $this->viewController->control($this->viewId);
            if (!empty($err)) {
                $exception = new Exception("ANKTEST009", $this->viewId, $err);
                $exception->setHttpStatus("403", "Access forbidden");
                throw $exception;
            }
        }
    }

    protected function getSmartElementdata()
    {
        $smartElementData = new \Anakeen\Routes\Core\Lib\DocumentApiData($this->smartElement);
        $smartElementData->setFields(["document.properties.all", "document.attributes.all"]);
        return $smartElementData->getDocumentData();
    }
}
