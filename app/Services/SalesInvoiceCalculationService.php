<?php

namespace App\Services;

class SalesInvoiceCalculationService
{
    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @return array{subtotal: string, discount_amount: string, tax_amount: string, total_amount: string, paid_amount: string, balance_due: string, lines: array<int, array<string, mixed>>}
     */
    public function calculate(array $lines, string|int|float|null $paidAmount = 0): array
    {
        $subtotalCents = 0;
        $discountCents = 0;
        $taxCents = 0;
        $calculatedLines = [];

        foreach ($lines as $line) {
            $quantity = (float) ($line['quantity'] ?? 0);
            $unitPriceCents = $this->toCents($line['unit_price'] ?? 0);
            $lineSubtotalCents = (int) round($quantity * $unitPriceCents);
            $lineDiscountCents = $this->toCents($line['discount_amount'] ?? 0);
            $taxableCents = max(0, $lineSubtotalCents - $lineDiscountCents);
            $taxRate = (float) ($line['tax_rate'] ?? 0);
            $lineTaxCents = (int) round($taxableCents * ($taxRate / 100));
            $lineTotalCents = $taxableCents + $lineTaxCents;

            $subtotalCents += $lineSubtotalCents;
            $discountCents += $lineDiscountCents;
            $taxCents += $lineTaxCents;

            $calculatedLines[] = [
                ...$line,
                'discount_amount' => $this->formatCents($lineDiscountCents),
                'tax_rate' => $this->formatDecimal($taxRate),
                'tax_amount' => $this->formatCents($lineTaxCents),
                'line_total' => $this->formatCents($lineTotalCents),
            ];
        }

        $totalCents = $subtotalCents - $discountCents + $taxCents;
        $paidCents = $this->toCents($paidAmount);
        $balanceDueCents = $totalCents - $paidCents;

        return [
            'subtotal' => $this->formatCents($subtotalCents),
            'discount_amount' => $this->formatCents($discountCents),
            'tax_amount' => $this->formatCents($taxCents),
            'total_amount' => $this->formatCents($totalCents),
            'paid_amount' => $this->formatCents($paidCents),
            'balance_due' => $this->formatCents($balanceDueCents),
            'lines' => $calculatedLines,
        ];
    }

    private function toCents(string|int|float|null $amount): int
    {
        return (int) round(((float) ($amount ?? 0)) * 100);
    }

    private function formatCents(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }

    private function formatDecimal(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
}
