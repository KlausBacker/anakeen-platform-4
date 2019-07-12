<?php
namespace Anakeen\Routes\Admin\Workflow;
use Anakeen\Core\ContextManager;
use Anakeen\SmartElementManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\SmartStructures\Wdoc\WDocHooks;
use Anakeen\Workflow\DotGraph;

class WorkflowGraph
{
    protected $extension = "svg";
    protected $wid = null;
    protected $inline = 1;
    protected $type = "simple";  //"complet","activity","justactivity","simple","cluster"
    protected $ratio = "auto"; // "auto", "fill", "compress", "expand"
    protected $orient = "LR"; //"LR","TB","BT","RL"
    protected $isize = 50;
    protected $useLabel = true;

    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\response
     * @throws Exception
     * @throws \Anakeen\Core\DocManager\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return $this->doRequest($request, $response);
    }

    /**
     * @param \Slim\Http\request $request
     * @param $args
     */
    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->wid = $args["workflow"];
        $this->extension = $args["extension"];
        $inlineQuery = $request->getQueryParam("inline", null);
        if ($inlineQuery) {
            $this->inline = ($inlineQuery === "yes" || $inlineQuery === "true" || $inlineQuery === "1");
        }

        $this->orient = $request->getQueryParam("orientation", "LR");
        $this->useLabel = $request->getQueryParam("useLabel", "state");
    }

    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @return \Slim\Http\response
     * @throws Exception
     * @throws \Anakeen\Core\DocManager\Exception
     */
    protected function doRequest(\Slim\Http\request $request, \Slim\Http\response $response)
    {
        /** @var WDocHooks $workflow */
        $workflow = SmartElementManager::getDocument($this->wid);
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
        $response = ApiV2Response::withFile($response, $tmpFile, $fileName, $this->inline, $mime);
        unlink($tmpFile);
        return $response;
    }

    protected function dot2File($dot, $format, $outfileName)
    {
        $tmpFile = sprintf("%s/%s.dot", ContextManager::getTmpDir(), uniqid("wfl"));
        file_put_contents($tmpFile, $dot);
        $cmd = sprintf("dot -T%s -o%s %s 2>&1", escapeshellarg($format), escapeshellarg($outfileName),
            escapeshellarg($tmpFile));
        exec($cmd, $out, $ret);

        unlink($tmpFile);

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
