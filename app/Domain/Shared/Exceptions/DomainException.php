<?php

declare(strict_types=1);

namespace App\Domain\Shared\Exceptions;

/**
 * Base class for every exception raised by domain rules.
 *
 * Extending the SPL DomainException lets callers that only know about
 * standard PHP exceptions still catch these meaningfully.
 */
class DomainException extends \DomainException {}
