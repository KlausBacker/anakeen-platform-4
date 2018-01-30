<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\HttpApi\V1\Crud;

class Response
{


    /**
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param  Callable           $crudCallable
     *
     * @return \Slim\Http\Response
     */
    public static function withCrud(\Slim\Http\request $request, \Slim\Http\response $response, $crudCallable)
    {
        /**
         * @var \Dcp\HttpApi\V1\Crud\Crud $crudObject
         */
        $crudObject = null;
        $data = [];

        try {
            $returnData = $crudCallable($crudObject);
            $data = [
                "success" => true,
                "data" => $returnData,
                "messages" => $crudObject->getMessages()
            ];
        } catch (\Dcp\HttpApi\V1\Crud\Exception $exception) {
            $exceptionMsg = \Dcp\Core\LogException::logMessage($exception, $errId);
            $response = $response->withStatus($exception->getHttpStatus(), $exception->getHttpMessage());

            $message = new \Dcp\HttpApi\V1\Api\RecordReturnMessage();
            $message->contentText = $exception->getUserMessage();
            if (!$message->contentText) {
                $message->contentText = $exception->getDcpMessage();
            }
            $message->type = $message::ERROR;
            $message->code = $exception->getDcpCode();
            $message->data = $exception->getData();
            $message->uri = $exception->getURI();
            foreach ($exception->getHeaders() as $hName => $header) {
                $response = $response->withHeader($hName, $header);
            }

            $data = [
                "success" => false,
                "data" => $exception->getData(),
                "exceptionMessage" => $exceptionMsg,
                "messages" => [$message]
            ];
        } catch (\Dcp\HttpApi\V1\Api\Exception $exception) {
            $exceptionMsg = \Dcp\Core\LogException::logMessage($exception, $errId);
            $response = $response->withStatus($exception->getHttpStatus(), $exception->getHttpMessage());
            $message = new \Dcp\HttpApi\V1\Api\RecordReturnMessage();
            $message->contentText = $exception->getUserMessage();
            if (!$message->contentText) {
                $message->contentText = $exception->getDcpMessage();
            }
            $message->type = $message::ERROR;
            $message->code = $exception->getDcpCode();
            $message->data = $exception->getData();
            $message->uri = $exception->getURI();
            foreach ($exception->getHeaders() as $hName => $header) {
                $response = $response->withHeader($hName, $header);
            }

            $data = [
                "success" => false,
                "data" => $exception->getData(),
                "exceptionMessage" => $exceptionMsg,
                "messages" => [$message]
            ];
        } catch (\Dcp\Exception $exception) {
            $exceptionMsg = \Dcp\Core\LogException::logMessage($exception, $errId);

            $response = $response->withStatus(400, "Anakeen Exception");

            $message = new \Dcp\HttpApi\V1\Api\RecordReturnMessage();
            $message->contentText = sprintf("[%s] %s", $errId, $exceptionMsg);
            $message->type = $message::ERROR;
            $message->code = $exception->getDcpCode();

            $data = [
                "success" => false,
                "data" => null,
                "exceptionMessage" => $exceptionMsg,
                "messages" => [$message]
            ];
        } catch (\Exception $exception) {
            $exceptionMsg = \Dcp\Core\LogException::logMessage($exception, $errId);
            $response = $response->withStatus(400, "Exception");

            $message = new \Dcp\HttpApi\V1\Api\RecordReturnMessage();
            $message->contentText = \Dcp\Core\LogException::getMessage($exception, $errId);
            $message->type = $message::ERROR;
            $message->code = "API0001";

            $data = [
                "success" => false,
                "data" => null,
                "exceptionMessage" => $exceptionMsg,
                "messages" => [$message]
            ];
        }

        return $response->withJson($data);
    }

    public static function initRequest(\Dcp\HttpApi\V1\Crud\Crud $crudObject)
    {

        $crudObject->setContentParameters(
            \Dcp\HttpApi\V1\Api\Router::extractContentParameters(
                \Dcp\HttpApi\V1\Api\Router::convertActionToCrud(),
                $crudObject
            )
        );
    }
}

