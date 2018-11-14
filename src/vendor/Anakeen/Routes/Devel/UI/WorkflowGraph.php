<?php
namespace Anakeen\Routes\Devel\UI;
use Anakeen\Core\ContextManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Routes\Core\Lib\Files;

/**
 * Get Workflow graph image
 *
 * @note Used by route : GET /api/v2/devel/ui/workflows/image/{workflow}/sizes/{size:[0-9x]+[cfs]?}[.{extension:png|jpeg|jpg|svg}]
 */
class WorkflowGraph {

    protected $extension = "svg";
    protected $wid = null;
    protected $size;
    protected $inline = false;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return $this->doRequest($request, $response);
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->wid = $args["workflow"];
        $this->size = $args["size"];
        $this->extension = $args["extension"];
        $inlineQuery = $request->getQueryParam("inline");
        if ($inlineQuery) {
            $this->inline = ($inlineQuery === "yes" || $inlineQuery === "true" || $inlineQuery === "1");
        }
    }

    protected function doRequest(\Slim\Http\request $request, \Slim\Http\response $response)
    {
        $sourceFile = __DIR__."/TestGraph/WorkflowSimple.svg";

//        $outFile = Files::resizeLocalImage($sourceFile, ContextManager::getTmpDir()."/WorkflowSample", $this->size);
        $outFile = $sourceFile;

        $fileName = sprintf("%s-%s.%s", $this->size, "WorkflowSample", $this->extension);

        $mime = "";
        if ($this->extension) {
            switch ($this->extension) {
                case "jpg":
                    $mime = "image/jpeg";
                    break;

                default:
                    $mime = "image/" . $this->extension;
            }
        }
        return ApiV2Response::withFile($response, $outFile, $fileName, $this->inline, $mime);
    }
}