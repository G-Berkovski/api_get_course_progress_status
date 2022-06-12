<?php

declare(strict_types=1);

namespace Api\Core\Controller;

use Api\Core\Helper\RouterHelper as RouterHelper;

/*
 * App Core Class
 * URL FORMAT - {path to}/controller/method/params
 */

class MainController
{

    /**
     * Routs the request  to the corresponding class
     *
     * @param string currentNamespace: Namespace string to buld
     * 
     * @return void
     */

    public static function route(string $currentNamespace): void
    {

        $method = '';
        $url    = self::getUrl();

        if (!empty($url) && is_array($url)) {

            //Check for api version
            if (strlen($url[0]) == 2) {
                //set api version
                $apiVersion = htmlentities($url[0]);
                // Remove api version
                unset($url[0]);
            }
        }

        $currentNamespace = sprintf($currentNamespace, $apiVersion);

        $controller = '';
        foreach ($url as $segment) {

            if (empty($controller)) {

                $isClass = class_exists($currentNamespace . ucfirst($segment));

                if ($isClass) {
                    //set constroller
                    $controller = ucfirst($segment);
                } else {
                    $currentNamespace  .=  ucfirst($segment) . '\\';
                }
            } else {
                //set method
                $method = $segment;
                break;
            }
        }


        $controller  = RouterHelper::getClass($currentNamespace, $controller);

        if (empty($controller)) {
            http_response_code(404);
            exit;
        }

        $methodExists  = method_exists($controller, $method);

        if (!$methodExists) {
            http_response_code(404);
            exit;
        }


        try {
            //TODO fix params
            // Get params
            $params = !empty($url) ? array_values($url) : [];

            call_user_func_array(array($controller, $method), $params);
        } catch (\Exception $e) {
            http_response_code(400);
            exit;
        }
    }


    /**
     * Gets the url and returns array with the segments 
     * 
     * @return array
     */

    private static function getUrl(): array
    {
        $url = [];
        if (!empty($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
        }
        return $url;
    }
}
