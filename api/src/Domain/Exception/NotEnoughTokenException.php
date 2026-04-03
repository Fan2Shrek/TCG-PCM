<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;

final class NotEnoughTokenException extends BadRequestException {}
