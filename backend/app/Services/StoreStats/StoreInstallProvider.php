<?php

namespace App\Services\StoreStats;

/** A store listing whose public install count can be read for the admin dashboard. */
interface StoreInstallProvider
{
    /** Short stable id used in the database and the JSON feed, e.g. "bazaar". */
    public function key(): string;

    /** Name shown in the dashboard. */
    public function label(): string;

    /** The current public install count, or null when it could not be read. */
    public function installs(): ?int;

    /** Public listing metrics; missing values stay null, never zero. */
    public function metrics(): array;
}
