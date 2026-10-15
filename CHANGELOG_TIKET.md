# Perubahan Skema Cetak Tiket Laporan

## Tanggal: 2026-09-16

### Ringkasan
Mengubah format laporan tiket dari agregat per jenis tiket menjadi **per tiket individual**. 
Jika 1 transaksi memiliki 2 tiket, maka di laporan akan muncul 2 baris terpisah.

### File yang Diubah

#### 1. `models/Report.php`
**Perubahan Query:**
- **Sebelum:** Query mengagregasi tiket dengan `SUM()` dan `GROUP BY tt.id`
- **Sesudah:** Query mengembalikan setiap `transaction_item` sebagai row terpisah
- **Detail:** Menghapus agregasi `COALESCE(SUM(...))` untuk kolom `la`, `lb`, `lc`
- **Menambahkan:** `ti.quantity`, `ti.tariff_day`, `ti.subtotal` per item

```sql
-- Sebelum (agregat):
SELECT tt.name, tt.color, 
       SUM(CASE WHEN ti.tariff_day = 'A' THEN ti.quantity ELSE 0 END) la,
       ...
GROUP BY tt.id

-- Sesudah (per item):
SELECT tt.name, tt.color, tt.price_a, tt.price_b, tt.price_c,
       ti.quantity, ti.tariff_day, ti.subtotal
FROM ticket_types tt
JOIN transaction_items ti ...
ORDER BY tt.id
```

#### 2. `controllers/OpsController.php` - `LaporanController::shape()`
**Perubahan Mapping:**
- Menambahkan field baru ke array `$tiket`:
  - `tariff_day` - Tarif hari yang dipakai (A/B/C)
  - `quantity` - Jumlah tiket dalam item tersebut
  - `unit_prices` - Array harga per tarif
  - `subtotal` - Total harga item

#### 3. `views/pages/laporan.php`
**Perubahan Besar:**

**a) Section Perhitungan Tiket (baris 25-42):**
```php
// Logika baru: Expand tiket berdasarkan quantity
foreach ($L['tiket'] as $r) {
    $qty = (int) $r['quantity'];
    // Loop untuk cetak qty tiket (misal qty=2, maka 2 baris)
    for ($i = 1; $i <= $qty; $i++) {
        $tiketRows[] = [...]; // Satu tiket = satu entry
        $totTiket += $tarif;
        $totLbr++;
    }
}
```

**b) Tabel Tiket (baris 165-178):**
- **Header baru:** `No | Jenis Tiket | Warna | Tarif | Harga (Rp)`
- **Sebelum:** Tabel kompleks dengan kolom Tarif A/B/C terpisah
- **Sesudah:** Tabel sederhana dengan nomor urut dan 1 kolom harga

```html
<!-- Sebelum -->
<th>Warna Tiket</th><th>(Rp)</th><th>Lbr</th> ... (11 kolom)

<!-- Sesudah -->
<th>No</th><th>Jenis Tiket</th><th>Warna</th><th>Tarif</th><th>Harga (Rp)</th>
```

**c) Row Tiket:**
```php
// Sebelum: 1 row per jenis tiket
<td><?= $r['nama'] ?></td><td><?= $r['la'] ?></td><td><?= $r['lb'] ?></td>...

// Sesudah: 1 row per tiket individual
<?php $no = 1; foreach ($tiketRows as $r): ?>
<td><?= $no++ ?></td><td><?= $r['nama'] ?></td><td><?= $r['warna'] ?></td>
<td><?= $r['tariff_day'] ?></td><td><?= $r['jml'] ?></td>
```

**d) Penyesuaian colspan untuk Member & Les:**
- Mengurangi colspan karena tabel tiket lebih sempit
- Member: colspan 6 → 4
- Les: colspan 7 → 6
- Setoran & Saldo Akhir: colspan disesuaikan

### Dampak Fungsional

#### Sebelum:
```
Transaksi #1: 2x Tiket Umum (Tarif A)
Transaksi #2: 3x Tiket Umum (Tarif A)

Laporan:
- Tiket Umum | Putih | Tarif A: 5 lembar | Rp 125.000
```

#### Sesudah:
```
Transaksi #1: 2x Tiket Umum (Tarif A)
Transaksi #2: 3x Tiket Umum (Tarif A)

Laporan:
1. Tiket Umum | Putih | A | Rp 25.000
2. Tiket Umum | Putih | A | Rp 25.000
3. Tiket Umum | Putih | A | Rp 25.000
4. Tiket Umum | Putih | A | Rp 25.000
5. Tiket Umum | Putih | A | Rp 25.000
---
Total: 5 lembar | Rp 125.000
```

### Kelebihan Format Baru
1. **Traceability lebih baik** - Setiap tiket dapat ditelusuri
2. **Cetak mudah** - Setiap baris = 1 tiket fisik yang bisa dipotong
3. **Audit lebih detail** - Menghitung ulang lebih mudah
4. **Konsisten dengan struk kasir** - Format serupa dengan transaksi

### Testing
- [x] Syntax check PHP (php -l) - OK
- [ ] Test dengan data dummy di browser
- [ ] Test laporan harian
- [ ] Test laporan mingguan
- [ ] Test laporan bulanan
- [ ] Test cetak (window.print)

### Catatan
- Format member dan les tetap agregat (tidak berubah)
- Total perhitungan tetap akurat
- Backward compatible dengan data lama di database
