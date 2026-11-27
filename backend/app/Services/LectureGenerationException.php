<?php

namespace App\Services;

/** Only messages explicitly intended for the admin UI belong in this exception. */
class LectureGenerationException extends \RuntimeException {}
