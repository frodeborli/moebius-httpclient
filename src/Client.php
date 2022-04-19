<?php
namespace Moebius\Http;

use function M\{run};
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\{
    RequestInterface,
    ResponseInterface
};
use Charm\Util\Uri;
use Moebius\Socket\Client as SocketClient;

class Client implements ClientInterface {

    public readonly ClientOptions $options;

    private ?SocketClient $socket = null;
    private ?Uri $uri = null;

    public function __construct(array|ClientOptions $options=[]) {
        $this->options = ClientOptions::create($options);
    }

    public function sendRequest(RequestInterface $request): ResponseInterface {
        return run(function() use ($request) {
            if ($this->uri !== null) {
                $uri = $this->uri->navigateTo($request->getUri());
            } else {
                $uri = $request->getUri();
            }

            $tcpAddress = 'tcp://'.$uri->getHost();
            $port = $uri->getPort();
            switch ($uri->getScheme()) {
                case 'ws':
                case 'http':
                    $tcpAddress .= ':'.($uri->getPort() ?? 80);
                    break;
                case 'wss':
                case 'https':
                    $tcpAddress .= ':'.($uri->getPort() ?? 443);
                    break;
                default:
                    throw new RequestError("Unsupported scheme '".$uri->getScheme()."'");
            }

            if ($this->socket?->address !== $tcpAddress) {
                if ($this->socket?->isConnected()) {
                    $this->socket->close();
                }
                $this->socket = new SocketClient($tcpAddress);
            }

            $requestChunk = $request->getMethod().' '.$request->getRequestTarget().' HTTP/'.$request->getProtocolVersion()."\r\n";
            foreach ($request->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    $requestChunk .= sprintf("%s: %s\r\n", $name, $value);
                }
            }
            $requestChunk .= "\r\n";
            echo $requestChunk;

            $this->socket->write($requestChunk);

            while (!$this->socket->eof()) {
                echo $this->socket->read(1024);
            }
            echo "---\n";


        });
    }

    private static function getEffectiveUrl(RequestInterface $request): array {
            $target = $request->getRequestTarget();

            var_dump($target);
            die();


            $uri = $request->getUri();

            switch ($scheme = $uri->getScheme()) {
                case 'http' :
                case 'https' :
                    break;
                case '' :
                    throw new ClientRequestError("Missing scheme");
                default :
                    throw new ClientRequestError("Unsupported scheme '$scheme'");
            }

        


            if (!$request->hasHeader('Host')) {
                throw new RequestError("Outbound HTTP requests require a host");
            }
            $hostHeaders = $request->getHeader('host');

            echo json_encode([
                'protocol' => $request->getProtocolVersion(),
                'method' => $request->getMethod(),
                'target' => $request->getRequestTarget(),
                'uri' => $request->getUri(),
                'headers' => $request->getHeaders(),
                'body' => $request->getBody(),
            ], JSON_PRETTY_PRINT);

    }

    private function connectTo(string $address): void {
        if ($this->socket !== null) {
            throw new LogicError("Already connected to ".$this->address);
        }
        $this->socket = new SocketClient($address, $this->options);
        $this->address = $address;
    }

    private function disconnect(): void {
        if (!$this->socket) {
            throw new LogicError("Not connected");
        }
        $this->socket->close();
        $this->socket = null;
        $this->address = null;
    }

}
