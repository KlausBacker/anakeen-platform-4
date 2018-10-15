<?php

namespace Anakeen\Routes\Devel\Smart;

use Anakeen\Core\DbManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Ui\DataSource;

/**
 * Get All Structures
 *
 * @note Used by route : GET /api/v2/devel/smart/structures/vendor/
 * @note Used by route : GET /api/v2/devel/smart/structures/all/
 */
class Structures
{
    const ENUMPAGESIZE = 50;
    protected $target="all";
    protected $filters = [];
    protected $slice = self::ENUMPAGESIZE;
    protected $offset = 0;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->target = $args["target"];
    }

    public function doRequest()
    {
        $data = [];

        $s=new \SearchDoc("", -1);
        if ($this->target === "vendor") {
            $s->addFilter("atags is null or atags->>'vendor' <> 'Anakeen'");
        }
        $s->setOrder("name, id");
        $s->setObjectReturn();
        $dl=$s->search()->getDocumentList();
        $structData=[];
        foreach ($dl as $structure) {
            $structData[]=[
                "id"=>intval($structure->id),
                "name"=>$structure->name,
                "title"=>$structure->getTitle(),
                "icon"=>$structure->getIcon("", 32)
            ];
        }
        $data=$structData;
        return $data;
    }
}
