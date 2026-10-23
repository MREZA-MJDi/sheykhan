<?php

namespace Tests\Unit;

use App\Support\PersianUi;
use PHPUnit\Framework\TestCase;

class PersianUiFormattingTest extends TestCase
{
    public function test_amounts_use_persian_digits_and_consistent_three_digit_grouping(): void
    {
        $this->assertSame('۱٬۲۳۴٬۵۶۷', PersianUi::number(1234567));
        $this->assertSame('۱٬۲۳۴٬۵۶۷ تومان', PersianUi::money(1234567));
        $this->assertSame('۱٬۲۳۴٬۵۶۷ ریال', PersianUi::moneyRial(1234567));
    }

    public function test_datetime_uses_jalali_date_and_persian_digits(): void
    {
        $this->assertSame('۱۴۰۵/۰۱/۰۱ · ۱۴:۰۵', PersianUi::dateTime('2026-03-21 14:05:00'));
    }

    public function test_currency_code_controls_the_display_unit_without_converting_the_amount(): void
    {
        $this->assertSame('۱٬۲۳۴٬۵۶۷ ریال', PersianUi::moneyInCurrency(1234567, 'IRR'));
        $this->assertSame('۱٬۲۳۴٬۵۶۷ تومان', PersianUi::moneyInCurrency(1234567, 'IRT'));
    }
}
