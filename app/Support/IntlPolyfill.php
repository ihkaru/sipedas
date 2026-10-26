<?php

namespace Illuminate\Support {
    if (!function_exists('Illuminate\Support\extension_loaded')) {
        /**
         * Namespace-level extension_loaded fallback for Illuminate\Support\Number
         * when the PHP intl extension is not loaded in the runtime environment.
         */
        function extension_loaded(string $extension): bool
        {
            if ($extension === 'intl') {
                return true;
            }

            return \extension_loaded($extension);
        }
    }
}

namespace {
    if (!class_exists('NumberFormatter')) {
        /**
         * Robust fallback polyfill for NumberFormatter to prevent ViewException
         * and RuntimeException during number/currency formatting in environments
         * where ext-intl is unavailable.
         */
        class NumberFormatter
        {
            public const PATTERN_DECIMAL = 0;
            public const DECIMAL = 1;
            public const CURRENCY = 2;
            public const PERCENT = 3;
            public const SCIENTIFIC = 4;
            public const SPELLOUT = 5;
            public const ORDINAL = 6;
            public const DURATION = 7;
            public const NUMBERING_SYSTEM = 8;
            public const PATTERN_RULEBASED = 9;
            public const CURRENCY_ACCOUNTING = 10;
            public const DEFAULT_STYLE = 1;

            public const FRACTION_DIGITS = 8;
            public const MAX_FRACTION_DIGITS = 9;
            public const MIN_FRACTION_DIGITS = 10;
            public const INTEGER_DIGITS = 11;
            public const MAX_INTEGER_DIGITS = 12;
            public const MIN_INTEGER_DIGITS = 13;

            public const TYPE_DEFAULT = 1;
            public const TYPE_INT32 = 2;
            public const TYPE_INT64 = 3;
            public const TYPE_DOUBLE = 4;
            public const TYPE_CURRENCY = 5;

            protected string $locale;
            protected int $style;
            protected ?string $pattern;
            protected array $attributes = [];

            public function __construct(string $locale, int $style, ?string $pattern = null)
            {
                $this->locale = $locale;
                $this->style = $style;
                $this->pattern = $pattern;
            }

            public function setAttribute(int $attribute, int|float $value): bool
            {
                $this->attributes[$attribute] = $value;
                return true;
            }

            public function getAttribute(int $attribute): int|float|false
            {
                return $this->attributes[$attribute] ?? false;
            }

            public function format(int|float $value, int $type = self::TYPE_DEFAULT): string|false
            {
                $decimals = 0;
                if (isset($this->attributes[self::MAX_FRACTION_DIGITS])) {
                    $decimals = (int) $this->attributes[self::MAX_FRACTION_DIGITS];
                } elseif (isset($this->attributes[self::FRACTION_DIGITS])) {
                    $decimals = (int) $this->attributes[self::FRACTION_DIGITS];
                } elseif (is_float($value)) {
                    $parts = explode('.', (string) $value);
                    $decimals = isset($parts[1]) ? strlen($parts[1]) : 0;
                }

                $formatted = number_format($value, $decimals, ',', '.');
                if ($this->style === self::PERCENT) {
                    return number_format($value * 100, $decimals, ',', '.') . '%';
                }
                if ($this->style === self::CURRENCY) {
                    return 'Rp ' . $formatted;
                }
                return $formatted;
            }

            public function formatCurrency(float $amount, string $currency): string|false
            {
                return $currency . ' ' . number_format($amount, 2, ',', '.');
            }

            public function parse(string $string, int $type = self::TYPE_DOUBLE, ?int &$offset = null): int|float|false
            {
                $clean = preg_replace('/[^\d\.\,\-]/', '', $string);
                $clean = str_replace('.', '', $clean);
                $clean = str_replace(',', '.', $clean);
                return ($type === self::TYPE_INT32 || $type === self::TYPE_INT64)
                    ? (int) $clean
                    : (float) $clean;
            }
        }
    }
}
