<?php

namespace App\Models\Master;

use App\Models\Gudang\Stok;
use App\Models\Assets\AssetBarang;
use App\Models\Gudang\PembelianDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Barang extends Model
{

    protected $table = 'um_barang';
    protected $guarded = [];

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(BarangSatuan::class, 'satuan_id', 'id');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(BarangKategori::class, 'kategori_id', 'id');
    }

    function pembelian(): HasMany
    {
        return $this->hasMany(PembelianDetail::class, 'pembelian_id', 'id');
    }

    function stoks(): HasMany
    {
        return $this->hasMany(Stok::class, 'barang_id', 'id');
    }

    function assets(): HasMany
    {
        return $this->hasMany(AssetBarang::class, 'barang_id', 'id');
    }

    public function latestStok()
    {
        return $this->hasOne(Stok::class)->latestOfMany();
        // Or: return $this->hasOne(Stok::class)->latest();
    }

    public function konversiSatuans(): HasMany
    {
        return $this->hasMany(BarangKonversiSatuan::class, 'barang_id', 'id');
    }
}
