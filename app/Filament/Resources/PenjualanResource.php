<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Barang;
use Filament\Forms\Form;
use App\Models\Penjualan;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\PenjualanResource\Pages;

class PenjualanResource extends Resource
{
    protected static ?string $model = Penjualan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('kode_barang')
                    ->label('Scan / Masukkan Kode Barang')
                    ->required()
                    ->helperText('Bisa di-scan atau diketik manual')
                    ->hint(new HtmlString('
                        <button type="button" onclick="openScannerModal()" class="filament-link inline-flex items-center justify-center gap-0.5 font-medium outline-none hover:underline focus:underline text-sm text-primary-600">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                <path fill-rule="evenodd" d="M1 8a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 018.07 3h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0016.07 6H17a2 2 0 012 2v7a2 2 0 01-2 2H3a2 2 0 01-2-2V8zm13.5 3a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM10 14a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                            </svg>
                            <span>Scan Barcode</span>
                        </button>
                        <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
                        <div id="scanner-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);z-index:9999;padding:20px;box-sizing:border-box;">
                            <div style="background:white;max-width:600px;margin:50px auto;padding:20px;border-radius:8px;">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
                                    <h3 style="margin:0;">Scan QR / Barcode</h3>
                                    <button onclick="closeScanner()" style="background:none;border:none;font-size:20px;cursor:pointer;">×</button>
                                </div>
                                <div id="scanner" style="width:100%;height:300px;background:#eee;"></div>
                                <p id="scanner-status" style="margin-top:10px;color:#666;"></p>
                                <button onclick="closeScanner()" style="margin-top:15px;padding:8px 16px;background:#f0f0f0;border:1px solid #ddd;border-radius:4px;cursor:pointer;">Tutup Scanner</button>
                            </div>
                        </div>
                        <script>
                            let html5QrCode;

                            function openScannerModal() {
                                document.getElementById("scanner-modal").style.display = "block";
                                initializeScanner();
                            }

                            function closeScanner() {
                                if (html5QrCode) {
                                    html5QrCode.stop().then(() => {
                                        document.getElementById("scanner-modal").style.display = "none";
                                    }).catch(console.error);
                                } else {
                                    document.getElementById("scanner-modal").style.display = "none";
                                }
                            }

                            function initializeScanner() {
                            const statusElement = document.getElementById("scanner-status");
                            statusElement.textContent = "Menyiapkan scanner...";

                            if (typeof Html5Qrcode !== "undefined") {
                                html5QrCode = new Html5Qrcode("scanner");

                                Html5Qrcode.getCameras().then(devices => {
                                    if (devices && devices.length) {
                                        console.log(devices); // Periksa daftar kamera yang tersedia

                                        // Coba menggunakan indeks kamera
                                        // const cameraId = devices[1].id;

                                        // Coba menggunakan facingMode
                                        const backCamera = devices.find(d => d.facingMode === "environment" || d.label.toLowerCase().includes("back"));
                                        if (!backCamera) {
                                            statusElement.textContent = "Kamera belakang tidak ditemukan";
                                            return;
                                        }

                                        const cameraId = backCamera.id;
                                        statusElement.textContent = "Memulai kamera...";

                                        html5QrCode.start(
                                            cameraId,
                                            {
                                                fps: 10,
                                                qrbox: { width: 250, height: 250 }
                                            },
                                            qrCodeMessage => {
                                                const input = document.querySelector("input[name=\'data[kode_barang]\']");
                                                if (input) {
                                                    input.value = qrCodeMessage;
                                                    input.dispatchEvent(new Event("input"));
                                                    closeScanner();
                                                }
                                            },
                                            errorMessage => {
                                                statusElement.textContent = "Scanning: " + errorMessage;
                                            }
                                        ).then(() => {
                                            statusElement.textContent = "Arahkan kamera ke QR code";
                                        }).catch(err => {
                                            statusElement.textContent = "Gagal memulai kamera: " + err.message;
                                            console.error(err);
                                        });
                                    } else {
                                        statusElement.textContent = "Tidak ada kamera yang ditemukan";
                                    }
                                }).catch(err => {
                                    statusElement.textContent = "Tidak dapat mengakses kamera: " + err.message;
                                    console.error(err);
                                });
                            } else {
                                statusElement.textContent = "Library scanner tidak tersedia";
                            }
                        }

                        function closeScanner() {
                            if (html5QrCode && html5QrCode.isRunning()) {
                                html5QrCode.stop().then(() => {
                                    html5QrCode = null;
                                    document.getElementById("scanner-modal").style.display = "none";
                                }).catch(err => {
                                    console.error(err);
                                });
                            } else {
                                document.getElementById("scanner-modal").style.display = "none";
                            }
                        }
                        </script>
                    ')),
                TextInput::make('jumlah')
                    ->numeric()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, Forms\Set $set, callable $get) {
                        $barang = Barang::find($get('barang_id'));
                        if ($barang) {
                            $set('total', $barang->harga * $state);
                        }
                    }),
                TextInput::make('total')->numeric()->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('barang.nama'),
                Tables\Columns\TextColumn::make('jumlah'),
                Tables\Columns\TextColumn::make('total')->money('IDR'),
                Tables\Columns\TextColumn::make('created_at')->label('Waktu'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenjualans::route('/'),
            'create' => Pages\CreatePenjualan::route('/create'),
            'edit' => Pages\EditPenjualan::route('/{record}/edit'),
        ];
    }
}
