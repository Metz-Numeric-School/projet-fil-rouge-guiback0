<?php

namespace App\Exception;

class PresenceException extends \Exception
{
    public const INVALID_DATE_FORMAT = 'INVALID_DATE_FORMAT';
    public const INVALID_DATE_RANGE = 'INVALID_DATE_RANGE';
    public const CALCULATION_ERROR = 'CALCULATION_ERROR';

    private string $errorCode;

    public function __construct(string $errorCode, string $message = '', \Throwable $previous = null)
    {
        $this->errorCode = $errorCode;
        parent::__construct($message ?: $this->getDefaultMessage($errorCode), 0, $previous);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    private function getDefaultMessage(string $errorCode): string
    {
        return match ($errorCode) {
            self::INVALID_DATE_FORMAT => 'Format de date invalide',
            self::INVALID_DATE_RANGE => 'Période de dates invalide',
            self::CALCULATION_ERROR => 'Erreur lors du calcul des présences',
            default => 'Erreur inconnue'
        };
    }

    public function toArray(): array
    {
        return [
            'success' => false,
            'error' => $this->errorCode,
            'message' => $this->getMessage()
        ];
    }
}