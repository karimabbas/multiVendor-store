<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Psy\CodeCleaner\FunctionReturnInWriteContextPass;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id', 'user_id', 'payment_method', 'status', 'payment_status', 'total',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault([
            'name' => 'Guest Customer'
        ]);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_items', 'order_id', 'product_id', 'id', 'id')
            ->using(OrderItem::class)
            ->withPivot([
                'product_name', 'price', 'quantity', 'options',
            ]);
    }

    public function addresses()
    {
        return $this->hasMany(OrderAddress::class);
    }

    public function billingAddress()
    {
        // this way return a collection of values not the model
        // return $this->addresses()->where('type','=','billing');

        //this way return the model (we made a relation with the same table)
        return $this->hasOne(OrderAddress::class, 'order_id', 'id')->where('type', '=', 'billing');
    }

    public function shippingAddress()
    {
        return $this->hasOne(OrderAddress::class, 'order_id', 'id')->where('type', '=', 'shipping');
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }


    public static function booted()
    {
        static::creating(function (Order $order) {
            //20220001 , 2023001
            $order->number = Order::getNextOrderNumber();
        });
    }

    public function getSum()
    {
        foreach ($this->products as $prodcut) {
            return $prodcut->pivot->quantity * $prodcut->pivot->price;
        }
    }


    public static function getNextOrderNumber()
    {

        $year =  Carbon::now()->year;
        $number = Order::whereYear('created_at', $year)->max('number');
        if ($number) {
            return $number + 1;
        }
        return $year . '0001';
    }
}
