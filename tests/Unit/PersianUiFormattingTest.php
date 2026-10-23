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

    public function test_currency_code_controls_the_display_unit_without_converting_the_amount(): void
    {
        $this->assertSame('۱٬۲۳۴٬۵۶۷ ریال', PersianUi::moneyInCurrency(1234567, 'IRR'));
        $this->assertSame('۱٬۲۳۴٬۵۶۷ تومان', PersianUi::moneyInCurrency(1234567, 'IRT'));
    }
}
