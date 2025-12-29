<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers\PaymentsRelationManager;
use App\Models\Invoice;
use App\Models\SparePart;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Placeholder; // Import untuk subtotal display
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $modelLabel = 'Faktur/Invoice';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ✅ PERBAIKAN: Grouping Informasi Utama dalam Section
                Section::make('Informasi Faktur')
                    ->description('Detail pelanggan, kendaraan terkait, dan status faktur.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                // customer_id
                                Select::make('customer_id')
                                    ->relationship('customer', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn (Set $set) => $set('vehicle_id', null))
                                    ->columnSpan(1),

                                // vehicle_id (difilter berdasarkan customer)
                                Select::make('vehicle_id')
                                    ->label('Kendaraan (Optional)')
                                    ->relationship('vehicle', 'plate_number', fn (callable $get, $query) => $query->where('customer_id', $get('customer_id') ?? 0))
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->columnSpan(1),

                                // invoice_date
                                DatePicker::make('invoice_date')
                                    ->default(now())
                                    ->required()
                                    ->columnSpan(1),

                                // invoice_number
                                TextInput::make('invoice_number')
                                    ->default('INV-' . Str::upper(Str::random(6)))
                                    ->unique(ignoreRecord: true)
                                    ->required()
                                    ->readOnlyOn('edit')
                                    ->columnSpan(1),
                                
                                // status (enum)
                                ToggleButtons::make('status')
                                    ->options([
                                        'DRAFT' => 'Draft',
                                        'ISSUED' => 'Diterbitkan',
                                        'PAID' => 'Lunas',
                                        'CANCELLED' => 'Batal',
                                    ])
                                    ->icons([
                                        'DRAFT' => 'heroicon-o-pencil',
                                        'ISSUED' => 'heroicon-o-paper-airplane',
                                        'PAID' => 'heroicon-o-check-circle',
                                        'CANCELLED' => 'heroicon-o-x-circle',
                                    ])
                                    ->inline()
                                    ->default('ISSUED')
                                    ->required()
                                    ->columnSpan(1),
                                
                                // ✅ PERBAIKAN: Tampilan Total Akhir Lebih Menonjol
                                Placeholder::make('total_amount_display') 
                                    ->label('TOTAL FAKTUR AKHIR')
                                    ->content(fn (Get $get) => 'Rp ' . number_format((float) $get('total_amount'), 0, ',', '.'))
                                    // Tambahkan styling visual untuk penekanan
                                    ->extraAttributes(['class' => 'font-bold text-xl text-primary-600 dark:text-primary-400'])
                                    ->dehydrated(false)
                                    ->columnSpan(1),
                            ]),
                    ]),

                // invoice_details (Repeater)
                Section::make('Detail Transaksi (Sparepart & Jasa)')
                    ->description('Tambahkan semua item, suku cadang, dan biaya jasa yang berlaku.')
                    ->schema([
                        Repeater::make('details')
                            ->relationship('details')
                            ->label(false)
                            ->schema([
                                // ✅ PERBAIKAN: Grid 5 Kolom untuk Subtotal Display
                                Grid::make(5) 
                                    ->schema([
                                        Select::make('spare_part_id')
                                            ->label('Sparepart')
                                            ->options(SparePart::pluck('name', 'id'))
                                            ->nullable()
                                            ->searchable()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                if ($state) {
                                                    $part = SparePart::find($state);
                                                    $set('unit_price', $part ? $part->price : 0);
                                                    $set('description', $part ? $part->name : null);
                                                }
                                                // Calculate subtotal
                                                $qty = (float) $get('quantity');
                                                $price = (float) $get('unit_price');
                                                $set('subtotal', $qty * $price);
                                            })
                                            ->columnSpan(2),

                                        TextInput::make('quantity')
                                            ->numeric()
                                            ->required()
                                            ->default(1)
                                            ->minValue(1)
                                            ->live(onBlur: true)
                                            ->columnSpan(1),

                                        TextInput::make('unit_price')
                                            ->label('Harga Satuan')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->columnSpan(1),
                                            
                                        // ✅ FIELD BARU: Tampilan Subtotal Real-Time di Repeater
                                        Placeholder::make('subtotal_display')
                                            ->label('Subtotal Baris')
                                            ->content(function (Get $get) {
                                                $subtotal = (float) $get('subtotal');
                                                return 'Rp ' . number_format($subtotal, 0, ',', '.');
                                            })
                                            ->columnSpan(1),
                                    ]),
                                    
                                Textarea::make('description')
                                    ->label('Deskripsi/Jasa Lain')
                                    ->nullable()
                                    ->required(fn (Get $get) => is_null($get('spare_part_id')))
                                    ->rows(1)
                                    ->columnSpanFull(),

                                // subtotal (Hidden) - Tetap diperlukan untuk menyimpan dan kalkulasi total
                                Hidden::make('subtotal')
                                    ->default(0)
                                    ->state(function (Set $set, Get $get) {
                                        $qty = (float) $get('quantity');
                                        $price = (float) $get('unit_price');
                                        $subtotal = $qty * $price;
                                        $set('subtotal', $subtotal);
                                        return $subtotal;
                                    })
                            ])
                            ->defaultItems(1)
                            ->columns(1)
                            ->columnSpanFull()
                            ->reactive()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $subtotal = collect($get('details'))
                                    ->sum(fn (array $item) => (float) $item['subtotal']);

                                $set('total_amount', round($subtotal, 2));
                            })
                            ->deleteAction(
                                fn (Action $action) => $action->label('Hapus Item')->icon('heroicon-o-trash'),
                            )
                    ]),
                
                // total_amount (Hidden untuk DB)
                Hidden::make('total_amount')
                    ->default(0)
                    ->dehydrated(true),

                // notes
                Textarea::make('notes')
                    ->label('Catatan Faktur')
                    ->columnSpanFull()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')->label('No. Faktur')->searchable()->sortable(),
                TextColumn::make('customer.name')->label('Pelanggan')->searchable()->sortable(),
                TextColumn::make('invoice_date')->label('Tanggal')->date()->sortable(),
                // ✅ PERBAIKAN: Formatting uang lebih tebal di tabel
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->weight('bold'),

                BadgeColumn::make('status')
                    ->color(fn (string $state): string => match ($state) {
                        'PAID' => 'success',
                        'ISSUED' => 'primary',
                        'DRAFT' => 'gray',
                        'CANCELLED' => 'danger',
                        default => 'secondary',
                    })
                    ->sortable(),
            ])
            ->defaultSort('invoice_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}