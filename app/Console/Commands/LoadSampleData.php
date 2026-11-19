<?php

namespace App\Console\Commands;

use App\Models\Agent;
use App\Models\AgentProductTerm;
use App\Models\Branch;
use App\Models\BranchProductPrice;
use App\Models\City;
use App\Models\Customer;
use App\Models\District;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\State;
use App\Models\Stock;
use App\Models\StockRequest;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\User;
use App\Models\Warehouse;
use App\Support\AgentSale;
use App\Support\BranchSale;
use App\Support\Catalog;
use App\Support\StockLedger;
use App\Support\StockTransferWorkflow;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoadSampleData extends Command
{
    protected $signature = 'sample:load';

    protected $description = 'Load sample showroom data with factories';

    public function handle(StockLedger $ledger, StockTransferWorkflow $workflow, BranchSale $sales, AgentSale $agentSales): int
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

        DB::transaction(function () use ($ledger, $workflow, $sales, $agentSales, $warehouse, $admin): void {
            $places = $this->places();
            $branches = $this->branches($warehouse, $places);
            $staff = $this->staff($warehouse, $branches);
            $products = $this->products();
            $agents = $this->agents($warehouse, $places);
            $this->agentCashiers($agents);
            $this->agentTerms($agents, $products);

            $this->at(now()->subDays(11)->setTime(10, 0), function () use ($ledger, $products, $warehouse, $admin): void {
                foreach ($this->openingQuantities() as $code => $quantity) {
                    $ledger->openWarehouseStock($this->variant($products, $code), $warehouse, $quantity, 'Opening count', $admin->id);
                }
            });

            $stock = Stock::query()
                ->where('product_variant_id', $this->variant($products, 'ST001')->id)
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

            $this->sale($sales, $branches['BR003'], $staff['cashiers']['BR003'], $this->variant($products, 'CH001'), 2, Payment::METHOD_CASH, 'Venkat Rao', '9848011007', now()->subDays(5)->setTime(12, 10));
            $this->sale($sales, $branches['BR002'], $staff['cashiers']['BR002'], $this->variant($products, 'BD001'), 1, Payment::METHOD_UPI, 'Kiran Reddy', '9848011005', now()->subDays(4)->setTime(15, 40), [
                ['variant' => $this->variant($products, 'CH002'), 'quantity' => 1],
            ]);
            $this->sale($sales, $branches['BR001'], $staff['cashiers']['BR001'], $this->variant($products, 'ST002'), 1, Payment::METHOD_CARD, 'Suresh Babu', '9848011003', now()->subDays(3)->setTime(13, 20));

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

            $this->sale($sales, $branches['BR002'], $staff['cashiers']['BR002'], $this->variant($products, 'SF002'), 1, Payment::METHOD_CASH, 'Priya Nair', '9848011004', now()->subDays(2)->setTime(16, 5));
            $this->sale($sales, $branches['BR001'], $staff['cashiers']['BR001'], $this->variant($products, 'CH001'), 2, Payment::METHOD_UPI, 'Lakshmi Devi', '9848011002', now()->subDay()->setTime(11, 45), [
                ['variant' => $this->variant($products, 'DN002'), 'quantity' => 1],
            ]);
            $this->sale($sales, $branches['BR003'], $staff['cashiers']['BR003'], $this->variant($products, 'SF003'), 1, Payment::METHOD_CARD, 'Anitha Rao', '9848011006', now()->subDay()->setTime(18, 10));
            $this->sale($sales, $branches['BR001'], $staff['cashiers']['BR001'], $this->variant($products, 'SF001'), 1, Payment::METHOD_CASH, 'Ravi Kumar', '9848011001', now()->setTime(10, 25));

            $this->transferToAgent($workflow, 'TR007', $warehouse, $agents['AG001'], $staff['warehouse'], 'Opening stock for Sri Lakshmi Furniture Mart', [
                'SF001' => 2,
            ], $products, now()->subDays(6)->setTime(10, 0), now()->subDays(6)->setTime(15, 0));
            $this->transferToAgent($workflow, 'TR008', $warehouse, $agents['AG002'], $staff['warehouse'], 'Opening stock for Kalanjali Home Studio', [
                'BD001' => 2,
            ], $products, now()->subDays(5)->setTime(10, 0), now()->subDays(5)->setTime(15, 0));
            $this->transferToAgent($workflow, 'TR009', $warehouse, $agents['AG003'], $staff['warehouse'], 'Opening stock for Deccan Living Gallery', [
                'CH001' => 2,
            ], $products, now()->subDays(4)->setTime(10, 0), now()->subDays(4)->setTime(15, 0));

            $this->agentSale($agentSales, $agents['AG001'], $this->variant($products, 'SF001'), 1, Payment::METHOD_CASH, 'Meena Rao', '9848012001', now()->subDays(2)->setTime(12, 15));
            $this->agentSale($agentSales, $agents['AG002'], $this->variant($products, 'BD001'), 1, Payment::METHOD_UPI, 'Sai Krishna', '9848012002', now()->subDay()->setTime(16, 40));
            $this->agentSale($agentSales, $agents['AG003'], $this->variant($products, 'CH001'), 1, Payment::METHOD_CARD, 'Harika Devi', '9848012003', now()->setTime(11, 5));

            $this->at(now()->setTime(9, 20), function () use ($agents, $products): void {
                $this->agentStockRequest('R00005', $agents['AG003'], 'Sofa set for a walk-in customer', [
                    'SF002' => 1,
                ], $products);
            });

            $this->at(now()->setTime(9, 40), function () use ($branches, $staff, $products): void {
                $this->stockRequest('R00004', $branches['BR003'], $staff['managers']['BR003'], 'Dining table for a customer visit', [
                    'DN001' => 1, 'CH002' => 2,
                ], $products);
            });
        });

        $this->components->info('Sample data loaded.');
        $this->table(['Email', 'Password', 'Role', 'Signs in at'], [
            ['admin@godwin.test', 'password', 'Admin', 'Admin'],
            ['manager.vijayawada@godwin.test', 'password', 'Manager', 'Vijayawada Showroom'],
            ['cashier.vijayawada@godwin.test', 'password', 'Cashier', 'Vijayawada Showroom'],
            ['manager.guntur@godwin.test', 'password', 'Manager', 'Guntur Showroom'],
            ['cashier.guntur@godwin.test', 'password', 'Cashier', 'Guntur Showroom'],
            ['manager.vizag@godwin.test', 'password', 'Manager', 'Visakhapatnam Showroom'],
            ['cashier.vizag@godwin.test', 'password', 'Cashier', 'Visakhapatnam Showroom'],
            ['manager.lakshmi@godwin.test', 'password', 'Manager', 'Sri Lakshmi Furniture Mart'],
            ['cashier.lakshmi@godwin.test', 'password', 'Cashier', 'Sri Lakshmi Furniture Mart'],
            ['manager.kalanjali@godwin.test', 'password', 'Manager', 'Kalanjali Home Studio'],
            ['cashier.kalanjali@godwin.test', 'password', 'Cashier', 'Kalanjali Home Studio'],
            ['manager.deccan@godwin.test', 'password', 'Manager', 'Deccan Living Gallery'],
            ['cashier.deccan@godwin.test', 'password', 'Cashier', 'Deccan Living Gallery'],
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
     * @param  array{vijayawada: array{state: State, district: District, city: City}, guntur: array{state: State, district: District, city: City}, vizag: array{state: State, district: District, city: City}}  $places
     * @return array<string, Agent>
     */
    private function agents(Warehouse $warehouse, array $places): array
    {
        $rows = [
            'AG001' => ['Sri Lakshmi Furniture Mart', 'Venkateswara Rao', '9848091001', 'manager.lakshmi@godwin.test', 'Besant Road, Governorpet', '520002', 'vijayawada', 5],
            'AG002' => ['Kalanjali Home Studio', 'Swapna Reddy', '9848091002', 'manager.kalanjali@godwin.test', 'Arundelpet, 4th Line', '522002', 'guntur', 8],
            'AG003' => ['Deccan Living Gallery', 'Naresh Kumar', '9848091003', 'manager.deccan@godwin.test', 'Siripuram Junction', '530003', 'vizag', 6],
        ];

        $agents = [];

        foreach ($rows as $code => [$shop, $contact, $phone, $email, $address, $pincode, $place, $commission]) {
            $location = $places[$place];
            $user = User::factory()->agent()->create([
                'name' => $contact,
                'email' => $email,
                'phone' => $phone,
            ]);

            $agent = Agent::query()->create([
                'code' => $code,
                'name' => $shop,
                'user_id' => $user->id,
                'warehouse_id' => $warehouse->id,
                'contact_person' => $contact,
                'phone' => $phone,
                'email' => $email,
                'address_line' => $address,
                'state_id' => $location['state']->id,
                'district_id' => $location['district']->id,
                'city_id' => $location['city']->id,
                'pincode' => $pincode,
                'commission_percent' => $commission,
                'status' => Agent::STATUS_ACTIVE,
            ]);
            $user->update(['agent_id' => $agent->id]);
            $agents[$code] = $agent;
        }

        return $agents;
    }

    /**
     * @param  array<string, Agent>  $agents
     */
    private function agentCashiers(array $agents): void
    {
        $rows = [
            'AG001' => ['Padmavathi', '9848092001', 'cashier.lakshmi@godwin.test'],
            'AG002' => ['Ramesh Babu', '9848092002', 'cashier.kalanjali@godwin.test'],
            'AG003' => ['Sirisha Rao', '9848092003', 'cashier.deccan@godwin.test'],
        ];

        foreach ($rows as $code => [$name, $phone, $email]) {
            User::factory()->agentStaff()->create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'agent_id' => $agents[$code]->id,
            ]);
        }
    }

    /**
     * @param  array<string, Agent>  $agents
     * @param  array<string, Product>  $products
     */
    private function agentTerms(array $agents, array $products): void
    {
        $rows = [
            'SF001' => [
                'AG001' => [40000, 5],
                'AG002' => [43000, 8],
                'AG003' => [41000, 6],
            ],
            'BD001' => [
                'AG001' => [36000, 4],
                'AG002' => [34000, 7],
                'AG003' => [37000, 5],
            ],
            'CH001' => [
                'AG001' => [11000, 6],
                'AG002' => [10000, 10],
                'AG003' => [12000, 8],
            ],
        ];

        foreach ($rows as $productCode => $shops) {
            foreach ($shops as $agentCode => [$price, $commission]) {
                $shopRate = (float) $agents[$agentCode]->commission_percent;

                AgentProductTerm::query()->create([
                    'agent_id' => $agents[$agentCode]->id,
                    'product_variant_id' => $this->variant($products, $productCode)->id,
                    'price' => $price,
                    'commission_percent' => (float) $commission === $shopRate ? null : $commission,
                ]);
            }
        }
    }

    private function agentSale(AgentSale $sales, Agent $agent, ProductVariant $variant, int $quantity, string $method, string $customerName, string $phone, Carbon $at): void
    {
        $this->at($at, function () use ($sales, $agent, $variant, $quantity, $method, $customerName, $phone): void {
            $customer = Customer::query()->create([
                'name' => $customerName,
                'phone' => $phone,
                'status' => Customer::STATUS_ACTIVE,
            ]);

            $sales->complete($agent, $customer, [
                ['product_variant_id' => $variant->id, 'quantity' => $quantity],
            ], $method, $agent->user_id);
        });
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
        $img = fn (string $id) => "https://images.unsplash.com/{$id}?auto=format&fit=crop&q=80&w=800";
        $imagePool = [
            $img('photo-1555041469-a586c61ea9bc'),
            $img('photo-1493663284031-b7e3aefcae8e'),
            $img('photo-1586023492125-27b2c045efd7'),
            $img('photo-1505693314120-0d443867891c'),
            $img('photo-1524758631624-e2822e304c36'),
            $img('photo-1617806118233-18e1de247200'),
            $img('photo-1577140917170-285929fb55b7'),
            $img('photo-1567538096630-e0c55bd6374c'),
            $img('photo-1595428774223-ef52624120d2'),
            $img('photo-1532372576444-dda954194ad0'),
            $img('photo-1540518614846-7ede433c5163'),
            $img('photo-1522771739844-6a9f6d5f14af'),
        ];

        // Named products used by stock / sales / transfers. Each gets 2–3 variants.
        $rows = [
            ['SF001', 'Alanis 3-Seater Sofa', 'Furniture', 'Living Room', 'Sofa', 'Chester', 42000, true, true, 'CRCA Steel & Velvet', 'Metal frame three-seater.', [$imagePool[0], $imagePool[1]], Product::STATUS_ACTIVE, [
                ['Walnut', 'Matte', 'Velvet', 42000, 54999],
                ['Forest green', 'Matte', 'Velvet', 43500, 54999],
                ['Charcoal', 'Textured', 'Linen blend', 41000, 52999],
            ]],
            ['SF002', 'Four Seater Family Sofa', 'Furniture', 'Living Room', 'Sofa', 'Royal', 62000, true, false, 'Solid Teak & Linen', 'Family sofa with removable cushions.', [$imagePool[1]], Product::STATUS_ACTIVE, [
                ['Natural oak', 'Natural wood', 'Linen blend', 62000, 74999],
                ['Ivory', 'Matte', 'Boucle', 64000, 74999],
            ]],
            ['SF003', 'Nordic Modular Sectional', 'Furniture', 'Living Room', 'Sofa', 'Modular', 78000, true, true, 'Powder Coated Steel', 'Corner sectional for the living room.', [$imagePool[2]], Product::STATUS_ACTIVE, [
                ['Ash grey', 'Matte', 'Velvet', 78000, 94999],
                ['Navy', 'Glossy', 'Leatherette', 80000, 96999],
                ['Charcoal', 'Textured', 'Genuine leather', 85000, 99999],
            ]],
            ['BD001', 'Queen Size Solid Teak Bed', 'Furniture', 'Bedroom', 'Queen Size Bed', 'Platform', 38000, true, false, 'Solid Teak Wood', 'Queen teak bed with storage drawers.', [$imagePool[3]], Product::STATUS_ACTIVE, [
                ['Walnut', 'Natural wood', 'Linen blend', 38000, 49999],
                ['Natural oak', 'Matte', 'Velvet', 39500, 51999],
            ]],
            ['BD002', 'Imperial King Bed', 'Furniture', 'Bedroom', 'King Size Bed', 'Royal', 45000, true, true, 'CRCA Steel & Seasoned Teak', 'King bed with heavy CRCA frame.', [$imagePool[3], $imagePool[4]], Product::STATUS_ACTIVE, [
                ['Charcoal', 'Matte', 'Velvet', 45000, 59999],
                ['Walnut', 'Natural wood', 'Genuine leather', 47000, 61999],
                ['Ivory', 'Duco paint', 'Boucle', 46000, 60999],
            ]],
            ['DN001', 'Imperial 6-Seater Dining Set', 'Furniture', 'Dining Room', 'Dining Table', 'Rectangular', 54000, true, true, 'CRCA Heavy Steel', 'Dining set with six matching chairs.', [$imagePool[5]], Product::STATUS_ACTIVE, [
                ['Walnut', 'Matte', '', 54000, 69999],
                ['Natural oak', 'Natural wood', '', 56000, 71999],
            ]],
            ['DN002', 'Compact 4-Seater Dining Set', 'Furniture', 'Dining Room', 'Dining Table', 'Round', 42000, true, false, 'Solid Teak Wood', 'Compact dining set for an apartment.', [$imagePool[6]], Product::STATUS_ACTIVE, [
                ['Natural oak', 'Natural wood', '', 42000, 54999],
                ['Charcoal', 'Matte', '', 41000, 53999],
            ]],
            ['CH001', 'Italian Leather Accent Chair', 'Furniture', 'Living Room', 'Accent Chair', 'Lounge', 12500, true, false, 'Italian Leather', 'Single accent chair for the living room.', [$imagePool[2]], Product::STATUS_ACTIVE, [
                ['Tan leather', 'Matte', 'Genuine leather', 12500, 15999],
                ['Charcoal', 'Textured', 'Leatherette', 11800, 14999],
                ['Navy', 'Glossy', 'Velvet', 13200, 16999],
            ]],
            ['CH002', 'Steel & Velvet Recliner', 'Furniture', 'Living Room', 'Recliner', 'Executive', 18500, true, false, 'CRCA Steel & Velvet', 'Cushioned recliner for a lounge corner.', [$imagePool[7]], Product::STATUS_ACTIVE, [
                ['Forest green', 'Matte', 'Velvet', 18500, 22999],
                ['Ash grey', 'Textured', 'Leatherette', 17900, 21999],
            ]],
            ['ST001', '2-Door Swing Wardrobe', 'Furniture', 'Bedroom', 'Wardrobe', 'Hinged', 36000, true, false, 'Solid Teak Wood', 'Wardrobe with hanging space and shelves.', [$imagePool[8]], Product::STATUS_ACTIVE, [
                ['Walnut', 'Natural wood', '', 36000, 45999],
                ['Ivory', 'Matte', '', 35000, 44999],
                ['Charcoal', 'Glossy', '', 37000, 46999],
            ]],
            ['ST002', 'Industrial TV Console', 'Furniture', 'Living Room', 'TV Console', 'Floor Standing', 18500, true, false, 'Powder Coated Steel', 'Low TV unit with drawers.', [$imagePool[9]], Product::STATUS_ACTIVE, [
                ['Charcoal', 'Matte', '', 18500, 24999],
                ['Walnut', 'Natural wood', '', 19200, 25999],
            ]],
            ['OT001', 'Floor Ottoman Stool', 'Furniture', 'Living Room', 'Ottoman', 'Minimalist', 4500, false, false, 'Fabric', 'Display sample, not for sale yet.', [], Product::STATUS_INACTIVE, [
                ['Ivory', 'Matte', 'Boucle', 4500, null],
                ['Forest green', 'Textured', 'Velvet', 4800, null],
            ]],
        ];

        $products = [];
        $counts = [];

        foreach ($rows as [$code, $name, $project, $room, $category, $type, $base, $isOnline, $featured, $material, $description, $images, $status, $variantRows]) {
            $products[$code] = $this->makeProduct(
                $code,
                $name,
                Str::slug($name),
                $project,
                $room,
                $category,
                $type,
                $base,
                $isOnline,
                $featured,
                $material,
                $description,
                $images,
                $status,
                $variantRows,
            );
            $leafId = $products[$code]->category_id;
            $counts[$leafId] = ($counts[$leafId] ?? 0) + 1;
        }

        // Fill Furniture leaf categories so the online catalog stays dense.
        $styles = ['Classic', 'Modern', 'Heritage', 'Urban', 'Studio', 'Royal', 'Compact', 'Premium'];
        $materials = ['Solid Teak', 'CRCA Steel', 'Engineered Wood', 'Fabric & Foam', 'Leatherette', 'Cotton Blend'];
        $colourKeys = array_keys(Catalog::colours());
        $finishes = Catalog::finishes();
        $upholsteries = Catalog::upholsteries();
        $seq = 1;

        foreach (Catalog::tree()['Furniture'] as $room => $categories) {
            foreach ($categories as $categoryName) {
                $leaf = Catalog::leafCategory('Furniture', $room, $categoryName);
                $have = $counts[$leaf->id] ?? 0;
                $target = 5 + (($leaf->id + strlen($categoryName)) % 3); // 5–7 products

                for ($n = $have + 1; $n <= $target; $n++) {
                    $code = 'GX'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
                    $seq++;
                    $style = $styles[($n + $leaf->id) % count($styles)];
                    $material = $materials[($n + $leaf->id) % count($materials)];
                    $base = 2500 + (($leaf->id * 17 + $n * 1300) % 75000);
                    $mrp = (int) round($base * 1.25);
                    $image = $imagePool[($leaf->id + $n) % count($imagePool)];
                    $family = Catalog::family('Furniture', $categoryName);
                    $types = Catalog::typesFor($categoryName);
                    $type = $types[($n - 1) % count($types)];
                    $c1 = $colourKeys[($n + $leaf->id) % count($colourKeys)];
                    $c2 = $colourKeys[($n + $leaf->id + 3) % count($colourKeys)];
                    $finish = $finishes[($n) % count($finishes)];
                    $up1 = Catalog::usesUpholstery($family) ? $upholsteries[($n) % count($upholsteries)] : '';
                    $up2 = Catalog::usesUpholstery($family) ? $upholsteries[($n + 1) % count($upholsteries)] : '';

                    $products[$code] = $this->makeProduct(
                        $code,
                        "{$style} {$categoryName} {$n}",
                        Str::slug($style.'-'.$categoryName.'-'.$n.'-'.$code),
                        'Furniture',
                        $room,
                        $categoryName,
                        $type,
                        $base,
                        true,
                        $n === 1,
                        $material,
                        "Sample {$categoryName} piece for the online catalog.",
                        [$image],
                        Product::STATUS_ACTIVE,
                        [
                            [$c1, $finish, $up1, $base, $mrp],
                            [$c2, $finishes[($n + 1) % count($finishes)], $up2, (int) round($base * 1.05), $mrp],
                        ],
                    );
                    $counts[$leaf->id] = ($counts[$leaf->id] ?? 0) + 1;
                }
            }
        }

        $vijayawada = Branch::query()->where('code', 'BR001')->first();
        if ($vijayawada) {
            BranchProductPrice::query()->create([
                'branch_id' => $vijayawada->id,
                'product_variant_id' => $this->variant($products, 'SF001')->id,
                'price' => 40000,
            ]);
            BranchProductPrice::query()->create([
                'branch_id' => $vijayawada->id,
                'product_variant_id' => $this->variant($products, 'BD002')->id,
                'price' => 44000,
            ]);
        }

        return $products;
    }

    /**
     * @param  list<string>  $images
     * @param  list<array{0: string, 1: string, 2: string, 3: float|int, 4: float|int|null}>  $variantRows
     */
    private function makeProduct(
        string $code,
        string $name,
        string $slug,
        string $project,
        string $room,
        string $productCategory,
        string $designType,
        float|int $basePrice,
        bool $isOnline,
        bool $featured,
        string $material,
        string $description,
        array $images,
        string $status,
        array $variantRows,
    ): Product {
        $category = Catalog::leafCategory($project, $room, $productCategory);
        $family = Catalog::family($project, $productCategory);

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'code' => $code,
            'slug' => $slug,
            'name' => $name,
            'description' => $description,
            'project_category' => $project,
            'room' => $room,
            'product_category' => $productCategory,
            'design_type' => $designType,
            'family' => $family,
            'material' => $material,
            'base_price' => $basePrice,
            'is_online' => $isOnline,
            'is_featured' => $featured,
            'status' => $status,
        ]);

        foreach ($variantRows as $index => [$colour, $finish, $upholstery, $price, $mrp]) {
            ProductVariant::query()->create([
                'product_id' => $product->id,
                'sku' => $code.'-V'.($index + 1),
                'colour' => $colour,
                'finish' => $finish,
                'upholstery' => Catalog::usesUpholstery($family) ? $upholstery : '',
                'price' => $price,
                'compare_at_price' => $mrp,
                'status' => ProductVariant::STATUS_ACTIVE,
            ]);
        }

        foreach ($images as $index => $url) {
            ProductImage::query()->create([
                'product_id' => $product->id,
                'url' => $url,
                'sort_order' => $index,
            ]);
        }

        return $product->load('variants');
    }

    /**
     * @param  array<string, Product>  $products
     */
    private function variant(array $products, string $code): ProductVariant
    {
        $product = $products[$code];
        $product->loadMissing('variants');

        return $product->variants->firstOrFail();
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
                'product_variant_id' => $this->variant($products, $productCode)->id,
                'quantity' => $quantity,
            ]);
        }

        return $request;
    }

    /**
     * @param  array<string, int>  $lines
     * @param  array<string, Product>  $products
     */
    private function transferToAgent(
        StockTransferWorkflow $workflow,
        string $code,
        Warehouse $warehouse,
        Agent $agent,
        User $dispatcher,
        string $notes,
        array $lines,
        array $products,
        Carbon $dispatchedAt,
        Carbon $receivedAt,
    ): void {
        $transfer = $this->at($dispatchedAt, function () use ($workflow, $code, $warehouse, $agent, $dispatcher, $notes, $lines, $products) {
            $transfer = StockTransfer::factory()->create([
                'code' => $code,
                'warehouse_id' => $warehouse->id,
                'branch_id' => null,
                'agent_id' => $agent->id,
                'notes' => $notes,
                'created_by' => $dispatcher->id,
            ]);

            foreach ($lines as $productCode => $quantity) {
                StockTransferItem::factory()->create([
                    'stock_transfer_id' => $transfer->id,
                    'product_variant_id' => $this->variant($products, $productCode)->id,
                    'quantity' => $quantity,
                ]);
            }

            $workflow->dispatch($transfer, $dispatcher->id);

            return $transfer;
        });

        $this->at($receivedAt, fn () => $workflow->receive($transfer, $agent->user_id));
    }

    /**
     * @param  array<string, int>  $lines
     * @param  array<string, Product>  $products
     */
    private function agentStockRequest(string $code, Agent $agent, string $notes, array $lines, array $products): StockRequest
    {
        $request = StockRequest::query()->create([
            'code' => $code,
            'agent_id' => $agent->id,
            'status' => StockRequest::STATUS_REQUESTED,
            'notes' => $notes,
            'requested_by' => $agent->user_id,
        ]);

        foreach ($lines as $productCode => $quantity) {
            $request->items()->create([
                'product_variant_id' => $this->variant($products, $productCode)->id,
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
                'product_variant_id' => $this->variant($products, $productCode)->id,
                'quantity' => $quantity,
            ]);
        }

        return $transfer;
    }

    /**
     * @param  list<array{variant: ProductVariant, quantity: int}>  $extra
     */
    private function sale(
        BranchSale $sales,
        Branch $branch,
        User $cashier,
        ProductVariant $variant,
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

        $lines = [['product_variant_id' => $variant->id, 'quantity' => $quantity]];

        foreach ($extra as $line) {
            $lines[] = ['product_variant_id' => $line['variant']->id, 'quantity' => $line['quantity']];
        }

        $this->at($when, fn () => $sales->complete($branch, $customer, $lines, $method, $cashier->id, [
            'delivery_type' => Order::DELIVERY_PICKUP,
            'notes' => null,
        ]));
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
