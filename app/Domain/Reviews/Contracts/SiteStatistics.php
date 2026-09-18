<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Contracts;

/**
 * Site-wide figures an aggregator may use as a prior.
 */
interface SiteStatistics
{
    /**
     * The mean overall score across every published review, 0–5.
     */
    public function meanOverallScore(): float;
}
