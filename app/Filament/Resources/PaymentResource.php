<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $modelLabel = 'Pembayaran';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('invoice_id')
                    ->relationship('invoice', 'invoice_number')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpan(1),

                DatePicker::make('payment_date')
                    ->default(now())
                    ->required()
                    ->columnSpan(1),

                TextInput::make('amount')
                    ->label('Jumlah Bayar')
                    ->numeric()
                    ->prefix('Rp')
                    ->required()
                    ->columnSpan(1),

                ToggleButtons::make('method')
                    ->label('Metode Pembayaran')
                    ->options([
                        'CASH' => 'Tunai',
                        'TRANSFER' => 'Transfer',
                        'DEBIT' => 'Debit',
                        'CREDIT' => 'Kredit',
                    ])
                    ->inline()
                    ->default('CASH')
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('reference')
                    ->label('Referensi (Optional)')
                    ->maxLength(255)
                    ->nullable()
                    ->columnSpanFull(),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice.invoice_number')
                    ->label('No. Faktur')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('payment_date')
                    ->label('Tanggal Bayar')
                    ->date()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('method')
                    ->label('Metode')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'TRANSFER' => 'primary',
                        'CASH' => 'success',
                        default => 'secondary',
                    }),
            ])
            ->defaultSort('payment_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}