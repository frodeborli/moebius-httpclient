<?php
namespace Moebius\Http;

use Moebius\Socket\ConnectionOptions;

class ClientOptions extends ConnectionOptions {

    public int $maxRedirs = 20;

}
