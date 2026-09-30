<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\Category;
use App\Models\City;
use App\Models\Customer;
use App\Models\District;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\Product;
use App\Models\State;
use App\Models\Stock;
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

            $this->transfer($workflow, 'TR001', $warehouse, $branches['BR001'], $staff['warehouse'], 'Weekly refill for Vijayawada', [
                'SF001' => 3, 'CH001' => 4, 'ST002' => 2, 'DN002' => 2,
            ], $products, now()->subDays(8)->setTime(9, 30), now()->subDays(8)->setTime(16, 0));

            $this->transfer($workflow, 'TR002', $warehouse, $branches['BR002'], $staff['warehouse'], 'Opening stock for Guntur', [
                'SF002' => 2, 'BD001' => 2, 'CH002' => 3, 'ST001' => 1,
            ], $products, now()->subDays(7)->setTime(10, 0), now()->subDays(7)->setTime(15, 30));

            $this->transfer($workflow, 'TR003', $warehouse, $branches['BR003'], $staff['warehouse'], 'Opening stock for Visakhapatnam', [
                'SF003' => 1, 'BD002' => 2, 'DN001' => 1, 'CH001' => 4,
            ], $products, now()->subDays(6)->setTime(10, 15), now()->subDays(6)->setTime(17, 0));

            $this->sale($sales, $branches['BR003'], $staff['BR003'], $products['CH001'], 2, Payment::METHOD_CASH, 'Venkat Rao', '9848011007', now()->subDays(5)->setTime(12, 10));
            $this->sale($sales, $branches['BR002'], $staff['BR002'], $products['BD001'], 1, Payment::METHOD_UPI, 'Kiran Reddy', '9848011005', now()->subDays(4)->setTime(15, 40), [
                ['product' => $products['CH002'], 'quantity' => 1],
            ]);
            $this->sale($sales, $branches['BR001'], $staff['BR001'], $products['ST002'], 1, Payment::METHOD_CARD, 'Suresh Babu', '9848011003', now()->subDays(3)->setTime(13, 20));

            $this->at(now()->subDays(2)->setTime(11, 0), function () use ($workflow, $warehouse, $branches, $staff, $products): void {
                $transfer = $this->draft('TR004', $warehouse, $branches['BR001'], $staff['warehouse'], 'Second delivery for Vijayawada', [
                    'SF002' => 1, 'CH002' => 2,
                ], $products);
                $workflow->dispatch($transfer, $staff['warehouse']->id);
            });

            $this->at(now()->subDays(2)->setTime(11, 20), function () use ($warehouse, $branches, $staff, $products): void {
                $this->draft('TR005', $warehouse, $branches['BR002'], $staff['warehouse'], 'Next Guntur delivery', [
                    'BD001' => 2, 'ST002' => 1,
                ], $products);
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

            $this->sale($sales, $branches['BR002'], $staff['BR002'], $products['SF002'], 1, Payment::METHOD_CASH, 'Priya Nair', '9848011004', now()->subDays(2)->setTime(16, 5));
            $this->sale($sales, $branches['BR001'], $staff['BR001'], $products['CH001'], 2, Payment::METHOD_UPI, 'Lakshmi Devi', '9848011002', now()->subDay()->setTime(11, 45), [
                ['product' => $products['DN002'], 'quantity' => 1],
            ]);
            $this->sale($sales, $branches['BR003'], $staff['BR003'], $products['SF003'], 1, Payment::METHOD_CARD, 'Anitha Rao', '9848011006', now()->subDay()->setTime(18, 10));
            $this->sale($sales, $branches['BR001'], $staff['BR001'], $products['SF001'], 1, Payment::METHOD_CASH, 'Ravi Kumar', '9848011001', now()->setTime(10, 25));
        });

        $this->components->info('Sample data loaded.');
        $this->table(['Email', 'Password', 'Signs in at'], [
            ['admin@godwin.test', 'password', 'Admin'],
            ['manager.vijayawada@godwin.test', 'password', 'Branch portal'],
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
     * @return array<string, User>
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

        $this->employee(
            'manager.vijayawada@godwin.test',
            'Ramesh Kumar',
            '9848002001',
            'MGR001',
            'Branch Manager',
            'branchManager',
            null,
            $branches['BR001']->id,
        );

        return [
            'warehouse' => $warehouseUser,
            'BR001' => $this->employee('cashier.vijayawada@godwin.test', 'Sowmya Rao', '9848003001', 'CSH001', 'Cashier', 'branchStaff', null, $branches['BR001']->id),
            'BR002' => $this->employee('cashier.guntur@godwin.test', 'Harish Patel', '9848003002', 'CSH002', 'Cashier', 'branchStaff', null, $branches['BR002']->id),
            'BR003' => $this->employee('cashier.vizag@godwin.test', 'Divya Nair', '9848003003', 'CSH003', 'Cashier', 'branchStaff', null, $branches['BR003']->id),
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
        $sofas = Category::factory()->create(['name' => 'Sofas']);
        $beds = Category::factory()->create(['name' => 'Beds']);
        $dining = Category::factory()->create(['name' => 'Dining']);
        $chairs = Category::factory()->create(['name' => 'Chairs']);
        $storage = Category::factory()->create(['name' => 'Storage']);

        $rows = [
            ['SF001', 'Three Seater Sofa', $sofas, 45000, true, 'Solid teak frame with linen upholstery.', Product::STATUS_ACTIVE],
            ['SF002', 'Four Seater Sofa', $sofas, 62000, false, 'Family sofa with removable cushions.', Product::STATUS_ACTIVE],
            ['SF003', 'L-Shaped Sofa', $sofas, 78000, true, 'Corner sofa for the living room.', Product::STATUS_ACTIVE],
            ['BD001', 'Queen Size Bed', $beds, 38000, true, 'Queen teak bed with storage.', Product::STATUS_ACTIVE],
            ['BD002', 'King Size Bed', $beds, 52000, false, 'King size bed in a sheesham finish.', Product::STATUS_ACTIVE],
            ['DN001', 'Six Seater Dining Table', $dining, 54000, true, 'Dining table with six matching chairs.', Product::STATUS_ACTIVE],
            ['DN002', 'Four Seater Dining Set', $dining, 42000, false, 'Compact dining set for an apartment.', Product::STATUS_ACTIVE],
            ['CH001', 'Teak Armchair', $chairs, 8500, true, 'Single teak armchair.', Product::STATUS_ACTIVE],
            ['CH002', 'Office Chair', $chairs, 6500, false, 'Cushioned chair for a study.', Product::STATUS_ACTIVE],
            ['ST001', 'Two Door Wardrobe', $storage, 36000, false, 'Wardrobe with hanging space and shelves.', Product::STATUS_ACTIVE],
            ['ST002', 'TV Unit', $storage, 18500, true, 'Low TV unit with drawers.', Product::STATUS_ACTIVE],
            ['OT001', 'Ottoman', $sofas, 4500, false, 'Display sample, not for sale yet.', Product::STATUS_INACTIVE],
        ];

        $products = [];

        foreach ($rows as [$code, $name, $category, $price, $online, $description, $status]) {
            $products[$code] = Product::factory()->create([
                'category_id' => $category->id,
                'code' => $code,
                'name' => $name,
                'description' => $description,
                'selling_price' => $price,
                'is_online' => $online,
                'status' => $status,
            ]);
        }

        return $products;
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
        User $user,
        string $notes,
        array $lines,
        array $products,
        Carbon $dispatchedAt,
        Carbon $receivedAt,
    ): void {
        $transfer = $this->at($dispatchedAt, function () use ($workflow, $code, $warehouse, $branch, $user, $notes, $lines, $products) {
            $transfer = $this->draft($code, $warehouse, $branch, $user, $notes, $lines, $products);
            $workflow->dispatch($transfer, $user->id);

            return $transfer;
        });

        $this->at($receivedAt, fn () => $workflow->receive($transfer, $user->id));
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
