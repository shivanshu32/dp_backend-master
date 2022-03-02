<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public function order_arts()
    {
        return $this->hasMany(OrderArt::class);
    }

     public function printer()
    {
        return $this->belongsTo(Printer::class, 'printer_id');
    }

    public function getTotalProductCountAttribute()
    {
        $totalCount = 0;
        for($i = 1; $i <= 5; $i++) {
            if($this['pcs_1_'.$i]) {
                try {
                    $totalCount += $this['pcs_1_'.$i];
                } catch (\Exception $e) {
                    $totalCount += 0;
                }
               
            }
            if($this['pcs_2_'.$i]) {
                try {
                    $totalCount += $this['pcs_2_'.$i];
                } catch (\Exception $e) {
                    $totalCount += 0;
                }
            }
            if($this['pcs_3_'.$i]) {
                try {
                    $totalCount += $this['pcs_3_'.$i];
                } catch (\Exception $e) {
                    $totalCount += 0;
                }
            }
            if($this['pcs_4_'.$i]) {
                try {
                    $totalCount += $this['pcs_4_'.$i];
                } catch (\Exception $e) {
                    $totalCount += 0;
                }
            }

            if($this['xs_'.$i]) {
                try {
                    $totalCount += $this['xs_'.$i];
                } catch (\Exception $e) {
                    $totalCount += 0;
                }
            }

            if($this['s_'.$i]) {
                try {
                    $totalCount += $this['s_'.$i];
                } catch (\Exception $e) {
                    $totalCount += 0;
                }
            }

            if($this['m_'.$i]) {
                try {
                    $totalCount += $this['m_'.$i];
                } catch (\Exception $e) {
                    $totalCount += 0;
                }
            }
            if($this['l_'.$i]) {
                try {
                    $totalCount += $this['l_'.$i];
                } catch (\Exception $e) {
                    $totalCount += 0;
                }
            }
            if($this['xxl_'.$i]) {
                try {
                    $totalCount += $this['xxl_'.$i];
                } catch (\Exception $e) {
                    $totalCount += 0;
                }
            }
            if($this['xl_'.$i]) {
                try {
                    $totalCount += $this['xl_'.$i];
                } catch (\Exception $e) {
                    $totalCount += 0;
                }
            }
            if($this['xxxl_'.$i]) {
                try {
                    $totalCount += $this['xxxl_'.$i];
                } catch (\Exception $e) {
                    $totalCount += 0;
                }
            }

        }

        return $totalCount;
    }

    protected $appends = ['total_product_count'];
}
