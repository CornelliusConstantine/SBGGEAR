<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create products
        $products = [
            // Kategori 1: Eye Protection (Kacamata Keselamatan)
            [
                'name' => 'Kacamata Safety 3M Virtua CCS',
                'slug' => 'kacamata-safety-3m-virtua-ccs',
                'description' => 'Kacamata pelindung ringan dengan desain stylish dan lapisan anti-gores. Dilengkapi dengan sistem tali cord control untuk kenyamanan penggunaan sepanjang hari.',
                'price' => 125000,
                'stock' => 85,
                'category_id' => 1,
                'sku' => 'KCM-001',
                'is_active' => true,
                'weight' => 30.00,
                'specifications' => json_encode([
                    'Material' => 'Polikarbonat dengan frame nilon',
                    'Warna' => 'Clear lens dengan frame hitam',
                    'Sertifikasi' => 'ANSI Z87.1-2020, CSA Z94.3',
                    'Fitur' => 'Anti-gores, sistem cord control, desain ringan'
                ]),
                'images' => json_encode([
                    'main' => 'kacamata-3m-virtua-main.jpg',
                    'gallery' => [
                        'kacamata-3m-virtua-1.jpg',
                        'kacamata-3m-virtua-2.jpg'
                    ]
                ]),
                'is_featured' => true,
            ],
            [
                'name' => 'Kacamata Goggle Kings KY1151',
                'slug' => 'kacamata-goggle-kings-ky1151',
                'description' => 'Kacamata goggle dengan ventilasi tidak langsung dan lapisan anti-kabut. Memberikan perlindungan menyeluruh untuk mata dari percikan dan partikel berbahaya.',
                'price' => 185000,
                'stock' => 60,
                'category_id' => 1,
                'sku' => 'KCM-002',
                'is_active' => true,
                'weight' => 80.00,
                'specifications' => json_encode([
                    'Material' => 'Lensa polikarbonat dengan frame PVC lunak',
                    'Warna' => 'Clear lens dengan frame hitam',
                    'Sertifikasi' => 'EN 166, ANSI Z87.1+',
                    'Fitur' => 'Anti-kabut, ventilasi tidak langsung, tahan bahan kimia'
                ]),
                'images' => json_encode([
                    'main' => 'kacamata-kings-ky1151-main.jpg',
                    'gallery' => [
                        'kacamata-kings-ky1151-1.jpg',
                        'kacamata-kings-ky1151-2.jpg'
                    ]
                ]),
                'is_featured' => false,
            ],
            [
                'name' => 'Kacamata Las Lakoni Xtra Vision',
                'slug' => 'kacamata-las-lakoni-xtra-vision',
                'description' => 'Kacamata las premium dengan lensa flip-up dan perlindungan radiasi inframerah. Dirancang untuk memberikan kenyamanan maksimal saat pengelasan dengan visibilitas optimal.',
                'price' => 225000,
                'stock' => 40,
                'category_id' => 1,
                'sku' => 'KCM-003',
                'is_active' => true,
                'weight' => 110.00,
                'specifications' => json_encode([
                    'Material' => 'Lensa mineral dengan frame nilon tahan panas',
                    'Warna' => 'Hitam dengan lensa gelap',
                    'Sertifikasi' => 'EN 166, EN 169, EN 175',
                    'Fitur' => 'Sistem flip-up, filter radiasi IR, ventilasi samping'
                ]),
                'images' => json_encode([
                    'main' => 'kacamata-las-lakoni-main.jpg',
                    'gallery' => [
                        'kacamata-las-lakoni-1.jpg',
                        'kacamata-las-lakoni-2.jpg'
                    ]
                ]),
                'is_featured' => true,
            ],
            
            // Kategori 2: Footwear (Sepatu Keselamatan)
            [
                'name' => 'Sepatu Safety Caterpillar Hydroedge',
                'slug' => 'sepatu-safety-caterpillar-hydroedge',
                'description' => 'Sepatu safety premium dengan ujung baja dan teknologi anti-air. Dirancang untuk kenyamanan maksimal dalam penggunaan jangka panjang di berbagai kondisi kerja.',
                'price' => 1250000,
                'stock' => 30,
                'category_id' => 2,
                'sku' => 'SPT-001',
                'is_active' => true,
                'weight' => 1200.00,
                'specifications' => json_encode([
                    'Material' => 'Kulit asli dengan lapisan anti-air',
                    'Warna' => 'Coklat tua',
                    'Sertifikasi' => 'SNI 7079:2016, ASTM F2413-18',
                    'Fitur' => 'Ujung baja, sol anti-slip, teknologi anti-air, bantalan EVA'
                ]),
                'images' => json_encode([
                    'main' => 'sepatu-cat-hydroedge-main.jpg',
                    'gallery' => [
                        'sepatu-cat-hydroedge-1.jpg',
                        'sepatu-cat-hydroedge-2.jpg'
                    ]
                ]),
                'is_featured' => true,
            ],
            [
                'name' => 'Sepatu Keselamatan Krisbow Apache',
                'slug' => 'sepatu-keselamatan-krisbow-apache',
                'description' => 'Sepatu keselamatan tangguh dengan desain ringan dan nyaman. Dilengkapi dengan ujung komposit dan sol anti-penetrasi untuk perlindungan optimal.',
                'price' => 875000,
                'stock' => 45,
                'category_id' => 2,
                'sku' => 'SPT-002',
                'is_active' => true,
                'weight' => 950.00,
                'specifications' => json_encode([
                    'Material' => 'Kulit sintetis berkualitas tinggi',
                    'Warna' => 'Hitam',
                    'Sertifikasi' => 'SNI 7079:2016, EN ISO 20345:2011 S3',
                    'Fitur' => 'Ujung komposit, sol anti-penetrasi, tahan minyak'
                ]),
                'images' => json_encode([
                    'main' => 'sepatu-krisbow-apache-main.jpg',
                    'gallery' => [
                        'sepatu-krisbow-apache-1.jpg',
                        'sepatu-krisbow-apache-2.jpg'
                    ]
                ]),
                'is_featured' => false,
            ],
            [
                'name' => 'Sepatu Safety Cheetah 7012H',
                'slug' => 'sepatu-safety-cheetah-7012h',
                'description' => 'Sepatu safety lokal berkualitas tinggi dengan ujung baja dan fitur anti-statis. Cocok untuk penggunaan di pabrik, bengkel, dan area konstruksi.',
                'price' => 685000,
                'stock' => 55,
                'category_id' => 2,
                'sku' => 'SPT-003',
                'is_active' => true,
                'weight' => 1050.00,
                'specifications' => json_encode([
                    'Material' => 'Kulit asli dan kain nilon',
                    'Warna' => 'Hitam dengan aksen kuning',
                    'Sertifikasi' => 'SNI 7079:2016',
                    'Fitur' => 'Ujung baja, anti-statis, sol tahan panas'
                ]),
                'images' => json_encode([
                    'main' => 'sepatu-cheetah-7012h-main.jpg',
                    'gallery' => [
                        'sepatu-cheetah-7012h-1.jpg',
                        'sepatu-cheetah-7012h-2.jpg'
                    ]
                ]),
                'is_featured' => true,
            ],
            
            // Kategori 3: Hand Protection (Sarung Tangan Kerja)
            [
                'name' => 'Sarung Tangan Ansell HyFlex 11-840',
                'slug' => 'sarung-tangan-ansell-hyflex-11-840',
                'description' => 'Sarung tangan presisi dengan lapisan nitrile foam yang memberikan cengkeraman superior dan sensitivitas tinggi. Ideal untuk pekerjaan presisi di industri elektronik dan perakitan.',
                'price' => 95000,
                'stock' => 120,
                'category_id' => 3,
                'sku' => 'SRG-001',
                'is_active' => true,
                'weight' => 60.00,
                'specifications' => json_encode([
                    'Material' => 'Nylon knit dengan lapisan nitrile foam',
                    'Warna' => 'Abu-abu dengan lapisan hitam',
                    'Sertifikasi' => 'EN 388:2016 (4131A)',
                    'Fitur' => 'Anti-slip, sensitivitas tinggi, tahan minyak'
                ]),
                'images' => json_encode([
                    'main' => 'sarung-tangan-ansell-hyflex-main.jpg',
                    'gallery' => [
                        'sarung-tangan-ansell-hyflex-1.jpg',
                        'sarung-tangan-ansell-hyflex-2.jpg'
                    ]
                ]),
                'is_featured' => true,
            ],
            [
                'name' => 'Sarung Tangan Kulit Las Leopard',
                'slug' => 'sarung-tangan-kulit-las-leopard',
                'description' => 'Sarung tangan las berbahan kulit sapi asli dengan lapisan perlindungan panas. Tahan terhadap suhu tinggi dan percikan api, ideal untuk pekerjaan pengelasan dan pengerjaan logam panas.',
                'price' => 125000,
                'stock' => 80,
                'category_id' => 3,
                'sku' => 'SRG-002',
                'is_active' => true,
                'weight' => 180.00,
                'specifications' => json_encode([
                    'Material' => 'Kulit sapi asli dengan lapisan perlindungan panas',
                    'Warna' => 'Kuning kecoklatan',
                    'Sertifikasi' => 'EN 388:2016, EN 407',
                    'Fitur' => 'Tahan panas, tahan api, jahitan kevlar'
                ]),
                'images' => json_encode([
                    'main' => 'sarung-tangan-las-leopard-main.jpg',
                    'gallery' => [
                        'sarung-tangan-las-leopard-1.jpg',
                        'sarung-tangan-las-leopard-2.jpg'
                    ]
                ]),
                'is_featured' => false,
            ],
            [
                'name' => 'Sarung Tangan Anti Kimia 3M Virtex',
                'slug' => 'sarung-tangan-anti-kimia-3m-virtex',
                'description' => 'Sarung tangan khusus tahan bahan kimia untuk perlindungan maksimal dari zat berbahaya. Dirancang dengan material nitrile tebal dan tekstur grip untuk penanganan cairan kimia yang aman.',
                'price' => 135000,
                'stock' => 65,
                'category_id' => 3,
                'sku' => 'SRG-003',
                'is_active' => true,
                'weight' => 90.00,
                'specifications' => json_encode([
                    'Material' => 'Nitrile tebal 15mil',
                    'Warna' => 'Hijau',
                    'Sertifikasi' => 'EN 374-1:2016 Type A, EN 388:2016',
                    'Fitur' => 'Tahan kimia, tekstur grip, panjang 33cm'
                ]),
                'images' => json_encode([
                    'main' => 'sarung-tangan-3m-virtex-main.jpg',
                    'gallery' => [
                        'sarung-tangan-3m-virtex-1.jpg',
                        'sarung-tangan-3m-virtex-2.jpg'
                    ]
                ]),
                'is_featured' => true,
            ],
            
            // Kategori 4: Head Protection (Helm Keselamatan)
            [
                'name' => 'Helm Proyek MSA X3000',
                'slug' => 'helm-proyek-msa-x3000',
                'description' => 'Helm proyek premium dengan sistem ventilasi canggih dan kenyamanan maksimal untuk penggunaan jangka panjang. Dilengkapi dengan tali dagu yang dapat dilepas dan penutup yang dapat disesuaikan.',
                'price' => 325000,
                'stock' => 45,
                'category_id' => 4,
                'sku' => 'HLM-001',
                'is_active' => true,
                'weight' => 450.00,
                'specifications' => json_encode([
                    'Material' => 'ABS berkualitas tinggi',
                    'Warna' => 'Kuning',
                    'Sertifikasi' => 'SNI 8056:2014, ANSI Z89.1-2014 Type I, Class E',
                    'Fitur' => 'Sistem ventilasi, tali dagu, penyesuaian ukuran ratchet'
                ]),
                'images' => json_encode([
                    'main' => 'helm-msa-x3000-main.jpg',
                    'gallery' => [
                        'helm-msa-x3000-1.jpg',
                        'helm-msa-x3000-2.jpg'
                    ]
                ]),
                'is_featured' => true,
            ],
            [
                'name' => 'Helm Industri Honeywell H2000',
                'slug' => 'helm-industri-honeywell-h2000',
                'description' => 'Helm industri tahan benturan dengan desain ergonomis untuk kenyamanan sepanjang hari. Cocok untuk penggunaan di pabrik, konstruksi, dan lingkungan industri lainnya.',
                'price' => 275000,
                'stock' => 60,
                'category_id' => 4,
                'sku' => 'HLM-002',
                'is_active' => true,
                'weight' => 420.00,
                'specifications' => json_encode([
                    'Material' => 'Polietilen densitas tinggi',
                    'Warna' => 'Putih',
                    'Sertifikasi' => 'SNI 8056:2014, EN 397',
                    'Fitur' => 'Penyerap keringat, sistem penyesuaian 4 titik'
                ]),
                'images' => json_encode([
                    'main' => 'helm-honeywell-h2000-main.jpg',
                    'gallery' => [
                        'helm-honeywell-h2000-1.jpg',
                        'helm-honeywell-h2000-2.jpg'
                    ]
                ]),
                'is_featured' => false,
            ],
            [
                'name' => 'Helm Keselamatan 3M H-700',
                'slug' => 'helm-keselamatan-3m-h-700',
                'description' => 'Helm keselamatan ringan dengan desain modern dan perlindungan superior. Dilengkapi dengan sistem ventilasi dan suspensi yang dapat disesuaikan untuk kenyamanan sepanjang hari.',
                'price' => 295000,
                'stock' => 40,
                'category_id' => 4,
                'sku' => 'HLM-003',
                'is_active' => true,
                'weight' => 380.00,
                'specifications' => json_encode([
                    'Material' => 'ABS tahan UV',
                    'Warna' => 'Oranye',
                    'Sertifikasi' => 'SNI 8056:2014, ANSI Z89.1-2014 Type I, Class C',
                    'Fitur' => 'Bobot ringan, ventilasi, desain modern'
                ]),
                'images' => json_encode([
                    'main' => 'helm-3m-h700-main.jpg',
                    'gallery' => [
                        'helm-3m-h700-1.jpg',
                        'helm-3m-h700-2.jpg'
                    ]
                ]),
                'is_featured' => true,
            ],
            
            // Kategori 5: Respiratory Protection (Masker Pelindung)
            [
                'name' => 'Masker Respirator 3M 6200 Half Face',
                'slug' => 'masker-respirator-3m-6200-half-face',
                'description' => 'Masker respirator setengah wajah dengan desain ergonomis dan sistem filter ganda. Memberikan perlindungan superior dari partikel, gas, dan uap dengan kenyamanan maksimal.',
                'price' => 450000,
                'stock' => 40,
                'category_id' => 5,
                'sku' => 'MSK-001',
                'is_active' => true,
                'weight' => 250.00,
                'specifications' => json_encode([
                    'Material' => 'Elastomer termoplastik',
                    'Warna' => 'Abu-abu dengan filter hitam',
                    'Sertifikasi' => 'EN 140:1998, NIOSH',
                    'Fitur' => 'Filter ganda, desain ergonomis, tali kepala yang dapat disesuaikan'
                ]),
                'images' => json_encode([
                    'main' => 'masker-3m-6200-main.jpg',
                    'gallery' => [
                        'masker-3m-6200-1.jpg',
                        'masker-3m-6200-2.jpg'
                    ]
                ]),
                'is_featured' => true,
            ],
            [
                'name' => 'Masker N95 Honeywell H910 Plus',
                'slug' => 'masker-n95-honeywell-h910-plus',
                'description' => 'Masker N95 premium dengan efisiensi filtrasi 95% untuk partikel hingga 0.3 mikron. Desain lipat datar dengan klip hidung yang dapat disesuaikan dan tali elastis nyaman.',
                'price' => 25000,
                'stock' => 200,
                'category_id' => 5,
                'sku' => 'MSK-002',
                'is_active' => true,
                'weight' => 10.00,
                'specifications' => json_encode([
                    'Material' => 'Meltblown polypropylene',
                    'Warna' => 'Putih',
                    'Sertifikasi' => 'SNI 8394:2017, NIOSH N95',
                    'Fitur' => 'Efisiensi filtrasi 95%, desain lipat datar, klip hidung aluminium'
                ]),
                'images' => json_encode([
                    'main' => 'masker-honeywell-n95-main.jpg',
                    'gallery' => [
                        'masker-honeywell-n95-1.jpg',
                        'masker-honeywell-n95-2.jpg'
                    ]
                ]),
                'is_featured' => false,
            ],
            [
                'name' => 'Masker Full Face Dräger X-plore 6300',
                'slug' => 'masker-full-face-drager-xplore-6300',
                'description' => 'Masker pelindung seluruh wajah dengan visor anti-gores lebar dan sistem filter premium. Memberikan perlindungan maksimal untuk mata dan sistem pernapasan.',
                'price' => 1850000,
                'stock' => 15,
                'category_id' => 5,
                'sku' => 'MSK-003',
                'is_active' => true,
                'weight' => 580.00,
                'specifications' => json_encode([
                    'Material' => 'EPDM berkualitas tinggi dengan visor polikarbonat',
                    'Warna' => 'Hitam dengan visor transparan',
                    'Sertifikasi' => 'EN 136 Class 3, EN 148-1',
                    'Fitur' => 'Visor lebar anti-gores, sistem 5 titik pengikat, kompatibel dengan filter RD40'
                ]),
                'images' => json_encode([
                    'main' => 'masker-drager-xplore-main.jpg',
                    'gallery' => [
                        'masker-drager-xplore-1.jpg',
                        'masker-drager-xplore-2.jpg'
                    ]
                ]),
                'is_featured' => true,
            ],
            
            // Kategori 6: Visibility (Rompi Keselamatan)
            [
                'name' => 'Rompi Keselamatan Safety Vest KV01',
                'slug' => 'rompi-keselamatan-safety-vest-kv01',
                'description' => 'Rompi keselamatan dengan visibilitas tinggi dan reflektif kelas 2. Dilengkapi dengan saku utilitas dan ventilasi mesh untuk kenyamanan di lingkungan kerja outdoor.',
                'price' => 85000,
                'stock' => 100,
                'category_id' => 6,
                'sku' => 'RMP-001',
                'is_active' => true,
                'weight' => 250.00,
                'specifications' => json_encode([
                    'Material' => 'Polyester 120 gsm dengan panel mesh',
                    'Warna' => 'Kuning neon dengan strip reflektif',
                    'Sertifikasi' => 'EN ISO 20471:2013 Class 2',
                    'Fitur' => '4 saku depan, resleting depan, panel mesh untuk ventilasi'
                ]),
                'images' => json_encode([
                    'main' => 'rompi-kv01-main.jpg',
                    'gallery' => [
                        'rompi-kv01-1.jpg',
                        'rompi-kv01-2.jpg'
                    ]
                ]),
                'is_featured' => true,
            ],
            [
                'name' => 'Rompi Surveyor Krisbow Orange',
                'slug' => 'rompi-surveyor-krisbow-orange',
                'description' => 'Rompi surveyor profesional dengan banyak kantong fungsional dan visibilitas tinggi. Ideal untuk surveyor, supervisor, dan personel lapangan yang membutuhkan banyak penyimpanan.',
                'price' => 155000,
                'stock' => 70,
                'category_id' => 6,
                'sku' => 'RMP-002',
                'is_active' => true,
                'weight' => 350.00,
                'specifications' => json_encode([
                    'Material' => 'Polyester 150 gsm dengan lapisan mesh',
                    'Warna' => 'Oranye dengan strip reflektif',
                    'Sertifikasi' => 'EN ISO 20471:2013 Class 2',
                    'Fitur' => '8 kantong multifungsi, gantungan radio, ring ID'
                ]),
                'images' => json_encode([
                    'main' => 'rompi-krisbow-surveyor-main.jpg',
                    'gallery' => [
                        'rompi-krisbow-surveyor-1.jpg',
                        'rompi-krisbow-surveyor-2.jpg'
                    ]
                ]),
                'is_featured' => false,
            ],
            [
                'name' => 'Lampu Keselamatan LED Klip',
                'slug' => 'lampu-keselamatan-led-klip',
                'description' => 'Lampu LED kecil dengan klip yang dapat dipasang pada pakaian atau peralatan untuk meningkatkan visibilitas dalam kondisi cahaya rendah atau malam hari.',
                'price' => 45000,
                'stock' => 120,
                'category_id' => 6,
                'sku' => 'RMP-003',
                'is_active' => true,
                'weight' => 50.00,
                'specifications' => json_encode([
                    'Material' => 'Plastik ABS dengan lensa polikarbonat',
                    'Warna' => 'Merah/Kuning (pilihan)',
                    'Baterai' => 'CR2032 (termasuk)',
                    'Fitur' => 'Mode konstan dan berkedip, tahan air, klip pengaman'
                ]),
                'images' => json_encode([
                    'main' => 'lampu-led-klip-main.jpg',
                    'gallery' => [
                        'lampu-led-klip-1.jpg',
                        'lampu-led-klip-2.jpg'
                    ]
                ]),
                'is_featured' => true,
            ],
            
            // Kategori 7: Pelindung Telinga
            [
                'name' => 'Earplug 3M E-A-R Classic',
                'slug' => 'earplug-3m-e-a-r-classic',
                'description' => 'Sumbat telinga berbahan busa PVC berkualitas tinggi dengan desain ergonomis. Memberikan perlindungan pendengaran optimal dengan tingkat reduksi kebisingan tinggi.',
                'price' => 12000,
                'stock' => 250,
                'category_id' => 7,
                'sku' => 'APT-001',
                'is_active' => true,
                'weight' => 5.00,
                'specifications' => json_encode([
                    'Material' => 'Busa PVC slow recovery',
                    'Warna' => 'Kuning',
                    'Sertifikasi' => 'EN 352-2, SNI',
                    'Fitur' => 'NRR 29dB, bisa digunakan berulang, bentuk silinder'
                ]),
                'images' => json_encode([
                    'main' => 'earplug-3m-classic-main.jpg',
                    'gallery' => [
                        'earplug-3m-classic-1.jpg',
                        'earplug-3m-classic-2.jpg'
                    ]
                ]),
                'is_featured' => false,
            ],
            [
                'name' => 'Earmuff Peltor X5A 3M',
                'slug' => 'earmuff-peltor-x5a-3m',
                'description' => 'Pelindung telinga premium dengan peredam suara tertinggi di kelasnya. Dilengkapi dengan bantalan lembut dan headband yang dapat disesuaikan untuk kenyamanan sepanjang hari.',
                'price' => 485000,
                'stock' => 45,
                'category_id' => 7,
                'sku' => 'APT-002',
                'is_active' => true,
                'weight' => 350.00,
                'specifications' => json_encode([
                    'Material' => 'ABS dengan bantalan PVC dan busa',
                    'Warna' => 'Hitam dengan aksen hijau',
                    'Sertifikasi' => 'EN 352-1, ANSI S3.19-1974',
                    'Fitur' => 'NRR 31dB, headband yang dapat disesuaikan, teknologi space cup'
                ]),
                'images' => json_encode([
                    'main' => 'earmuff-peltor-x5a-main.jpg',
                    'gallery' => [
                        'earmuff-peltor-x5a-1.jpg',
                        'earmuff-peltor-x5a-2.jpg'
                    ]
                ]),
                'is_featured' => true,
            ],
            [
                'name' => 'Earmuff Elektronik Howard Leight Sync',
                'slug' => 'earmuff-elektronik-howard-leight-sync',
                'description' => 'Earmuff elektronik dengan radio FM terintegrasi dan bluetooth. Melindungi pendengaran sekaligus memungkinkan komunikasi dan hiburan saat bekerja di lingkungan bising.',
                'price' => 1250000,
                'stock' => 20,
                'category_id' => 7,
                'sku' => 'APT-003',
                'is_active' => true,
                'weight' => 380.00,
                'specifications' => json_encode([
                    'Material' => 'ABS premium dengan bantalan gel',
                    'Warna' => 'Hitam dengan aksen merah',
                    'Sertifikasi' => 'EN 352-1, EN 352-8',
                    'Fitur' => 'NRR 25dB, Bluetooth, radio FM, mikrofon terintegrasi'
                ]),
                'images' => json_encode([
                    'main' => 'earmuff-howard-leight-main.jpg',
                    'gallery' => [
                        'earmuff-howard-leight-1.jpg',
                        'earmuff-howard-leight-2.jpg'
                    ]
                ]),
                'is_featured' => true,
            ],
            
            // Kategori 8: Alat Keselamatan Ketinggian
            [
                'name' => 'Full Body Harness Karam PN 56',
                'slug' => 'full-body-harness-karam-pn56',
                'description' => 'Harness tubuh lengkap dengan 5 titik pengikatan dan bantalan ergonomis. Dirancang untuk kenyamanan maksimal dalam pemakaian jangka panjang saat bekerja di ketinggian.',
                'price' => 1850000,
                'stock' => 30,
                'category_id' => 8,
                'sku' => 'PKK-001',
                'is_active' => true,
                'weight' => 1800.00,
                'specifications' => json_encode([
                    'Material' => 'Webbing poliester 45mm dengan fitting baja',
                    'Warna' => 'Hitam dengan aksen merah',
                    'Sertifikasi' => 'EN 361, ANSI Z359.1',
                    'Fitur' => '5 titik pengikatan, bantalan punggung ergonomis, buckle quick release'
                ]),
                'images' => json_encode([
                    'main' => 'harness-karam-pn56-main.jpg',
                    'gallery' => [
                        'harness-karam-pn56-1.jpg',
                        'harness-karam-pn56-2.jpg'
                    ]
                ]),
                'is_featured' => true,
            ],
            [
                'name' => 'Lanyard Petzl Absorbica-Y MGO',
                'slug' => 'lanyard-petzl-absorbica-y-mgo',
                'description' => 'Lanyard double dengan penyerap energi terintegrasi. Dirancang untuk pergerakan vertikal dengan pengamanan 100% pada struktur logam atau kabel.',
                'price' => 3250000,
                'stock' => 15,
                'category_id' => 8,
                'sku' => 'PKK-002',
                'is_active' => true,
                'weight' => 1650.00,
                'specifications' => json_encode([
                    'Material' => 'Webbing poliamida dengan karabiner aluminium',
                    'Warna' => 'Kuning hitam',
                    'Sertifikasi' => 'CE EN 355, ANSI Z359.13',
                    'Fitur' => 'Panjang 150cm, penyerap energi, karabiner MGO, desain Y'
                ]),
                'images' => json_encode([
                    'main' => 'lanyard-petzl-absorbica-main.jpg',
                    'gallery' => [
                        'lanyard-petzl-absorbica-1.jpg',
                        'lanyard-petzl-absorbica-2.jpg'
                    ]
                ]),
                'is_featured' => false,
            ],
            [
                'name' => 'Self Retracting Lifeline Protecta Rebel',
                'slug' => 'self-retracting-lifeline-protecta-rebel',
                'description' => 'Perangkat penangkap jatuh otomatis dengan kabel baja galvanis. Memberikan kebebasan bergerak maksimal sambil tetap memberikan perlindungan jatuh yang andal.',
                'price' => 5750000,
                'stock' => 10,
                'category_id' => 8,
                'sku' => 'PKK-003',
                'is_active' => true,
                'weight' => 7500.00,
                'specifications' => json_encode([
                    'Material' => 'Housing thermoplastic dengan kabel baja galvanis',
                    'Warna' => 'Hitam kuning',
                    'Sertifikasi' => 'EN 360, ANSI Z359.14 Class B',
                    'Fitur' => 'Panjang 15m, indikator jatuh, swivel top, pengunci ganda'
                ]),
                'images' => json_encode([
                    'main' => 'srl-protecta-rebel-main.jpg',
                    'gallery' => [
                        'srl-protecta-rebel-1.jpg',
                        'srl-protecta-rebel-2.jpg'
                    ]
                ]),
                'is_featured' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['slug' => $product['slug']],
                $product
            );
        }
    }
} 