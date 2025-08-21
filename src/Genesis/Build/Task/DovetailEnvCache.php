<?php

/**
 * @package Dovetail
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Genesis\Build\Task;

use DecodeLabs\Dovetail\Env;
use DecodeLabs\Iota;
use DecodeLabs\Terminus\Session;

class DovetailEnvCache implements PostActivation
{
    public int $priority {
        get => 100;
    }

    public string $description {
        get => 'Caching environment variables';
    }

    public function __construct(
        protected Iota $iota
    ) {
    }

    public function run(
        Session $session
    ): void {
        $data = Env::rebuildCache($this->iota);

        $session->{'cyan'}('Found ');
        $session->{'brightYellow'}((string)count($data));
        $session->{'.cyan'}(' environment variables');
    }
}
