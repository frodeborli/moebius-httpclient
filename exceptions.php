<?php
namespace Moebius\Http;

use Psr\Http\Client\{
    ClientExceptionInterface,
    RequestExceptionInterface,
    NetworkExceptionInterface
};

/**
 * Base exception class for moebius/http
 */
class HttpException extends \Exception {}

/**
 * HTTP client related exceptions
 */
class ClientException extends HttpException implements ClientExceptionInterface {}

/**
 * HTTP request related errors (preventable)
 */
class RequestError extends HttpException implements RequestExceptionInterface {}

/**
 * HTTP request related exceptions (runtime)
 */
class RequestException extends HttpException implements RequestExceptionInterface {}

/**
 * HTTP network related exceptions
 */
class NetworkException extends HttpException implements RequestExceptionInterface {}
