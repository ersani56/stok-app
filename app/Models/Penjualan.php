<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;
    protected $fillable = ['barang_id', 'jumlah', 'total'];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
    protected static function booted(): void
    {
        static::created(function ($penjualan) {
            $barang = $penjualan->barang;
            $barang->decrement('stok', $penjualan->jumlah);
        });
    }
}
