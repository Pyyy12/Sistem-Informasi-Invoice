<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Illuminate\Database\Eloquent\Builder;

class PaymentsRelationManager extends RelationManager
{
    // Menggunakan relasi 'payments' yang ada di model App\Models\Invoice
    protected static string $relationship = 'payments';
    protected static ?string $title = 'Pembayaran Terkait';
    protected static ?string $modelLabel = 'Pembayaran';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // invoice_id tidak perlu ditampilkan karena sudah otomatis terisi
                
                DatePicker::make('payment_date')
                    ->label('Tanggal Bayar')
                    ->default(now())
                    ->required()
                    ->columnSpan(1),

                TextInput::make('amount')
                    ->label('Jumlah Bayar')
                    ->numeric()
                    ->prefix('Rp')
                    ->required()
                    ->columnSpan(2),
                
                ToggleButtons::make('method')
                    ->label('Metode Pembayaran')
                    ->options([
                        'CASH' => 'Tunai',
                        'TRANSFER' => 'Transfer',
                        'DEBIT' => 'Debit',
                        'CREDIT' => 'Kredit',
                    ])
                    ->icons([
                        'CASH' => 'heroicon-o-wallet',
                        'TRANSFER' => 'heroicon-o-arrow-path',
                    ])
                    ->inline()
                    ->default('CASH')
                    ->required()
                    ->columnSpan(3),
                
                TextInput::make('reference')
                    ->label('Referensi (Nomor Transaksi)')
                    ->maxLength(255)
                    ->nullable()
                    ->columnSpan(3),
            ])
            ->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->columns([
                TextColumn::make('payment_date')
                    ->label('Tanggal Bayar')
                    ->date()
                    ->sortable(),
                
                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                
                BadgeColumn::make('method')
                    ->label('Metode')
                    ->color(fn (string $state): string => match ($state) {
                        'TRANSFER' => 'primary',
                        'CASH' => 'success',
                        default => 'secondary',
                    }),

                TextColumn::make('reference')
                    ->label('Referensi')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteAction::make(),
                ]),
            ])
            ->defaultSort('payment_date', 'desc');
    }
}