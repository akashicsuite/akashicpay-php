<?php

declare(strict_types=1);

namespace Akashic\Utils;

use Akashic\Constants\OtherErrorCode;
use Akashic\L1Network;
use Exception;

use function fmod;
use function strval;

class Currency
{
    /**
     * Method for safe conversion from coin/token decimals.
     *
     * @param  string      $coinSymbol  (which should be a NetworkSymbol)
     * @param  string|null $tokenSymbol (which should be a TokenSymbol)
     * @throws Exception
     */
    public static function convertToDecimals(
        string $amount,
        string $coinSymbol,
        ?string $tokenSymbol = null
    ): string {
        $conversionFactor = self::getConversionFactor(
            $coinSymbol,
            $tokenSymbol
        );
        $convertedAmount  = (10 ** $conversionFactor) * (float) $amount;
        self::throwIfNotInteger($convertedAmount);

        return strval($convertedAmount);
    }

    /**
     * Get the conversion factor based on the coin or token symbol.
     */
    private static function getConversionFactor(
        string $coinSymbol,
        ?string $tokenSymbol = null
    ): int {
        $network = L1Network::NETWORK_DICTIONARY[$coinSymbol];

        if (! $tokenSymbol) {
            return $network['nativeCoin']['decimal'];
        }

        foreach ($network['tokens'] as $token) {
            if ($token['symbol'] === $tokenSymbol) {
                return $token['decimal'];
            }
        }

        throw new Exception(OtherErrorCode::UNSUPPORTED_COIN_ERROR);
    }

    private static function throwIfNotInteger($amount): void
    {
        if (strval(fmod($amount, 1)) !== '0') {
            throw new Exception(OtherErrorCode::TRANSACTION_TOO_SMALL_ERROR);
        }
    }
}
