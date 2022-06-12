<?php

declare(strict_types=1);

namespace  Api\Core\Helper;

class RouterHelper
{

    /*
     * Loads Class if file exist
     * @return (object) instance of the class
     */

    public static function getClass(string $namespace, string $class): ?object
    {

        $fullclass_namespace = $namespace . $class;
        $fullclass_path      = $namespace . $class . '.php';

        $fullclass_path      = str_replace('\\', '/', $fullclass_path);
        $fullclass_namespace = str_replace('/', '\\', $fullclass_namespace);
        $fullclass_namespace = (str_starts_with($fullclass_namespace, '\\')) ? $fullclass_namespace : '\\' . $fullclass_namespace;

        //check if class exists
        if (class_exists($fullclass_namespace)) {
            return new $fullclass_namespace;
        } else {
            // check if file exists
            if (file_exists($fullclass_path)) {
                // if can be required
                if (require_once $fullclass_path) {

                    // return new instance
                    return new $fullclass_namespace;
                } else {
                    return null;
                }
            } else {
                return null;
            }
        }
    }
}
