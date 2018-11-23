<?php

namespace Anakeen\Routes\Devel\UI;

use Anakeen\Core\ContextManager;
use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\SmartStructures\Wdoc\WDocHooks;
use Anakeen\Workflow\DotGraph;

/**
 * Get Workflow graph image
 *
 * @note Used by route : GET /api/v2/devel/ui/workflows/image/{workflow}[.{extension:png|svg|dot}]
 */
class WorkflowGraph
{

    protected $extension = "svg";
    protected $wid = null;
    protected $size;
    protected $inline = 1;
    protected $type = "simple";  //"complet","activity","justactivity","simple","cluster"
    protected $ratio = "auto"; // "auto", "fill", "compress", "expand"
    protected $orient = "LR"; //"LR","TB","BT","RL"
    protected $isize = 50;
    protected $useLabel=true;

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
        $inlineQuery = $request->getQueryParam("inline", null);
        if ($inlineQuery) {
            $this->inline = ($inlineQuery === "yes" || $inlineQuery === "true" || $inlineQuery === "1");
        }

        $this->orient = $request->getQueryParam("orientation", "LR");
        $this->useLabel = $request->getQueryParam("useLabel", "true") !== "false";
    }

    protected function doRequest(\Slim\Http\request $request, \Slim\Http\response $response)
    {
        /** @var WDocHooks $workflow */
        $workflow = SEManager::getDocument($this->wid);
        if (!$workflow) {
            throw new Exception(sprintf("Workflow \"%s\" not found", $this->wid));
        }
        if (!is_a($workflow, WDocHooks::class)) {
            throw new Exception(sprintf("Element \"%s\" is not a workflow", $this->wid));
        }
        $fileName = sprintf("%s.%s", "WorkflowSample", $this->extension);

        $tmpFile = sprintf("%s/%s.%s", ContextManager::getTmpDir(), uniqid("wfl"), $this->extension);
        $dot = $this->dotGraph($workflow);

        $mime = "";
        if ($this->extension) {
            switch ($this->extension) {
                case "dot":
                    $mime = "text/plain";
                    file_put_contents($tmpFile, $dot);
                    break;
                case "png":
                    $mime = "image/png";
                    $this->dot2File($dot, "png", $tmpFile);
                    break;
                case "svg":
                    $mime = "image/svg+xml";
                    $this->dot2File($dot, "svg", $tmpFile);
                    break;
                default:
                    $mime = "image/" . $this->extension;
            }
        }
        return ApiV2Response::withFile($response, $tmpFile, $fileName, $this->inline, $mime);
    }

    protected function dot2File($dot, $format, $outfileName)
    {
        $tmpFile = sprintf("%s/%s.dot", ContextManager::getTmpDir(), uniqid("wfl"));
        file_put_contents($tmpFile, $dot);
        $cmd = sprintf("dot -T%s -o%s %s 2>&1", escapeshellarg($format), escapeshellarg($outfileName), escapeshellarg($tmpFile));
        exec($cmd, $out, $ret);


        if ($ret !== 0) {
            throw new Exception(implode("\n", $out));
        }
    }

    protected function dotGraph(WDocHooks $workflow)
    {
        $dw = new DotGraph();
        $dw->useLabel($this->useLabel);
        $dw->setOrient($this->orient);
        $dw->setRatio($this->ratio);
        $dw->setSize($this->isize);
        $dw->setType($this->type);
        $dw->setWorkflow($workflow);
        $dot = $dw->generate();
        return $dot;
    }
}
