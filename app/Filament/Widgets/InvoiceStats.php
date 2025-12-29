<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InvoiceStats extends BaseWidget
{
    protected static ?int $sort = 0; // Widget ini akan muncul paling atas

    protected function getStats(): array
    {
        // 1. Kalkulasi Pendapatan Lunas Bulan Ini
        $paidInvoices = Invoice::where('status', 'PAID')
            ->whereMonth('invoice_date', now()->month)
            ->whereYear('invoice_date', now()->year);
        
        $totalPaid = $paidInvoices->sum('total_amount');
        $countPaid = $paidInvoices->count();

        // 2. Kalkulasi Piutang Belum Lunas
        $issuedInvoices = Invoice::where('status', 'ISSUED');
        $totalIssued = $issuedInvoices->sum('total_amount');
        $countIssued = $issuedInvoices->count();

        return [
            Stat::make('Total Pendapatan Bulan Ini (Lunas)', 'Rp ' . number_format($totalPaid, 0, ',', '.'))
                ->description("{$countPaid} Faktur Lunas")
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Total Piutang Belum Lunas', 'Rp ' . number_format($totalIssued, 0, ',', '.'))
                ->description("{$countIssued} Faktur Menunggu Pembayaran")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
                
            Stat::make('Jumlah Pelanggan Terdaftar', Customer::count())
                ->description('Total pelanggan yang terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}