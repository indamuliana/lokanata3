<?php

$dir = __DIR__ . '/database/migrations';
$files = scandir($dir);

foreach($files as $file) {
    if (strpos($file, 'create_room_types_table') !== false) {
        $content = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_types', function (Blueprint \$table) {
            \$table->id();
            \$table->string('name');
            \$table->decimal('base_price_normal', 15, 2);
            \$table->decimal('base_price_high', 15, 2);
            \$table->integer('capacity')->default(2);
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
PHP;
        file_put_contents("$dir/$file", $content);
    }

    if (strpos($file, 'create_rooms_table') !== false) {
        $content = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
            \$table->string('room_number')->unique();
            \$table->enum('condition_status', ['Normal', 'Maintenance', 'Non-Aktif'])->default('Normal');
            \$table->enum('readiness_status', ['Kotor', 'Sedang Dibersihkan', 'Ready'])->default('Ready');
            \$table->enum('current_occupancy', ['Available', 'Booked', 'Check-in', 'Check-out'])->default('Available');
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
PHP;
        file_put_contents("$dir/$file", $content);
    }

    if (strpos($file, 'create_hall_rentals_table') !== false) {
        $content = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hall_rentals', function (Blueprint \$table) {
            \$table->id();
            \$table->string('name');
            \$table->decimal('rate_normal_per_day', 15, 2);
            \$table->decimal('rate_high_per_day', 15, 2);
            \$table->integer('capacity')->nullable();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hall_rentals');
    }
};
PHP;
        file_put_contents("$dir/$file", $content);
    }

    if (strpos($file, 'create_menu_items_table') !== false) {
        $content = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint \$table) {
            \$table->id();
            \$table->string('name');
            \$table->string('category')->nullable();
            \$table->decimal('default_price', 15, 2);
            \$table->boolean('is_custom_created')->default(false);
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
PHP;
        file_put_contents("$dir/$file", $content);
    }

    if (strpos($file, 'create_season_periods_table') !== false) {
        $content = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('season_periods', function (Blueprint \$table) {
            \$table->id();
            \$table->date('start_date');
            \$table->date('end_date');
            \$table->enum('session_type', ['Normal', 'High']);
            \$table->string('description')->nullable();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('season_periods');
    }
};
PHP;
        file_put_contents("$dir/$file", $content);
    }

    if (strpos($file, 'create_reservations_table') !== false) {
        $content = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint \$table) {
            \$table->id();
            \$table->string('code_booking')->unique();
            \$table->string('guest_name');
            \$table->string('guest_phone')->nullable();
            \$table->date('check_in_date');
            \$table->date('check_out_date');
            \$table->enum('status', ['Booked', 'Check-in', 'Check-out', 'Cancelled']);
            \$table->enum('cancellation_type', ['None', 'Hangus', 'Refund 100%', 'Refund Sebagian'])->default('None');
            \$table->decimal('refund_amount', 15, 2)->nullable();
            \$table->decimal('security_deposit_amount', 15, 2)->default(0);
            \$table->string('deposit_status')->nullable();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
PHP;
        file_put_contents("$dir/$file", $content);
    }

    if (strpos($file, 'create_invoices_table') !== false) {
        $content = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
            \$table->string('invoice_number')->unique();
            \$table->decimal('subtotal', 15, 2)->default(0);
            \$table->decimal('discount_amount', 15, 2)->default(0);
            \$table->decimal('tax_amount', 15, 2)->default(0);
            \$table->decimal('total_amount', 15, 2)->default(0);
            \$table->decimal('paid_amount', 15, 2)->default(0);
            \$table->decimal('balance_due', 15, 2)->default(0);
            \$table->enum('payment_status', ['Unpaid', 'Partial', 'Paid'])->default('Unpaid');
            \$table->uuid('qr_verification_uuid')->nullable()->unique();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
PHP;
        file_put_contents("$dir/$file", $content);
    }

    if (strpos($file, 'create_invoice_items_table') !== false) {
        $content = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            \$table->enum('item_type', ['Room', 'F&B', 'Hall', 'Penalty', 'Other']);
            \$table->string('description');
            \$table->decimal('unit_price', 15, 2);
            \$table->integer('quantity')->default(1);
            \$table->decimal('subtotal', 15, 2);
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
PHP;
        file_put_contents("$dir/$file", $content);
    }

    if (strpos($file, 'create_payment_records_table') !== false) {
        $content = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_records', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            \$table->string('payment_phase'); // e.g. 'DP', 'Check-in', 'Check-out'
            \$table->enum('payment_method', ['Tunai', 'QRIS', 'Transfer']);
            \$table->decimal('amount_paid', 15, 2);
            \$table->timestamp('paid_at')->useCurrent();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_records');
    }
};
PHP;
        file_put_contents("$dir/$file", $content);
    }

    if (strpos($file, 'create_room_transfer_logs_table') !== false) {
        $content = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_transfer_logs', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
            \$table->foreignId('from_room_id')->constrained('rooms')->cascadeOnDelete();
            \$table->foreignId('to_room_id')->constrained('rooms')->cascadeOnDelete();
            \$table->string('reason')->nullable();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_transfer_logs');
    }
};
PHP;
        file_put_contents("$dir/$file", $content);
    }
}
