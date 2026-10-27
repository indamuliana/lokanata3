<?php

$dir = __DIR__ . '/app/Models';

$models = [
    'RoomType' => <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    protected \$guarded = [];

    public function rooms()
    {
        return \$this->hasMany(Room::class);
    }
}
PHP,
    
    'Room' => <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected \$guarded = [];

    public function roomType()
    {
        return \$this->belongsTo(RoomType::class);
    }
}
PHP,
    
    'HallRental' => <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HallRental extends Model
{
    protected \$guarded = [];
}
PHP,

    'MenuItem' => <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected \$guarded = [];
}
PHP,

    'SeasonPeriod' => <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonPeriod extends Model
{
    protected \$guarded = [];
}
PHP,

    'Reservation' => <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected \$guarded = [];

    public function invoice()
    {
        return \$this->hasOne(Invoice::class);
    }

    public function roomTransferLogs()
    {
        return \$this->hasMany(RoomTransferLog::class);
    }
}
PHP,

    'Invoice' => <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected \$guarded = [];

    public function reservation()
    {
        return \$this->belongsTo(Reservation::class);
    }

    public function items()
    {
        return \$this->hasMany(InvoiceItem::class);
    }

    public function paymentRecords()
    {
        return \$this->hasMany(PaymentRecord::class);
    }
}
PHP,

    'InvoiceItem' => <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected \$guarded = [];

    public function invoice()
    {
        return \$this->belongsTo(Invoice::class);
    }
}
PHP,

    'PaymentRecord' => <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentRecord extends Model
{
    protected \$guarded = [];

    public function invoice()
    {
        return \$this->belongsTo(Invoice::class);
    }
}
PHP,

    'RoomTransferLog' => <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomTransferLog extends Model
{
    protected \$guarded = [];

    public function reservation()
    {
        return \$this->belongsTo(Reservation::class);
    }

    public function fromRoom()
    {
        return \$this->belongsTo(Room::class, 'from_room_id');
    }

    public function toRoom()
    {
        return \$this->belongsTo(Room::class, 'to_room_id');
    }
}
PHP,
];

foreach ($models as $name => $content) {
    file_put_contents("$dir/$name.php", $content);
}
