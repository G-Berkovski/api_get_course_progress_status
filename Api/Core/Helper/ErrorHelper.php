<?php

declare(strict_types=1);

namespace  Api\Core\Helper;

class ErrorHelper
{

    /**
     * Throws exception
     *
     * @param int $errno: Error number
     * @param string $errstr: Error message
     * @param string $errfile: File where Error occured
     * @param int $errline: File line in the error
     * 
     * @return void
     */

    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): void
    {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    /**
     * Returns error response
     *
     * @param Throwable $exception
     * 
     * @return void
     */

    public static function handleException(\Throwable $exception): void
    {
        http_response_code(500);

        echo json_encode([
            "code" => $exception->getCode(),
            "message" => $exception->getMessage(),
            "file" => $exception->getFile(),
            "line" => $exception->getLine()
        ]);
        exit;
    }

    /**
     * Validates input
     *
     * @param string $varName: Variable name
     * @param mixed $input: Variable value
     * @param string $validator: Validator string type
     * 
     * @return string|null: Returns error message or null
     */

    public static function validateInput(string $varName, mixed $input, string $validator): string|null
    {
        if ($validator === 'int') {
            if (filter_var($input, FILTER_VALIDATE_INT) === false) {
                return 'Invalid input for ' . $varName . '!';
            }
        }

        if ($validator === 'date_RFC3339') {
            $date = \DateTime::createFromFormat(\DateTime::RFC3339, $input);
            if (!$date) {
                return 'Invalid input for ' . $varName . '!';
            }
        }

        return null;
    }
}
