<?php
class MasterJenjang extends Model {
    protected string $table = 'master_jenjang';
    public function active(): array { return $this->query('SELECT * FROM master_jenjang WHERE is_active=1 ORDER BY sort_order, name'); }
}
class MasterKelompokUsia extends Model {
    protected string $table = 'master_kelompok_usia';
    public function active(): array { return $this->query('SELECT * FROM master_kelompok_usia WHERE is_active=1 ORDER BY sort_order, name'); }
}
class MasterRombongan extends Model {
    protected string $table = 'master_rombongan';
    public function active(): array { return $this->query('SELECT * FROM master_rombongan WHERE is_active=1 ORDER BY name'); }
}
