<?php


namespace Anakeen\Router;


use Anakeen\Core\DbManager;
use Anakeen\Core\SmartStructure\ExportConfiguration;
use Anakeen\Router\Config\RouterInfo;

class ExportRoutesConfiguration
{

    const NS = "sde";
    const NSURL = ExportConfiguration::NSBASEURL . "sde/1.0";
    protected $dom;
    protected $domConfig;

    public function __construct()
    {
        $this->dom = new \DOMDocument("1.0", "UTF-8");
        $this->dom->formatOutput = true;
        $this->domConfig = $this->cel("config");
        $this->dom->appendChild($this->domConfig);
    }

    /**
     * Initialise xml config for some routes
     * @param RouterInfo[] $routes routes to export
     * @return void
     */
    public function extractRoutes(array $routes)
    {
        $routesNsNodes=[];
        foreach ($routes as $route) {
            $info = $this->cel("route");
            if (preg_match("/(.*)::(.*)/", $route->name, $reg)) {
                $ns=$reg[1];
                if (!isset($routesNsNodes[$ns])) {
                    $routesNsNodes[$ns]= $this->cel("routes");
                    $routesNsNodes[$ns]->setAttribute("namespace", $reg[1]);
                    $this->domConfig->appendChild($routesNsNodes[$ns]);
                }
                $routesNsNodes[$ns]->appendChild($info);
                $info->setAttribute("name", $reg[2]);


                $node = $this->cel("callable");
                $node->nodeValue = $route->callable;
                $info->appendChild($node);


                foreach ($route->methods as $method) {
                    $node = $this->cel("method");
                    $node->nodeValue = $method;
                    $info->appendChild($node);
                }

                if (!is_array($route->pattern)) {
                    $route->pattern = [$route->pattern];
                }
                foreach ($route->pattern as $pattern) {
                    $node = $this->cel("pattern");
                    $node->nodeValue = $pattern;
                    $info->appendChild($node);
                }


                $node = $this->cel("description");
                $node->nodeValue = $route->description;
                $info->appendChild($node);

                if (!$route->authenticated) {
                    $node = $this->cel("authenticated");
                    $node->nodeValue = "false";
                    $info->appendChild($node);
                }
                if ($route->override) {
                    $info->setAttribute("override", $route->override);
                }
                if ($route->requiredAccess) {
                    $node = $this->cel("requiredAccess");
                    $accesses=[];
                    if (!empty($route->requiredAccess->and)) {
                        $node->setAttribute("operator", "and");
                        $accesses=$route->requiredAccess->and;
                    } elseif (!empty($route->requiredAccess->or)) {
                        $node->setAttribute("operator", "or");
                        $accesses=$route->requiredAccess->or;
                    }
                    foreach ($accesses as $access) {
                        $aNode = $this->cel("access");
                        if (preg_match("/(.*)::(.*)/", $access, $reg)) {
                            $aNode->setAttribute("ns", $reg[1]);
                            $aNode->nodeValue=$reg[2];
                            $node->appendChild($aNode);
                        }
                    }
                    $info->appendChild($node);
                }
            }
        }

    }


    public function extractRouteAcl($vendor) {


        $sql=sprintf("select * from acl where name ~* '^%s::'", $vendor);
        DbManager::query($sql, $acls);
        /**    <sde:accesses namespace="CCFD">
        <sde:access name="CCFD::ONEFAM_MASTER">
            <sde:description>Access choose masters families</sde:description>
        </sde:access>
         *
         */
        $accessesNodes=[];
        foreach ($acls as $acl) {

            if (preg_match("/(.*)::(.*)/", $acl["name"], $reg)) {
                $accessNode=$this->cel("access");
                $ns=$reg[1];
                if (!isset($accessesNodes[$ns])) {
                    $accessesNodes[$ns] = $this->cel("accesses");
                    $accessesNodes[$ns]->setAttribute("namespace", $ns);
                    $this->domConfig->appendChild($accessesNodes[$ns]);
                }
                $accessNode->setAttribute("name", $reg[2]);
                $accessesNodes[$ns]->appendChild($accessNode);
                $descNode=$this->cel("description");
                $descNode->nodeValue = $acl["description"];
                $accessNode->appendChild($descNode);
            }
        }

    }

    /**
     *
     * @return string xml content of route list
     * @return string
     */
    public function toXml() {
        return $this->dom->saveXML();

    }

    protected function cel($name)
    {
        return $this->dom->createElementNS(self::NSURL, self::NS . ":" . $name);
    }
}