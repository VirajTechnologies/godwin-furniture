<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\BranchProductPrice;
use App\Models\Category;
use App\Models\City;
use App\Models\Customer;
use App\Models\District;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\State;
use App\Models\Stock;
use App\Models\StockRequest;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\User;
use App\Models\Warehouse;
use App\Support\BranchSale;
use App\Support\StockLedger;
use App\Support\StockTransferWorkflow;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LoadSampleData extends Command
{
    protected $signature = 'sample:load';

    protected $description = 'Load sample showroom data with factories';

    public function handle(StockLedger $ledger, StockTransferWorkflow $workflow, BranchSale $sales): int
    {
        if (Branch::query()->where('code', 'BR001')->exists()) {
            $this->components->error('Sample data is already loaded. Run migrate:fresh --seed, then sample:load again.');

            return self::FAILURE;
        }

        $warehouse = Warehouse::query()->where('code', 'WH001')->first();
        $admin = User::query()->where('email', 'admin@godwin.test')->first();

        if (! $warehouse || ! $admin) {
            $this->components->error('Run php artisan migrate:fresh --seed first.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($ledger, $workflow, $sales, $warehouse, $admin): void {
            $places = $this->places();
            $branches = $this->branches($warehouse, $places);
            $staff = $this->staff($warehouse, $branches);
            $products = $this->products();

            $this->at(now()->subDays(11)->setTime(10, 0), function () use ($ledger, $products, $warehouse, $admin): void {
                foreach ($this->openingQuantities() as $code => $quantity) {
                    $ledger->openWarehouseStock($products[$code], $warehouse, $quantity, 'Opening count', $admin->id);
                }
            });

            $stock = Stock::query()
                ->where('product_id', $products['ST001']->id)
                ->where('warehouse_id', $warehouse->id)
                ->firstOrFail();

            $this->at(now()->subDays(10)->setTime(11, 30), function () use ($ledger, $stock, $admin): void {
                $ledger->adjust($stock, 2, 'add', 'Assembled extra wardrobes', $admin->id);
            });

            $this->transfer($workflow, 'TR001', $warehouse, $branches['BR001'], $staff['warehouse'], $staff['managers']['BR001'], 'Weekly refill for Vijayawada', [
                'SF001' => 3, 'CH001' => 4, 'ST002' => 2, 'DN002' => 2,
            ], $products, now()->subDays(8)->setTime(9, 30), now()->subDays(8)->setTime(16, 0));

            $this->transfer($workflow, 'TR002', $warehouse, $branches['BR002'], $staff['warehouse'], $staff['managers']['BR002'], 'Opening stock for Guntur', [
                'SF002' => 2, 'BD001' => 2, 'CH002' => 3, 'ST001' => 1,
            ], $products, now()->subDays(7)->setTime(10, 0), now()->subDays(7)->setTime(15, 30));

            $this->transfer($workflow, 'TR003', $warehouse, $branches['BR003'], $staff['warehouse'], $staff['managers']['BR003'], 'Opening stock for Visakhapatnam', [
                'SF003' => 1, 'BD002' => 2, 'DN001' => 1, 'CH001' => 4,
            ], $products, now()->subDays(6)->setTime(10, 15), now()->subDays(6)->setTime(17, 0));

            $this->sale($sales, $branches['BR003'], $staff['cashiers']['BR003'], $products['CH001'], 2, Payment::METHOD_CASH, 'Venkat Rao', '9848011007', now()->subDays(5)->setTime(12, 10));
            $this->sale($sales, $branches['BR002'], $staff['cashiers']['BR002'], $products['BD001'], 1, Payment::METHOD_UPI, 'Kiran Reddy', '9848011005', now()->subDays(4)->setTime(15, 40), [
                ['product' => $products['CH002'], 'quantity' => 1],
            ]);
            $this->sale($sales, $branches['BR001'], $staff['cashiers']['BR001'], $products['ST002'], 1, Payment::METHOD_CARD, 'Suresh Babu', '9848011003', now()->subDays(3)->setTime(13, 20));

            $this->at(now()->subDays(4)->setTime(9, 0), function () use ($branches, $staff, $products): void {
                $this->stockRequest('R00001', $branches['BR001'], $staff['managers']['BR001'], 'Changed the display plan', [
                    'ST001' => 1,
                ], $products, cancelled: true);
            });

            $vijayawadaRequest = $this->at(now()->subDays(3)->setTime(9, 15), function () use ($branches, $staff, $products) {
                return $this->stockRequest('R00002', $branches['BR001'], $staff['managers']['BR001'], 'Showroom needs another sofa and two chairs', [
                    'SF002' => 1, 'CH002' => 2,
                ], $products);
            });

            $this->at(now()->subDays(2)->setTime(11, 0), function () use ($workflow, $warehouse, $branches, $staff, $products, $vijayawadaRequest): void {
                $transfer = $this->draft('TR004', $warehouse, $branches['BR001'], $staff['warehouse'], 'Second delivery for Vijayawada', [
                    'SF002' => 1, 'CH002' => 2,
                ], $products);
                $workflow->dispatch($transfer, $staff['warehouse']->id);
                $vijayawadaRequest->update(['stock_transfer_id' => $transfer->id]);
            });

            $gunturRequest = $this->at(now()->subDay()->setTime(10, 0), function () use ($branches, $staff, $products) {
                return $this->stockRequest('R00003', $branches['BR002'], $staff['managers']['BR002'], 'Wardrobe and beds for a home order', [
                    'BD001' => 2, 'ST002' => 1,
                ], $products);
            });

            $this->at(now()->subDay()->setTime(14, 0), function () use ($warehouse, $branches, $staff, $products, $gunturRequest): void {
                $transfer = $this->draft('TR005', $warehouse, $branches['BR002'], $staff['warehouse'], 'Next Guntur delivery', [
                    'BD001' => 2, 'ST002' => 1,
                ], $products);
                $gunturRequest->update(['stock_transfer_id' => $transfer->id]);
            });

            $this->at(now()->subDays(2)->setTime(11, 40), function () use ($warehouse, $branches, $staff, $products): void {
                $transfer = $this->draft('TR006', $warehouse, $branches['BR003'], $staff['warehouse'], 'Cancelled before dispatch', [
                    'SF001' => 2,
                ], $products);
                $transfer->update([
                    'status' => StockTransfer::STATUS_CANCELLED,
                    'cancelled_at' => now(),
                ]);
            });

            $this->sale($sales, $branches['BR002'], $staff['cashiers']['BR002'], $products['SF002'], 1, Payment::METHOD_CASH, 'Priya Nair', '9848011004', now()->subDays(2)->setTime(16, 5));
            $this->sale($sales, $branches['BR001'], $staff['cashiers']['BR001'], $products['CH001'], 2, Payment::METHOD_UPI, 'Lakshmi Devi', '9848011002', now()->subDay()->setTime(11, 45), [
                ['product' => $products['DN002'], 'quantity' => 1],
            ]);
            $this->sale($sales, $branches['BR003'], $staff['cashiers']['BR003'], $products['SF003'], 1, Payment::METHOD_CARD, 'Anitha Rao', '9848011006', now()->subDay()->setTime(18, 10));
            $this->sale($sales, $branches['BR001'], $staff['cashiers']['BR001'], $products['SF001'], 1, Payment::METHOD_CASH, 'Ravi Kumar', '9848011001', now()->setTime(10, 25));

            $this->at(now()->setTime(9, 40), function () use ($branches, $staff, $products): void {
                $this->stockRequest('R00004', $branches['BR003'], $staff['managers']['BR003'], 'Dining table for a customer visit', [
                    'DN001' => 1, 'CH002' => 2,
                ], $products);
            });
        });

        $this->components->info('Sample data loaded.');
        $this->table(['Email', 'Password', 'Signs in at'], [
            ['admin@godwin.test', 'password', 'Admin'],
            ['manager.vijayawada@godwin.test', 'password', 'Branch portal'],
            ['manager.guntur@godwin.test', 'password', 'Branch portal'],
            ['manager.vizag@godwin.test', 'password', 'Branch portal'],
            ['cashier.vijayawada@godwin.test', 'password', 'Branch portal'],
            ['cashier.guntur@godwin.test', 'password', 'Branch portal'],
            ['cashier.vizag@godwin.test', 'password', 'Branch portal'],
        ]);

        return self::SUCCESS;
    }

    /**
     * @return array{vijayawada: array{state: State, district: District, city: City}, guntur: array{state: State, district: District, city: City}, vizag: array{state: State, district: District, city: City}}
     */
    private function places(): array
    {
        $state = State::query()->where('state_name', 'Andhra Pradesh')->firstOrFail();
        $krishna = District::query()->where('state_id', $state->id)->where('district_name', 'Krishna')->firstOrFail();
        $vijayawada = City::query()->where('district_id', $krishna->id)->where('city_name', 'Vijayawada')->firstOrFail();

        $gunturDistrict = District::factory()->create([
            'state_id' => $state->id,
            'district_name' => 'Guntur',
        ]);
        $guntur = City::factory()->create([
            'state_id' => $state->id,
            'district_id' => $gunturDistrict->id,
            'city_name' => 'Guntur',
        ]);

        $vizagDistrict = District::factory()->create([
            'state_id' => $state->id,
            'district_name' => 'Visakhapatnam',
        ]);
        $vizag = City::factory()->create([
            'state_id' => $state->id,
            'district_id' => $vizagDistrict->id,
            'city_name' => 'Visakhapatnam',
        ]);

        return [
            'vijayawada' => ['state' => $state, 'district' => $krishna, 'city' => $vijayawada],
            'guntur' => ['state' => $state, 'district' => $gunturDistrict, 'city' => $guntur],
            'vizag' => ['state' => $state, 'district' => $vizagDistrict, 'city' => $vizag],
        ];
    }

    /**
     * @param  array{vijayawada: array{state: State, district: District, city: City}, guntur: array{state: State, district: District, city: City}, vizag: array{state: State, district: District, city: City}}  $places
     * @return array<string, Branch>
     */
    private function branches(Warehouse $warehouse, array $places): array
    {
        $rows = [
            'BR001' => ['Vijayawada Showroom', 'Ramesh Kumar', '9848090101', 'vijayawada@godwin.test', 'MG Road, Benz Circle', '520010', 'vijayawada'],
            'BR002' => ['Guntur Showroom', 'Srinivas Rao', '9848090102', 'guntur@godwin.test', 'Brodipet Main Road', '522002', 'guntur'],
            'BR003' => ['Visakhapatnam Showroom', 'Padma Reddy', '9848090103', 'vizag@godwin.test', 'Dwaraka Nagar, 2nd Lane', '530016', 'vizag'],
        ];

        $branches = [];

        foreach ($rows as $code => [$name, $contact, $phone, $email, $address, $pincode, $place]) {
            $location = $places[$place];
            $branches[$code] = Branch::factory()->create([
                'code' => $code,
                'name' => $name,
                'warehouse_id' => $warehouse->id,
                'contact_person' => $contact,
                'phone' => $phone,
                'email' => $email,
                'address_line' => $address,
                'state_id' => $location['state']->id,
                'district_id' => $location['district']->id,
                'city_id' => $location['city']->id,
                'pincode' => $pincode,
            ]);
        }

        return $branches;
    }

    /**
     * @param  array<string, Branch>  $branches
     * @return array{warehouse: User, managers: array<string, User>, cashiers: array<string, User>}
     */
    private function staff(Warehouse $warehouse, array $branches): array
    {
        $warehouseUser = $this->employee(
            'warehouse@godwin.test',
            'Mahesh Babu',
            '9848001001',
            'WHS001',
            'Store Keeper',
            'warehouseStaff',
            $warehouse->id,
            null,
        );

        return [
            'warehouse' => $warehouseUser,
            'managers' => [
                'BR001' => $this->employee('manager.vijayawada@godwin.test', 'Ramesh Kumar', '9848002001', 'MGR001', 'Branch Manager', 'branchManager', null, $branches['BR001']->id),
                'BR002' => $this->employee('manager.guntur@godwin.test', 'Srinivas Rao', '9848002002', 'MGR002', 'Branch Manager', 'branchManager', null, $branches['BR002']->id),
                'BR003' => $this->employee('manager.vizag@godwin.test', 'Padma Reddy', '9848002003', 'MGR003', 'Branch Manager', 'branchManager', null, $branches['BR003']->id),
            ],
            'cashiers' => [
                'BR001' => $this->employee('cashier.vijayawada@godwin.test', 'Sowmya Rao', '9848003001', 'CSH001', 'Cashier', 'branchStaff', null, $branches['BR001']->id),
                'BR002' => $this->employee('cashier.guntur@godwin.test', 'Harish Patel', '9848003002', 'CSH002', 'Cashier', 'branchStaff', null, $branches['BR002']->id),
                'BR003' => $this->employee('cashier.vizag@godwin.test', 'Divya Nair', '9848003003', 'CSH003', 'Cashier', 'branchStaff', null, $branches['BR003']->id),
            ],
        ];
    }

    private function employee(string $email, string $name, string $phone, string $code, string $designation, string $role, ?int $warehouseId, ?int $branchId): User
    {
        $user = User::factory()->{$role}()->create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
        ]);

        Employee::factory()->create([
            'user_id' => $user->id,
            'employee_code' => $code,
            'warehouse_id' => $warehouseId,
            'branch_id' => $branchId,
            'designation' => $designation,
        ]);

        return $user;
    }

    /**
     * @return array<string, Product>
     */
    private function products(): array
    {
        $living = $this->room('Living Room', 'living-room', 1, 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=600');
        $bedroom = $this->room('Bedroom', 'bedroom', 2, 'https://images.unsplash.com/photo-1505693314120-0d443867891c?auto=format&fit=crop&q=80&w=600');
        $dining = $this->room('Dining Room', 'dining-room', 3, 'https://images.unsplash.com/photo-1617806118233-18e1de247200?auto=format&fit=crop&q=80&w=600');
        $furnishings = $this->room('Furnishings', 'furnishings', 4, 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&q=80&w=600');

        $subs = [];
        $sort = 0;
        foreach ([
            // Living Room — Sofas & Seating
            ['3 Seater Sofas', '3-seater-sofas', 'Sofas & Seating'],
            ['2 Seater Sofas', '2-seater-sofas', 'Sofas & Seating'],
            ['1 Seater Sofas', '1-seater-sofas', 'Sofas & Seating'],
            ['Sofa Sets & Sectionals', 'sofa-sets-sectionals', 'Sofas & Seating'],
            ['Sofa Cum Beds & Corner Sofas', 'sofa-cum-beds-corner-sofas', 'Sofas & Seating'],
            // Living Room — Recliners & Chairs
            ['Leather & Fabric Recliners', 'leather-fabric-recliners', 'Recliners & Chairs'],
            ['Recliner Sets', 'recliner-sets', 'Recliners & Chairs'],
            ['Accent & Folding Chairs', 'accent-folding-chairs', 'Recliners & Chairs'],
            ['Benches & Ottoman Stools', 'benches-ottoman-stools', 'Recliners & Chairs'],
            ['Bean Bags & Pouffes', 'bean-bags-pouffes', 'Recliners & Chairs'],
            // Living Room — Tables & Storage
            ['Centre & Coffee Tables', 'centre-coffee-tables', 'Tables & Storage'],
            ['End & Console Tables', 'end-console-tables', 'Tables & Storage'],
            ['TV Consoles & Media Units', 'tv-consoles-media-units', 'Tables & Storage'],
            ['Wall Shelves & Home Mandir', 'wall-shelves-home-mandir', 'Tables & Storage'],
            ['Shoe Racks & Cabinets', 'shoe-racks-cabinets', 'Tables & Storage'],
        ] as [$name, $slug, $group]) {
            $subs[$slug] = $this->subcategory($living, $name, $slug, $group, ++$sort);
        }

        $sort = 0;
        foreach ([
            // Bedroom — Beds & Frames
            ['King Size Hydraulic Beds', 'king-size-hydraulic-beds', 'Beds & Frames'],
            ['Queen Size Solid Teak Beds', 'queen-size-solid-teak-beds', 'Beds & Frames'],
            ['Single & Poster Beds', 'single-poster-beds', 'Beds & Frames'],
            ['Heavy Duty Metal Bunk Beds', 'heavy-duty-metal-bunk-beds', 'Beds & Frames'],
            // Bedroom — Wardrobes & Storage
            ['2 Door Swing Wardrobes', '2-door-swing-wardrobes', 'Wardrobes & Storage'],
            ['3 & 4 Door Wardrobes', '3-4-door-wardrobes', 'Wardrobes & Storage'],
            ['Sliding Door Wardrobes', 'sliding-door-wardrobes', 'Wardrobes & Storage'],
            ['Bedside Tables & Nightstands', 'bedside-tables-nightstands', 'Wardrobes & Storage'],
            // Bedroom — Mattresses & Dressers
            ['Memory Foam Mattresses', 'memory-foam-mattresses', 'Mattresses & Dressers'],
            ['Spring & Orthopedic Mattresses', 'spring-orthopedic-mattresses', 'Mattresses & Dressers'],
            ['Chest of Drawers', 'chest-of-drawers', 'Mattresses & Dressers'],
            ['Dresser Mirrors & Vanities', 'dresser-mirrors-vanities', 'Mattresses & Dressers'],
        ] as [$name, $slug, $group]) {
            $subs[$slug] = $this->subcategory($bedroom, $name, $slug, $group, ++$sort);
        }

        $sort = 0;
        foreach ([
            // Dining — Dining Sets
            ['4-Seater Dining Sets', '4-seater-dining-sets', 'Dining Sets'],
            ['6-Seater Solid Teak Sets', '6-seater-solid-teak-sets', 'Dining Sets'],
            ['8-Seater Grand Dining Sets', '8-seater-grand-dining-sets', 'Dining Sets'],
            ['Industrial Steel Dining Sets', 'industrial-steel-dining-sets', 'Dining Sets'],
            // Dining — Chairs & Tables
            ['Teak & Marble Dining Tables', 'teak-marble-dining-tables', 'Chairs & Tables'],
            ['Upholstered Dining Chairs', 'upholstered-dining-chairs', 'Chairs & Tables'],
            ['Solid Wood Dining Benches', 'solid-wood-dining-benches', 'Chairs & Tables'],
            ['Crockery Cabinets & Curios', 'crockery-cabinets-curios', 'Chairs & Tables'],
            // Dining — Bar Furniture
            ['Luxury Bar Cabinets', 'luxury-bar-cabinets', 'Bar Furniture'],
            ['Bar Stools & Counter Chairs', 'bar-stools-counter-chairs', 'Bar Furniture'],
            ['Serving Trolleys & Carts', 'serving-trolleys-carts', 'Bar Furniture'],
            ['Wine Racks & Glasses', 'wine-racks-glasses', 'Bar Furniture'],
        ] as [$name, $slug, $group]) {
            $subs[$slug] = $this->subcategory($dining, $name, $slug, $group, ++$sort);
        }

        $sort = 0;
        foreach ([
            // Furnishings — Bedding & Sheets
            ['100% Cotton Double Bedsheets', 'cotton-double-bedsheets', 'Bedding & Sheets'],
            ['King & Queen Bedding Sets', 'king-queen-bedding-sets', 'Bedding & Sheets'],
            ['Pillows & Memory Foam Fillers', 'pillows-memory-foam-fillers', 'Bedding & Sheets'],
            ['Quilts, Comforters & Dohars', 'quilts-comforters-dohars', 'Bedding & Sheets'],
            // Furnishings — Cushions & Curtains
            ['Designer Cushion Covers', 'designer-cushion-covers', 'Cushions & Curtains'],
            ['Filled Floor Cushions', 'filled-floor-cushions', 'Cushions & Curtains'],
            ['Door & Window Curtains', 'door-window-curtains', 'Cushions & Curtains'],
            ['Blackout Blinds & Rods', 'blackout-blinds-rods', 'Cushions & Curtains'],
            // Furnishings — Rugs & Coverings
            ['Handwoven Wool Carpets', 'handwoven-wool-carpets', 'Rugs & Coverings'],
            ['Traditional Dhurries & Rugs', 'traditional-dhurries-rugs', 'Rugs & Coverings'],
            ['Anti-Skid Doormats', 'anti-skid-doormats', 'Rugs & Coverings'],
            ['Protective Sofa Covers', 'protective-sofa-covers', 'Rugs & Coverings'],
        ] as [$name, $slug, $group]) {
            $subs[$slug] = $this->subcategory($furnishings, $name, $slug, $group, ++$sort);
        }

        $img = fn (string $id) => "https://images.unsplash.com/{$id}?auto=format&fit=crop&q=80&w=800";

        $rows = [
            // Existing codes (stock / sales flows depend on these)
            ['SF001', 'Alanis Metal Frame 3-Seater Velvet Sofa', 'alanis-metal-frame-3-seater-velvet-sofa', $subs['3-seater-sofas'], 42000, 38999, 54999, true, true, 'CRCA Steel & Emerald Velvet', 'Metal frame three-seater with emerald velvet.', [$img('photo-1555041469-a586c61ea9bc'), $img('photo-1493663284031-b7e3aefcae8e')], Product::STATUS_ACTIVE],
            ['SF002', 'Four Seater Family Sofa', 'four-seater-family-sofa', $subs['sofa-sets-sectionals'], 62000, 58999, 74999, true, false, 'Solid Teak & Linen', 'Family sofa with removable cushions.', [$img('photo-1493663284031-b7e3aefcae8e')], Product::STATUS_ACTIVE],
            ['SF003', 'Nordic Modular Sectional Steel Sofa', 'nordic-modular-sectional-steel-sofa', $subs['sofa-sets-sectionals'], 78000, 72999, 94999, true, true, 'Powder Coated Steel', 'Corner sectional for the living room.', [$img('photo-1586023492125-27b2c045efd7')], Product::STATUS_ACTIVE],
            ['BD001', 'Queen Size Solid Teak Bed', 'queen-size-solid-teak-bed', $subs['queen-size-solid-teak-beds'], 38000, 35999, 49999, true, false, 'Solid Teak Wood', 'Queen teak bed with storage drawers.', [$img('photo-1505693314120-0d443867891c')], Product::STATUS_ACTIVE],
            ['BD002', 'Godwin Imperial Heavy Duty Metal & Teak King Bed', 'godwin-imperial-heavy-duty-metal-teak-king-bed', $subs['king-size-hydraulic-beds'], 45000, 42999, 59999, true, true, 'CRCA Steel & Seasoned Teak', 'King bed with heavy CRCA frame and teak accents.', [$img('photo-1505693314120-0d443867891c'), $img('photo-1524758631624-e2822e304c36')], Product::STATUS_ACTIVE],
            ['DN001', 'Imperial 6-Seater Steel Dining Set', 'imperial-6-seater-steel-dining-set', $subs['6-seater-solid-teak-sets'], 54000, 51999, 69999, true, true, 'CRCA Heavy Steel', 'Dining set with six matching chairs.', [$img('photo-1617806118233-18e1de247200')], Product::STATUS_ACTIVE],
            ['DN002', 'Godwin Compact 4-Seater Dining Set', 'godwin-compact-4-seater-dining-set', $subs['4-seater-dining-sets'], 42000, 39999, 54999, true, false, 'Solid Teak Wood', 'Compact dining set for an apartment.', [$img('photo-1577140917170-285929fb55b7')], Product::STATUS_ACTIVE],
            ['CH001', 'Italian Leather & Steel Accent Chair', 'italian-leather-steel-accent-chair', $subs['accent-folding-chairs'], 12500, 10999, 15999, true, false, 'Italian Leather', 'Single accent chair for the living room.', [$img('photo-1586023492125-27b2c045efd7')], Product::STATUS_ACTIVE],
            ['CH002', 'Godwin Steel & Velvet Recliner Chair', 'godwin-steel-velvet-recliner-chair', $subs['leather-fabric-recliners'], 18500, 16999, 22999, true, false, 'CRCA Steel & Velvet', 'Cushioned recliner for a lounge corner.', [$img('photo-1567538096630-e0c55bd6374c')], Product::STATUS_ACTIVE],
            ['ST001', 'Godwin 2-Door Swing Wardrobe', 'godwin-2-door-swing-wardrobe', $subs['2-door-swing-wardrobes'], 36000, 33999, 45999, true, false, 'Solid Teak Wood', 'Wardrobe with hanging space and shelves.', [$img('photo-1595428774223-ef52624120d2')], Product::STATUS_ACTIVE],
            ['ST002', 'Industrial Steel & Teak TV Console', 'industrial-steel-teak-tv-console', $subs['tv-consoles-media-units'], 18500, 16999, 24999, true, false, 'Powder Coated Steel', 'Low TV unit with drawers.', [$img('photo-1532372576444-dda954194ad0')], Product::STATUS_ACTIVE],
            ['OT001', 'Floor Ottoman Stool', 'floor-ottoman-stool', $subs['benches-ottoman-stools'], 4500, null, null, false, false, 'Fabric', 'Display sample, not for sale yet.', [], Product::STATUS_INACTIVE],

            // Extra catalog products (cover more static subcategories)
            ['SF004', 'Compact 2-Seater Lounge Sofa', 'compact-2-seater-lounge-sofa', $subs['2-seater-sofas'], 28000, 25999, 34999, true, true, 'Fabric & Steel', 'Two-seater sofa for apartments.', [$img('photo-1555041469-a586c61ea9bc')], Product::STATUS_ACTIVE],
            ['SF005', 'Single Seater Reading Sofa', 'single-seater-reading-sofa', $subs['1-seater-sofas'], 16000, 14999, 19999, true, false, 'Velvet', 'Deep single seat for reading corners.', [$img('photo-1567538096630-e0c55bd6374c')], Product::STATUS_ACTIVE],
            ['SF006', 'Corner Sofa Cum Bed', 'corner-sofa-cum-bed', $subs['sofa-cum-beds-corner-sofas'], 52000, 48999, 64999, true, true, 'Fabric & Foam', 'L-shaped sofa that converts to a bed.', [$img('photo-1493663284031-b7e3aefcae8e')], Product::STATUS_ACTIVE],
            ['CH003', 'Twin Leather Recliner Set', 'twin-leather-recliner-set', $subs['recliner-sets'], 42000, 39999, 52999, true, false, 'Leather', 'Matching pair of recliners.', [$img('photo-1567538096630-e0c55bd6374c')], Product::STATUS_ACTIVE],
            ['TB001', 'Round Teak Coffee Table', 'round-teak-coffee-table', $subs['centre-coffee-tables'], 14500, 12999, 17999, true, false, 'Solid Teak', 'Centre table with lower shelf.', [$img('photo-1532372576444-dda954194ad0')], Product::STATUS_ACTIVE],
            ['BD003', 'Single Poster Teak Bed', 'single-poster-teak-bed', $subs['single-poster-beds'], 22000, 19999, 27999, true, false, 'Solid Teak', 'Single bed with tall posts.', [$img('photo-1540518614846-7ede433c5163')], Product::STATUS_ACTIVE],
            ['BD004', 'Metal Bunk Bed Twin', 'metal-bunk-bed-twin', $subs['heavy-duty-metal-bunk-beds'], 31000, 28999, 37999, true, true, 'CRCA Steel', 'Heavy duty bunk for kids rooms.', [$img('photo-1505693314120-0d443867891c')], Product::STATUS_ACTIVE],
            ['ST003', '4 Door Sliding Wardrobe', '4-door-sliding-wardrobe', $subs['sliding-door-wardrobes'], 58000, 54999, 69999, true, false, 'Engineered Wood', 'Sliding wardrobe with mirror panel.', [$img('photo-1595428774223-ef52624120d2')], Product::STATUS_ACTIVE],
            ['MT001', 'Orthopedic Memory Foam Mattress', 'orthopedic-memory-foam-mattress', $subs['memory-foam-mattresses'], 24000, 21999, 29999, true, true, 'Memory Foam', 'Queen memory foam mattress.', [$img('photo-1522771739844-6a9f6d5f14af')], Product::STATUS_ACTIVE],
            ['DN003', 'Grand 8-Seater Dining Set', 'grand-8-seater-dining-set', $subs['8-seater-grand-dining-sets'], 86000, 81999, 99999, true, true, 'Solid Teak', 'Formal dining for large families.', [$img('photo-1617806118233-18e1de247200')], Product::STATUS_ACTIVE],
            ['DN004', 'Industrial Steel Dining Set', 'industrial-steel-dining-set', $subs['industrial-steel-dining-sets'], 48000, 44999, 57999, true, false, 'Powder Coated Steel', 'Six-seater industrial dining.', [$img('photo-1577140917170-285929fb55b7')], Product::STATUS_ACTIVE],
            ['BA001', 'Godwin Bar Cabinet', 'godwin-bar-cabinet', $subs['luxury-bar-cabinets'], 32000, 29999, 39999, true, false, 'Teak & Glass', 'Bar cabinet with glass doors.', [$img('photo-1514933651103-005eec06c04b')], Product::STATUS_ACTIVE],
            ['FN001', 'Cotton Double Bedsheet Set', 'cotton-double-bedsheet-set', $subs['cotton-double-bedsheets'], 2499, 1999, 3499, true, true, '100% Cotton', 'Double bedsheet with two pillow covers.', [$img('photo-1522771739844-6a9f6d5f14af')], Product::STATUS_ACTIVE],
            ['FN002', 'Designer Cushion Cover Pack', 'designer-cushion-cover-pack', $subs['designer-cushion-covers'], 1299, 999, 1799, true, false, 'Cotton Blend', 'Pack of four cushion covers.', [$img('photo-1584100936595-c0654b55a2e2')], Product::STATUS_ACTIVE],
            ['FN003', 'Handwoven Wool Carpet', 'handwoven-wool-carpet', $subs['handwoven-wool-carpets'], 8999, 7999, 11999, true, false, 'Wool', 'Living room carpet in warm tones.', [$img('photo-1600121848594-d8644e57abab')], Product::STATUS_ACTIVE],
        ];

        $products = [];

        foreach ($rows as [$code, $name, $slug, $category, $selling, $online, $mrp, $isOnline, $featured, $material, $description, $images, $status]) {
            $product = Product::factory()->create([
                'category_id' => $category->id,
                'code' => $code,
                'slug' => $slug,
                'name' => $name,
                'description' => $description,
                'material' => $material,
                'selling_price' => $selling,
                'online_price' => $online,
                'compare_at_price' => $mrp,
                'is_online' => $isOnline,
                'is_featured' => $featured,
                'status' => $status,
            ]);

            foreach ($images as $index => $url) {
                ProductImage::query()->create([
                    'product_id' => $product->id,
                    'url' => $url,
                    'sort_order' => $index,
                ]);
            }

            $products[$code] = $product;
        }

        $vijayawada = Branch::query()->where('code', 'BR001')->first();
        if ($vijayawada) {
            BranchProductPrice::query()->create([
                'branch_id' => $vijayawada->id,
                'product_id' => $products['SF001']->id,
                'price' => 40000,
            ]);
            BranchProductPrice::query()->create([
                'branch_id' => $vijayawada->id,
                'product_id' => $products['BD002']->id,
                'price' => 44000,
            ]);
        }

        return $products;
    }

    private function room(string $name, string $slug, int $sort, string $image): Category
    {
        return Category::factory()->create([
            'parent_id' => null,
            'name' => $name,
            'slug' => $slug,
            'menu_group' => null,
            'sort_order' => $sort,
            'image_url' => $image,
            'status' => Category::STATUS_ACTIVE,
        ]);
    }

    private function subcategory(Category $room, string $name, string $slug, string $group, int $sort): Category
    {
        return Category::factory()->create([
            'parent_id' => $room->id,
            'name' => $name,
            'slug' => $slug,
            'menu_group' => $group,
            'sort_order' => $sort,
            'image_url' => null,
            'status' => Category::STATUS_ACTIVE,
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function openingQuantities(): array
    {
        return [
            'SF001' => 12,
            'SF002' => 8,
            'SF003' => 5,
            'BD001' => 10,
            'BD002' => 6,
            'DN001' => 6,
            'DN002' => 8,
            'CH001' => 24,
            'CH002' => 18,
            'ST001' => 7,
            'ST002' => 10,
        ];
    }

    /**
     * @param  array<string, int>  $lines
     * @param  array<string, Product>  $products
     */
    private function transfer(
        StockTransferWorkflow $workflow,
        string $code,
        Warehouse $warehouse,
        Branch $branch,
        User $dispatcher,
        User $receiver,
        string $notes,
        array $lines,
        array $products,
        Carbon $dispatchedAt,
        Carbon $receivedAt,
    ): void {
        $transfer = $this->at($dispatchedAt, function () use ($workflow, $code, $warehouse, $branch, $dispatcher, $notes, $lines, $products) {
            $transfer = $this->draft($code, $warehouse, $branch, $dispatcher, $notes, $lines, $products);
            $workflow->dispatch($transfer, $dispatcher->id);

            return $transfer;
        });

        $this->at($receivedAt, fn () => $workflow->receive($transfer, $receiver->id));
    }

    /**
     * @param  array<string, int>  $lines
     * @param  array<string, Product>  $products
     */
    private function stockRequest(string $code, Branch $branch, User $manager, string $notes, array $lines, array $products, bool $cancelled = false): StockRequest
    {
        $request = StockRequest::query()->create([
            'code' => $code,
            'branch_id' => $branch->id,
            'status' => $cancelled ? StockRequest::STATUS_CANCELLED : StockRequest::STATUS_REQUESTED,
            'notes' => $notes,
            'requested_by' => $manager->id,
            'cancelled_at' => $cancelled ? now() : null,
        ]);

        foreach ($lines as $productCode => $quantity) {
            $request->items()->create([
                'product_id' => $products[$productCode]->id,
                'quantity' => $quantity,
            ]);
        }

        return $request;
    }

    /**
     * @param  array<string, int>  $lines
     * @param  array<string, Product>  $products
     */
    private function draft(string $code, Warehouse $warehouse, Branch $branch, User $user, string $notes, array $lines, array $products): StockTransfer
    {
        $transfer = StockTransfer::factory()->create([
            'code' => $code,
            'warehouse_id' => $warehouse->id,
            'branch_id' => $branch->id,
            'notes' => $notes,
            'created_by' => $user->id,
        ]);

        foreach ($lines as $productCode => $quantity) {
            StockTransferItem::factory()->create([
                'stock_transfer_id' => $transfer->id,
                'product_id' => $products[$productCode]->id,
                'quantity' => $quantity,
            ]);
        }

        return $transfer;
    }

    /**
     * @param  list<array{product: Product, quantity: int}>  $extra
     */
    private function sale(
        BranchSale $sales,
        Branch $branch,
        User $cashier,
        Product $product,
        int $quantity,
        string $method,
        string $customerName,
        string $phone,
        Carbon $when,
        array $extra = [],
    ): void {
        $customer = Customer::factory()->create([
            'name' => $customerName,
            'phone' => $phone,
            'email' => null,
        ]);

        $lines = [['product_id' => $product->id, 'quantity' => $quantity]];

        foreach ($extra as $line) {
            $lines[] = ['product_id' => $line['product']->id, 'quantity' => $line['quantity']];
        }

        $this->at($when, fn () => $sales->complete($branch, $customer, $lines, $method, $cashier->id, null));
    }

    private function at(Carbon $moment, callable $callback): mixed
    {
        Carbon::setTestNow($moment);

        try {
            return $callback();
        } finally {
            Carbon::setTestNow();
        }
    }
}
